<?php

namespace Translation\Bundle\Catalogue;

use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\MetadataAwareInterface;

/**
 * Calculate the number of messages in a catalogue.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CatalogueCounter
{
    /**
     * @var NoteParser
     */
    private $noteParser;

    /**
     *
     * @param NoteParser $noteParser
     */
    public function __construct(NoteParser $noteParser)
    {
        $this->noteParser = $noteParser;
    }

    /**
     * @param MessageCatalogueInterface $catalogue
     *
     * @return int
     */
    public function getNumberOfDefinedMessages(MessageCatalogueInterface $catalogue)
    {
        $total = 0;
        foreach ($catalogue->getDomains() as $domain) {
            $total += count($catalogue->all($domain));
        }

        return $total;
    }

    /**
     * @param MessageCatalogueInterface $catalogue
     * @return array
     */
    public function getCatalogueStatistics(MessageCatalogueInterface $catalogue)
    {
        $result = [];
        $domains = $catalogue->getDomains();
        foreach ($domains as $domain) {
            $result[$domain]['defined'] = count($catalogue->all($domain));
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

            foreach ($catalogue->all($domain) as $key => $text) {
                $notes = $this->noteParser->getNotes($domain, $key, $catalogue);
                if ($this->noteParser->hasNoteNew($notes)) {
                    ++$result[$domain]['new'];
                }

                if ($this->noteParser->hasNoteObsolete($notes)) {
                    ++$result[$domain]['obsolete'];
                }
            }
        }

        // Sum the number of new and obsolete messages.
        $result['_total']['new'] = 0;
        $result['_total']['obsolete'] = 0;
        foreach ($domains as $domain) {
            $result['_total']['new'] += $result[$domain]['new'];
            $result['_total']['obsolete'] += $result[$domain]['obsolete'];
        }

        return $result;
    }
}
