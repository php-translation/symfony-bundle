<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Catalogue;

use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\MetadataAwareInterface;
use Translation\Bundle\Model\CatalogueMessage;
use Translation\Bundle\Model\Metadata;

/**
 * A manager that handle loaded catalogues.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CatalogueManager
{
    /**
     * @var MessageCatalogueInterface[]
     */
    private $catalogues;

    /**
     * @param MessageCatalogueInterface[] $catalogues
     */
    public function load(array $catalogues)
    {
        $this->catalogues = [];
        foreach ($catalogues as $c) {
            $this->catalogues[$c->getLocale()] = $c;
        }
    }

    /**
     * @return array
     */
    public function getDomains()
    {
        /** @var MessageCatalogueInterface $c */
        $c = reset($this->catalogues);

        return $c->getDomains();
    }

    /**
     * @param string $locale
     * @param string $domain
     *
     * @return CatalogueMessage[]
     */
    public function getMessages($locale, $domain)
    {
        $messages = [];
        if (!isset($this->catalogues[$locale])) {
            return $messages;
        }

        foreach ($this->catalogues[$locale]->all($domain) as $key => $text) {
            $messages[] = $this->createMessage($this->catalogues[$locale], $locale, $domain, $key, $text);
        }

        return $messages;
    }

    /**
     * @param array $config {
     *
     *      @var string $domain
     *      @var string $locale
     *      @var bool $isNew
     *      @var bool $isObsolete
     *      @var bool $isApproved
     * }
     *
     * @return CatalogueMessage[]
     */
    public function findMessages(array $config = [])
    {
        $inputDomain = isset($config['domain']) ? $config['domain'] : null;
        $isNew = isset($config['isNew']) ? $config['isNew'] : null;
        $isObsolete = isset($config['isObsolete']) ? $config['isObsolete'] : null;
        $isApproved = isset($config['isApproved']) ? $config['isApproved'] : null;

        $messages = [];
        $catalogues = [];
        if (isset($config['locale'])) {
            $locale = $config['locale'];
            if (isset($this->catalogues[$locale])) {
                $catalogues = [$locale => $this->catalogues[$locale]];
            }
        } else {
            $catalogues = $this->catalogues;
        }

        foreach ($catalogues as $locale => $catalogue) {
            $domains = $catalogue->getDomains();
            if (null !== $inputDomain) {
                $domains = [$inputDomain];
            }
            foreach ($domains as $domain) {
                foreach ($catalogue->all($domain) as $key => $text) {
                    $messages[] = $this->createMessage($catalogue, $locale, $domain, $key, $text);
                }
            }
        }

        $messages = array_filter($messages, function (CatalogueMessage $m) use ($isNew, $isObsolete, $isApproved) {
            if (null !== $isNew && $m->isNew() !== $isNew) {
                return false;
            }
            if (null !== $isObsolete && $m->isObsolete() !== $isObsolete) {
                return false;
            }
            if (null !== $isApproved && $m->isApproved() !== $isApproved) {
                return false;
            }

            return true;
        });

        return $messages;
    }

    /**
     * @param string $domain
     * @param string $key
     *
     * @return array
     */
    public function getTranslations($domain, $key)
    {
        $translations = [];
        foreach ($this->catalogues as $locale => $catalogue) {
            if ($catalogue->has($key, $domain)) {
                $translations[$locale] = $catalogue->get($key, $domain);
            }
        }

        return $translations;
    }

    /**
     * @param MessageCatalogueInterface $catalogue
     * @param $locale
     * @param $domain
     * @param $key
     * @param $text
     *
     * @return CatalogueMessage
     */
    private function createMessage(MessageCatalogueInterface $catalogue, $locale, $domain, $key, $text)
    {
        $catalogueMessage = new CatalogueMessage($this, $locale, $domain, $key, $text);

        if ($catalogue instanceof MetadataAwareInterface) {
            $catalogueMessage->setMetadata(new Metadata($catalogue->getMetadata($key, $domain)));
        }

        return $catalogueMessage;
    }
}
