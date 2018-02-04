<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Model;

use Symfony\Component\VarDumper\Cloner\Data;
use Translation\Common\Model\Message;
use Translation\Common\Model\MessageInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class SfProfilerMessage
{
    /**
     * @var int
     *
     * This is the number of times the message occurs on a specific page
     */
    private $count;

    /**
     * @var string
     *
     * The domain the message belongs to
     */
    private $domain;

    /**
     * @var string
     *
     * The key/phrase you write in the source code
     */
    private $key;

    /**
     * @var string
     *
     * The locale the translations is on
     */
    private $locale;

    /**
     * @var int
     *
     * The current state of the translations. See Symfony\Component\Translation\DataCollectorTranslator
     *
     * MESSAGE_DEFINED = 0;
     * MESSAGE_MISSING = 1;
     * MESSAGE_EQUALS_FALLBACK = 2;
     */
    private $state;

    /**
     * @var string
     *
     * The translated string. This is the preview of the message. Ie no placeholders is visible.
     */
    private $translation;

    /**
     * @var int
     *
     * The number which we are feeding a transChoice with
     * Used only in Symfony >2.8
     */
    private $transChoiceNumber;

    /**
     * @var array
     *
     * The parameters sent to the translations
     * Used only in Symfony >2.8
     */
    private $parameters;

    /**
     * @param array $data
     *
     * @return SfProfilerMessage
     */
    public static function create(array $data)
    {
        $message = new self();
        if (isset($data['id'])) {
            $message->setKey($data['id']);
        }
        if (isset($data['domain'])) {
            $message->setDomain($data['domain']);
        }
        if (isset($data['locale'])) {
            $message->setLocale($data['locale']);
        }
        if (isset($data['translation'])) {
            $message->setTranslation($data['translation']);
        }
        if (isset($data['state'])) {
            $message->setState($data['state']);
        }
        if (isset($data['count'])) {
            $message->setCount($data['count']);
        }
        if (isset($data['transChoiceNumber'])) {
            $message->setTransChoiceNumber($data['transChoiceNumber']);
        }
        if (isset($data['parameters'])) {
            $message->setParameters($data['parameters']);
        }

        return $message;
    }

    /**
     * Convert to a Common\Model\MessageInterface.
     *
     * @return MessageInterface
     */
    public function convertToMessage()
    {
        $meta = [];

        if ($this->hasParameters()) {
            // Reduce to only get one value of each parameter, not all the usages.
            $meta['parameters'] = array_reduce($this->getParameters(), 'array_merge', []);
        }

        if (!empty($this->getCount())) {
            $meta['count'] = $this->getCount();
        }

        if (!empty($this->getTransChoiceNumber())) {
            $meta['transChoiceNumber'] = $this->getTransChoiceNumber();
        }

        return new Message(
            $this->key,
            $this->domain,
            $this->locale,
            $this->translation,
            $meta
        );
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param string $translation
     *
     * @return $this
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * @return int
     */
    public function getTransChoiceNumber()
    {
        return $this->transChoiceNumber;
    }

    /**
     * @param int $transChoiceNumber
     *
     * @return $this
     */
    public function setTransChoiceNumber($transChoiceNumber)
    {
        $this->transChoiceNumber = $transChoiceNumber;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        $pure = [];
        foreach ($this->parameters as $p) {
            if ($p instanceof Data) {
                $p = $p->getRawData();
            }
            $pure[] = $p;
        }

        return $pure;
    }

    /**
     * @return bool
     */
    public function hasParameters()
    {
        return !empty($this->parameters);
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
