<?php

namespace Translation\Bundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class GuiMessageRepresentation
{
    /**
     * @var string
     * @Assert\NotBlank(groups={"Create", "Edit", "Delete"})
     */
    private $key;

    /**
     * @var string
     * @Assert\NotBlank(groups={"Create", "Edit"})
     */
    private $message;

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
     * @return GuiMessageRepresentation
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return GuiMessageRepresentation
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
