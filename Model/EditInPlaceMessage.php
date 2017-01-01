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
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class EditInPlaceMessage
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
     * @var string
     * @Assert\NotBlank(groups={"Create", "Edit", "Delete"})
     */
    private $domain;

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
     * @return EditInPlaceMessage
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
     * @return EditInPlaceMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;

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
     * @return EditInPlaceMessage
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }
}
