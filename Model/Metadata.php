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

/**
 * Message metadata abstraction.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class Metadata
{
    /**
     * @var array
     */
    private $metadata;

    /**
     * @var array
     */
    private $notes = [];

    /**
     * @param array $metadata
     */
    public function __construct($metadata)
    {
        if (empty($metadata)) {
            $metadata = [];
        }

        $this->metadata = $metadata;
        if (isset($metadata['notes'])) {
            $this->notes = $metadata['notes'];
        }
    }

    /**
     * @return string|null
     */
    public function getState()
    {
        $notes = $this->getAllInCategory('state');
        foreach ($notes as $note) {
            if (isset($note['content'])) {
                return $note['content'];
            }
        }
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->removeAllInCategory('state');
        $this->addCategory('state', $state);
    }

    /**
     * @return null|string
     */
    public function getDesc()
    {
        $notes = $this->getAllInCategory('desc');
        foreach ($notes as $note) {
            if (isset($note['content'])) {
                return $note['content'];
            }
        }

        return null;
    }

    /**
     * Get the extracted translation if any.
     *
     * @return null|string
     */
    public function getTranslation()
    {
        $notes = $this->getAllInCategory('translation');
        foreach ($notes as $note) {
            if (isset($note['content'])) {
                return $note['content'];
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        $notes = $this->getAllInCategory('approved');
        foreach ($notes as $note) {
            if (isset($note['content'])) {
                return 'true' === $note['content'];
            }
        }

        return false;
    }

    /**
     * @param bool $bool
     */
    public function setApproved($bool)
    {
        $this->removeAllInCategory('approved');
        $this->addCategory('approved', $bool ? 'true' : 'false');
    }

    /**
     * @return array
     */
    public function getSourceLocations()
    {
        $sources = [];
        $notes = $this->getAllInCategory('file-source');
        foreach ($notes as $note) {
            if (!isset($note['content'])) {
                continue;
            }
            list($path, $line) = explode(':', $note['content'], 2);
            $sources[] = ['path' => $path, 'line' => $line];
        }

        return $sources;
    }

    /**
     * Add metadata.
     *
     * @param string $name
     * @param string $content
     */
    public function addCategory($name, $content, $priority = 1)
    {
        $this->notes[] = ['category' => $name, 'content' => $content, 'priority' => $priority];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $metadata = $this->metadata;
        $metadata['notes'] = $this->notes;

        return $metadata;
    }

    /**
     * Return all notes for one category. It will also order data according to priority.
     *
     * @param string $category
     *
     * @return array
     */
    public function getAllInCategory($category)
    {
        $data = [];
        foreach ($this->notes as $note) {
            if (!isset($note['category'])) {
                continue;
            }
            if ($note['category'] === $category) {
                if (!isset($note['priority'])) {
                    $note['priority'] = '1';
                }
                $data[] = $note;
            }
        }

        usort($data, function (array $a, array $b) {
            return (int) $a['priority'] - (int) $b['priority'];
        });

        return $data;
    }

    /**
     * Remove all metadata in category.
     *
     * @param string $category
     */
    public function removeAllInCategory($category)
    {
        foreach ($this->notes as $i => $note) {
            if (isset($note['category']) && $note['category'] === $category) {
                unset($this->notes[$i]);
            }
        }
    }
}
