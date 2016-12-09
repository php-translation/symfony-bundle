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

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class WebUiMessage
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
     * @return WebUiMessage
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
     * @return WebUiMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
