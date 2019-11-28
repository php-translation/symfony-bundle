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

use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\Extractor\Model\Error;

/**
 * The result from the Importer.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ImportResult
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
    public function getMessageCatalogues(): array
    {
        return $this->messageCatalogues;
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
