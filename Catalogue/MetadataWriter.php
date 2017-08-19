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

use Symfony\Component\Translation\MessageCatalogue;

/**
 * Write metadata to the catalogue.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class MetadataWriter
{
    /**
     * @param MessageCatalogue $catalogue
     * @param string           $key       message key
     * @param string           $domain
     * @param string           $type      the type of metadata. "notes" is a common one
     * @param mixed            $value
     */
    public function write(MessageCatalogue $catalogue, $key, $domain, $type, $value)
    {
        $meta = $catalogue->getMetadata($key, $domain);
        if (!isset($meta[$type])) {
            $meta[$type] = [];
        }

        if ($this->exists($meta[$type], $value)) {
            return;
        }

        $meta[$type][] = $value;
        $catalogue->setMetadata($key, $meta, $domain);
    }

    /**
     * @param array $metadata
     * @param mixed $value
     *
     * @return bool
     */
    private function exists($metadata, $value)
    {
        foreach ($metadata as $data) {
            if ($data == $value) {
                return true;
            }
        }

        return false;
    }
}
