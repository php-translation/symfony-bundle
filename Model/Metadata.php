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
    private $metadata = [];

    /**
     * @var array
     */
    private $notes = [];

    /**
     * @param array|null $metadata
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

    public function getState(): ?string
    {
        $notes = $this->getAllInCategory('state');
        foreach ($notes as $note) {
            if (isset($note['content'])) {
                return $note['content'];
            }
        }

        return null;
    }

    public function setState(string $state): void
    {
        $this->removeAllInCategory('state');
        $this->addCategory('state', $state);
    }

    public function getDesc(): ?string
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
     */
    public function getTranslation(): ?string
    {
        $notes = $this->getAllInCategory('translation');
        foreach ($notes as $note) {
            if (isset($note['content'])) {
                return $note['content'];
            }
        }

        return null;
    }

    public function isApproved(): bool
    {
        $notes = $this->getAllInCategory('approved');
        foreach ($notes as $note) {
            if (isset($note['content'])) {
                return 'true' === $note['content'];
            }
        }

        return false;
    }

    public function setApproved(bool $bool): void
    {
        $this->removeAllInCategory('approved');
        $this->addCategory('approved', $bool ? 'true' : 'false');
    }

    public function getSourceLocations(): array
    {
        $sources = [];
        $notes = $this->getAllInCategory('file-source');
        foreach ($notes as $note) {
            if (!isset($note['content'])) {
                continue;
            }
            list($path, $line) = \explode(':', $note['content'], 2);
            $sources[] = ['path' => $path, 'line' => $line];
        }

        return $sources;
    }

    /**
     * Add metadata.
     */
    public function addCategory(string $name, string $content, int $priority = 1): void
    {
        $this->notes[] = ['category' => $name, 'content' => $content, 'priority' => $priority];
    }

    public function toArray(): array
    {
        $metadata = $this->metadata;
        $metadata['notes'] = $this->notes;

        return $metadata;
    }

    /**
     * Return all notes for one category. It will also order data according to priority.
     */
    public function getAllInCategory(string $category): array
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

        \usort($data, static function (array $a, array $b) {
            return (int) $a['priority'] - (int) $b['priority'];
        });

        return $data;
    }

    /**
     * Remove all metadata in category.
     */
    public function removeAllInCategory(string $category): void
    {
        foreach ($this->notes as $i => $note) {
            if (isset($note['category']) && $note['category'] === $category) {
                unset($this->notes[$i]);
            }
        }
    }
}
