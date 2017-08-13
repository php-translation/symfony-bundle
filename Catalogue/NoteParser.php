<?php


namespace Translation\Bundle\Catalogue;

use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\MetadataAwareInterface;

class NoteParser
{

    /**
     * @param $domain
     * @param $key
     * @param MetadataAwareInterface $catalogue
     * 
     * @return array
     */
    public function getNotes($domain, $key, MetadataAwareInterface $catalogue)
    {
        $meta = $catalogue->getMetadata($key, $domain);

        if (!isset($meta['notes'])) {
            return [];
        }

        return $meta['notes'];
    }

    /**
     * @param array $notes
     *
     * @return bool
     */
    public function hasNoteObsolete(array $notes)
    {
        foreach ($notes as $note) {
            if ($note['content'] === 'status:obsolete') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $notes
     *
     * @return bool
     */
    public function hasNoteNew(array $notes)
    {
        foreach ($notes as $note) {
            if ($note['content'] === 'status:new') {
                return true;
            }
        }

        return false;
    }
}
