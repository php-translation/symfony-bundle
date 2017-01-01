<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Catalogue\Operation;

use Symfony\Component\Translation\Catalogue\AbstractOperation;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
final class MetadataAwareMerge extends AbstractOperation
{
    protected function processDomain($domain)
    {
        $this->messages[$domain] = [
            'all' => [],
            'new' => [],
            'obsolete' => [],
        ];
        $targetMessages = $this->target->all($domain);

        foreach ($this->source->all($domain) as $id => $message) {
            $this->messages[$domain]['all'][$id] = $message;
            $this->result->add([$id => $message], $domain);
            if (empty($message)) {
                $this->messages[$domain]['new'][$id] = $message;
            }

            // If $id is NOT defined in target.
            if (!array_key_exists($id, $targetMessages)) {
                $this->messages[$domain]['obsolete'][$id] = $message;
            }

            if (null !== $keySourceMetadata = $this->source->getMetadata($id, $domain)) {
                $this->result->setMetadata($id, $keySourceMetadata, $domain);
            }

            // Get metadata from target
            if (null !== $keyTargetMetadata = $this->target->getMetadata($id, $domain)) {
                if (null === $keySourceMetadata) {
                    // If there were no metadata in source. Just use target's metadata
                    $this->result->setMetadata($id, $keyTargetMetadata, $domain);
                    continue;
                }

                // Merge metadata
                $resultMetadata = $this->mergeMetaData($keySourceMetadata, $keyTargetMetadata);
                $this->result->setMetadata($id, $resultMetadata, $domain);
            }
        }

        foreach ($targetMessages as $id => $message) {
            if (!$this->source->has($id, $domain)) {
                $this->messages[$domain]['all'][$id] = $message;
                $this->messages[$domain]['new'][$id] = $message;

                $this->result->add([$id => $message], $domain);
                if (null !== $keyMetadata = $this->target->getMetadata($id, $domain)) {
                    $this->result->setMetadata($id, $keyMetadata, $domain);
                }
            }
        }
    }

    /**
     * @param array $source
     * @param array $target
     *
     * @return array
     */
    private function mergeMetadata(array $source, array $target)
    {
        $toRemove = ['status:new', 'status:obsolete', 'file-source'];
        $resultNotes = [];

        // Remove some old notes
        if (isset($source['notes'])) {
            foreach ($source['notes'] as $note) {
                if (isset($note['content']) && in_array($note['content'], $toRemove)) {
                    continue;
                }
                $resultNotes[] = $note;
            }
        }

        if (isset($target['notes'])) {
            foreach ($target['notes'] as $note) {
                $resultNotes[] = $note;
            }
        }

        $result = $source;
        $result['notes'] = $resultNotes;

        return $result;
    }
}
