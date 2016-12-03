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
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Translator;
use Translation\Symfony\Model\Message;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class WebUIController extends Controller
{
    public function indexAction($configName = null)
    {
        $config = $this->getConfiguration($configName);

        $configuedLocales = $this->getParameter('php_translation.locales');
        $allLocales = Intl::getLocaleBundle()->getLocaleNames('en');
        $locales = [];
        foreach ($configuedLocales as $l) {
            $locales[$l] = $allLocales[$l];
        }
        $catalogues = $this->get('php_translation.catalogue_fetcher')->getCatalogues($configuedLocales, [$config['output_dir']]);
        $catalogueSize = [];
        $maxDomainSize = [];
        $maxCatalogueSize = 1;
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
            'locales' => $locales,
            'configName' => $configName,
            'configNames' => $this->get('php_translation.configuration_manager')->getNames(),
        ]);
    }

    /**
     * @param $locale
     * @param $domain
     *
     * @return Response
     */
    public function showAction($configName, $locale, $domain)
    {
        $config = $this->getConfiguration($configName);
        $locales = $this->getParameter('php_translation.locales');
        /** @var Translator $translator */
        $catalogues = $this->get('php_translation.catalogue_fetcher')->getCatalogues($locales, [$config['output_dir']]);
        $catalogueManager = $this->get('php_translation.catalogue_manager');
        $catalogueManager->load($catalogues);

        /** @var Message[] $messages */
        $messages = $catalogueManager->getMessages($locale, $domain);
        usort($messages, function (Message $a, Message $b) {
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
        // TODO use the Translator\Common\Storage interface
        return new Response('Not yet implemented');
    }

    /**
     * @param Request $request
     * @param string  $domain
     *
     * @return Response
     */
    public function editAction(Request $request, $domain)
    {
        return new Response('Not yet implemented');
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
            throw new \LogicException('You must configure at least one key under translation.config');
        }

        $config = $configurationManager->getConfiguration($configName);

        if (empty($config)) {
            throw $this->createNotFoundException('No translation configuration named "'.$configName.'" was found.');
        }

        return $config;
    }
}
