<?php

namespace Translation\Bundle\Model;

/**
 * Message metadata abstraction
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
     *
     * @param array $metadata
     */
    public function __construct(array $metadata)
    {
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
        $this->notes[] = ['category'=>'state', 'content'=> $state];
    }


    /**
     * @return bool
     */
    public function isApproved()
    {
        $notes = $this->getAllInCategory('approved');
        foreach ($notes as $note) {
            if (isset($note['content'])) {
                return $note['content'] === 'true';
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
        $this->notes[] = ['category'=>'approved', 'content'=> $bool ? 'true': 'false'];
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
    private function getAllInCategory($category)
    {
        $data = [];
        foreach ($this->notes as &$note) {
            if ($note['category'] === $category) {
                if (!isset($note['priority'])) {
                    $note['priority'] = "1";
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
     * Remove all metadata in category
     *
     * @param string $category
     */
    private function removeAllInCategory($category)
    {
        foreach ($this->notes as $i => $note) {
            if ($note['category'] === $category) {
                unset($this->notes[$i]);
            }
        }
    }
}
