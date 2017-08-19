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

use Symfony\Component\Translation\MetadataAwareInterface;

/**
 * Parse a "note" for data.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
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
