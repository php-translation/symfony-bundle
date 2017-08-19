<?php

namespace Translation\Bundle\Model;

use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\Extractor\Model\Error;

/**
 * The result from the Importer.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ImportResult
{
    /**
     * @var MessageCatalogueInterface[]
     */
    private $messageCatalogues;

    /**
     * @var Error[]
     */
    private $errors;

    /**
     * @param MessageCatalogueInterface[] $messageCatalogues
     * @param Error[]                     $errors
     */
    public function __construct(array $messageCatalogues, array $errors)
    {
        $this->messageCatalogues = $messageCatalogues;
        $this->errors = $errors;
    }

    /**
     * @return MessageCatalogueInterface[]
     */
    public function getMessageCatalogues()
    {
        return $this->messageCatalogues;
    }

    /**
     * @return Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
