<?php

namespace Translation\Bundle\Model;

/**
 *
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SfProfilerMessage
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
    private $id;

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
     *                    array( count = 1, domain = "navigation", id = "logout", locale = "sv", state = 1, translation = "logout" )
     */
    public function __construct(array $data)
    {
        $this->domain = $data['domain'];
        $this->id = $data['id'];
        $this->locale = $data['locale'];
        $this->state = $data['state'];
        $this->translation = $data['translation'];

        if (isset($data['count'])) {
            $this->count = $data['count'];
        }

        if (isset($data['transChoiceNumber'])) {
            $this->transChoiceNumber = $data['transChoiceNumber'];
        }

        if (isset($data['parameters'])) {
            $this->parameters = $data['parameters'];
        }
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

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
        return $this->parameters;
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
