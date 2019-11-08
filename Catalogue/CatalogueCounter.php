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

use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\MetadataAwareInterface;
use Translation\Bundle\Model\Metadata;

/**
 * Calculate the number of messages in a catalogue.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CatalogueCounter
{
    public function getNumberOfDefinedMessages(MessageCatalogueInterface $catalogue): int
    {
        $total = 0;
        foreach ($catalogue->getDomains() as $domain) {
            $total += \count($catalogue->all($domain));
        }

        return $total;
    }

    public function getCatalogueStatistics(MessageCatalogueInterface $catalogue): array
    {
        $result = [];
        $domains = $catalogue->getDomains();
        foreach ($domains as $domain) {
            $result[$domain]['defined'] = \count($catalogue->all($domain));
        }

        // Sum the number of defined messages.
        $result['_total']['defined'] = 0;
        foreach ($domains as $domain) {
            $result['_total']['defined'] += $result[$domain]['defined'];
        }

        if (!$catalogue instanceof MetadataAwareInterface) {
            return $result;
        }

        // For each domain check if the message is new or undefined.
        foreach ($domains as $domain) {
            $result[$domain]['new'] = 0;
            $result[$domain]['obsolete'] = 0;
            $result[$domain]['approved'] = 0;

            foreach ($catalogue->all($domain) as $key => $text) {
                $metadata = new Metadata($catalogue->getMetadata($key, $domain));
                $state = $metadata->getState();
                if ('new' === $state) {
                    ++$result[$domain]['new'];
                }

                if ('obsolete' === $state) {
                    ++$result[$domain]['obsolete'];
                }

                if ($metadata->isApproved()) {
                    ++$result[$domain]['approved'];
                }
            }
        }

        // Sum the number of new and obsolete messages.
        $result['_total']['new'] = 0;
        $result['_total']['obsolete'] = 0;
        $result['_total']['approved'] = 0;
        foreach ($domains as $domain) {
            $result['_total']['new'] += $result[$domain]['new'];
            $result['_total']['obsolete'] += $result[$domain]['obsolete'];
            $result['_total']['approved'] += $result[$domain]['approved'];
        }

        return $result;
    }
}
