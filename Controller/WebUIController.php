<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Translator;
use Translation\Bundle\Exception\MessageValidationException;
use Translation\Bundle\Model\WebUiMessage;
use Translation\Common\Exception\StorageException;
use Translation\Bundle\Model\CatalogueMessage;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class WebUIController extends Controller
{
    /**
     * Show a dashboard for the configuration.
     *
     * @param string|null $configName
     *
     * @return Response
     */
    public function indexAction($configName = null)
    {
        $config = $this->getConfiguration($configName);
        $localeMap = $this->getLocale2LanguageMap();
        $catalogues = $this->get('php_translation.catalogue_fetcher')->getCatalogues(array_keys($localeMap), [$config['output_dir']]);

        $catalogueSize = [];
        $maxDomainSize = [];
        $maxCatalogueSize = 1;

        // For each catalogue (or locale)
        /** @var MessageCatalogue $catalogue */
        foreach ($catalogues as $catalogue) {
            $locale = $catalogue->getLocale();
            $domains = $catalogue->all();
            ksort($domains);
            $catalogueSize[$locale] = 0;
            foreach ($domains as $domain => $messages) {
                $count = count($messages);
                $catalogueSize[$locale] += $count;
                if (!isset($maxDomainSize[$domain]) || $count > $maxDomainSize[$domain]) {
                    $maxDomainSize[$domain] = $count;
                }
            }

            if ($catalogueSize[$locale] > $maxCatalogueSize) {
                $maxCatalogueSize = $catalogueSize[$locale];
            }
        }

        return $this->render('TranslationBundle:WebUI:index.html.twig', [
            'catalogues' => $catalogues,
            'catalogueSize' => $catalogueSize,
            'maxDomainSize' => $maxDomainSize,
            'maxCatalogueSize' => $maxCatalogueSize,
            'localeMap' => $localeMap,
            'configName' => $configName,
            'configNames' => $this->get('php_translation.configuration_manager')->getNames(),
        ]);
    }

    /**
     * Show a catalogue.
     *
     * @param string $configName
     * @param string $locale
     * @param string $domain
     *
     * @return Response
     */
    public function showAction($configName, $locale, $domain)
    {
        $config = $this->getConfiguration($configName);
        $locales = $this->getParameter('php_translation.locales');

        // Get a catalogue manager and load it with all the catalogues
        $catalogueManager = $this->get('php_translation.catalogue_manager');
        $catalogueManager->load($this->get('php_translation.catalogue_fetcher')->getCatalogues($locales, [$config['output_dir']]));

        /** @var CatalogueMessage[] $messages */
        $messages = $catalogueManager->getMessages($locale, $domain);
        usort($messages, function (CatalogueMessage $a, CatalogueMessage $b) {
            return strcmp($a->getKey(), $b->getKey());
        });

        return $this->render('TranslationBundle:WebUI:show.html.twig', [
            'messages' => $messages,
            'domains' => $catalogueManager->getDomains(),
            'currentDomain' => $domain,
            'locales' => $locales,
            'currentLocale' => $locale,
            'configName' => $configName,
            'configNames' => $this->get('php_translation.configuration_manager')->getNames(),
        ]);
    }

    /**
     * @param Request $request
     * @param string  $domain
     *
     * @return Response
     */
    public function createAction(Request $request, $configName, $locale, $domain)
    {
        $storage = $this->get('php_translation.storage.file.'.$configName);
        try {
            $message = $this->getMessage($request, ['Create']);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), 400);
        }

        try {
            $storage->set($locale, $domain, $message->getKey(), $message->getMessage());
        } catch (StorageException $e) {
            throw new BadRequestHttpException(sprintf(
                'Key "%s" does already exist for "%s" on domain "%s".',
                $message->getKey(),
                $locale,
                $domain
            ), $e);
        }

        return new Response('Translation created');
    }

    /**
     * @param Request $request
     * @param string  $configName
     * @param string  $locale
     * @param string  $domain
     *
     * @return Response
     */
    public function editAction(Request $request, $configName, $locale, $domain)
    {
        try {
            $message = $this->getMessage($request, ['Edit']);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), 400);
        }

        $this->get('php_translation.storage.file.'.$configName)->update($locale, $domain, $message->getKey(), $message->getMessage());

        return new Response('Translation updated');
    }

    /**
     * @param Request $request
     * @param string  $configName
     * @param string  $locale
     * @param string  $domain
     *
     * @return Response
     */
    public function deleteAction(Request $request, $configName, $locale, $domain)
    {
        try {
            $message = $this->getMessage($request, ['Delete']);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), 400);
        }

        $this->get('php_translation.storage.file.'.$configName)->delete($locale, $domain, $message->getKey());

        return new Response('Message was deleted');
    }

    /**
     * @param $configName
     *
     * @return array
     */
    private function getConfiguration(&$configName)
    {
        $configurationManager = $this->get('php_translation.configuration_manager');
        $configName = $configName !== null ? $configName : $configurationManager->getFirstName();
        if ($configName === null) {
            throw new \LogicException('You must configure at least one key under translation.configs');
        }

        $config = $configurationManager->getConfiguration($configName);

        if (empty($config)) {
            throw $this->createNotFoundException('No translation configuration named "'.$configName.'" was found.');
        }

        return $config;
    }

    /**
     * @param Request $request
     * @param array   $validationGroups
     *
     * @return WebUiMessage
     */
    private function getMessage(Request $request, array $validationGroups = [])
    {
        $json = $request->getContent();
        $data = json_decode($json, true);
        $message = new WebUiMessage();
        if (isset($data['key'])) {
            $message->setKey($data['key']);
        }
        if (isset($data['message'])) {
            $message->setMessage($data['message']);
        }

        $errors = $this->get('validator')->validate($message, null, $validationGroups);
        if (count($errors) > 0) {
            throw  MessageValidationException::create();
        }

        return $message;
    }

    /**
     * This will return a map of our configured locales and their language name.
     *
     * @return array locale => language
     */
    private function getLocale2LanguageMap()
    {
        $configuedLocales = $this->getParameter('php_translation.locales');
        $names = Intl::getLocaleBundle()->getLocaleNames('en');
        $map = [];
        foreach ($configuedLocales as $l) {
            $map[$l] = $names[$l];
        }

        return $map;
    }
}
