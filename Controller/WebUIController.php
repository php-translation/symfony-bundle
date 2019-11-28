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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Catalogue\CatalogueManager;
use Translation\Bundle\Exception\MessageValidationException;
use Translation\Bundle\Model\CatalogueMessage;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Service\StorageManager;
use Translation\Bundle\Service\StorageService;
use Translation\Common\Exception\StorageException;
use Translation\Common\Model\Message;
use Translation\Common\Model\MessageInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class WebUIController extends AbstractController
{
    /**
     * @var StorageManager
     */
    private $storageManager;

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @var CatalogueManager
     */
    private $catalogueManager;

    /**
     * @var CatalogueFetcher
     */
    private $catalogueFetcher;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        StorageManager $storageManager,
        ConfigurationManager $configurationManager,
        CatalogueManager $catalogueManager,
        CatalogueFetcher $catalogueFetcher,
        ValidatorInterface $validator
    ) {
        $this->storageManager = $storageManager;
        $this->configurationManager = $configurationManager;
        $this->catalogueManager = $catalogueManager;
        $this->catalogueFetcher = $catalogueFetcher;
        $this->validator = $validator;
    }

    /**
     * Show a dashboard for the configuration.
     */
    public function indexAction(?string $configName = null): Response
    {
        if (!$this->getParameter('php_translation.webui.enabled')) {
            return new Response('You are not allowed here. Check you config. ', 400);
        }

        $config = $this->configurationManager->getConfiguration($configName);
        $localeMap = $this->getLocale2LanguageMap();
        $catalogues = $this->catalogueFetcher->getCatalogues($config);

        $catalogueSize = [];
        $maxDomainSize = [];
        $maxCatalogueSize = 1;

        // For each catalogue (or locale)
        /** @var MessageCatalogue $catalogue */
        foreach ($catalogues as $catalogue) {
            $locale = $catalogue->getLocale();
            $domains = $catalogue->all();
            \ksort($domains);
            $catalogueSize[$locale] = 0;
            foreach ($domains as $domain => $messages) {
                $count = \count($messages);
                $catalogueSize[$locale] += $count;
                if (!isset($maxDomainSize[$domain]) || $count > $maxDomainSize[$domain]) {
                    $maxDomainSize[$domain] = $count;
                }
            }

            if ($catalogueSize[$locale] > $maxCatalogueSize) {
                $maxCatalogueSize = $catalogueSize[$locale];
            }
        }

        return $this->render('@Translation/WebUI/index.html.twig', [
            'catalogues' => $catalogues,
            'catalogueSize' => $catalogueSize,
            'maxDomainSize' => $maxDomainSize,
            'maxCatalogueSize' => $maxCatalogueSize,
            'localeMap' => $localeMap,
            'configName' => $config->getName(),
            'configNames' => $this->configurationManager->getNames(),
        ]);
    }

    /**
     * Show a catalogue.
     */
    public function showAction(string $configName, string $locale, string $domain): Response
    {
        if (!$this->getParameter('php_translation.webui.enabled')) {
            return new Response('You are not allowed here. Check you config. ', 400);
        }

        $config = $this->configurationManager->getConfiguration($configName);

        // Get a catalogue manager and load it with all the catalogues
        $this->catalogueManager->load($this->catalogueFetcher->getCatalogues($config));

        /** @var CatalogueMessage[] $messages */
        $messages = $this->catalogueManager->getMessages($locale, $domain);
        \usort($messages, function (CatalogueMessage $a, CatalogueMessage $b) {
            return \strcmp($a->getKey(), $b->getKey());
        });

        return $this->render('@Translation/WebUI/show.html.twig', [
            'messages' => $messages,
            'domains' => $this->catalogueManager->getDomains(),
            'currentDomain' => $domain,
            'locales' => $this->getParameter('php_translation.locales'),
            'currentLocale' => $locale,
            'configName' => $config->getName(),
            'configNames' => $this->configurationManager->getNames(),
            'allow_create' => $this->getParameter('php_translation.webui.allow_create'),
            'allow_delete' => $this->getParameter('php_translation.webui.allow_delete'),
            'file_base_path' => $this->getParameter('php_translation.webui.file_base_path'),
        ]);
    }

    public function createAction(Request $request, string $configName, string $locale, string $domain): Response
    {
        if (!$this->getParameter('php_translation.webui.enabled') || !$this->getParameter('php_translation.webui.allow_create')) {
            return new Response('You are not allowed to create. Check you config. ', 400);
        }

        /** @var StorageService $storage */
        $storage = $this->storageManager->getStorage($configName);

        try {
            $message = $this->getMessageFromRequest($request);
            $message = $message->withDomain($domain);
            $message = $message->withLocale($locale);
            $this->validateMessage($message, ['Create']);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), 400);
        }

        try {
            $storage->create($message);
        } catch (StorageException $e) {
            throw new BadRequestHttpException(\sprintf('Key "%s" does already exist for "%s" on domain "%s".', $message->getKey(), $locale, $domain), $e);
        }

        return $this->render('@Translation/WebUI/create.html.twig', [
            'message' => $message,
        ]);
    }

    public function editAction(Request $request, string $configName, string $locale, string $domain): Response
    {
        if (!$this->getParameter('php_translation.webui.enabled')) {
            return new Response('You are not allowed here. Check you config. ', 400);
        }

        try {
            $message = $this->getMessageFromRequest($request);
            $message = $message->withDomain($domain);
            $message = $message->withLocale($locale);
            $this->validateMessage($message, ['Edit']);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), 400);
        }

        /** @var StorageService $storage */
        $storage = $this->storageManager->getStorage($configName);
        $storage->update($message);

        return new Response('Translation updated');
    }

    public function deleteAction(Request $request, string $configName, string $locale, string $domain): Response
    {
        if (!$this->getParameter('php_translation.webui.enabled') || !$this->getParameter('php_translation.webui.allow_delete')) {
            return new Response('You are not allowed to create. Check you config. ', 400);
        }

        try {
            $message = $this->getMessageFromRequest($request);
            $message = $message->withLocale($locale);
            $message = $message->withDomain($domain);
            $this->validateMessage($message, ['Delete']);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), 400);
        }

        /** @var StorageService $storage */
        $storage = $this->storageManager->getStorage($configName);
        $storage->delete($locale, $domain, $message->getKey());

        return new Response('Message was deleted');
    }

    private function getMessageFromRequest(Request $request): MessageInterface
    {
        $json = $request->getContent();
        $data = \json_decode($json, true);
        $message = new Message($data['key']);
        if (isset($data['message'])) {
            $message = $message->withTranslation($data['message']);
        }

        return $message;
    }

    /**
     * This will return a map of our configured locales and their language name.
     *
     * @return array locale => language
     */
    private function getLocale2LanguageMap(): array
    {
        $configuredLocales = $this->getParameter('php_translation.locales');
        $names = \class_exists(Locales::class)
            ? Locales::getNames('en')
            : Intl::getLocaleBundle()->getLocaleNames('en');
        $map = [];
        foreach ($configuredLocales as $l) {
            $map[$l] = $names[$l] ?? $l;
        }

        return $map;
    }

    /**
     * @throws MessageValidationException
     */
    private function validateMessage(MessageInterface $message, array $validationGroups): void
    {
        $errors = $this->validator->validate($message, null, $validationGroups);
        if (\count($errors) > 0) {
            throw  MessageValidationException::create();
        }
    }
}
