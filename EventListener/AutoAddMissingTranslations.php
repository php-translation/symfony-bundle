<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\EventListener;

use Translation\Common\Model\Message;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Translation\DataCollectorTranslator;
use Translation\Bundle\Service\StorageService;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class AutoAddMissingTranslations
{
    /**
     * @var DataCollectorTranslator
     */
    private $dataCollector;

    /**
     * @var StorageService
     */
    private $storage;

    /**
     * @param DataCollectorTranslator $translator
     * @param StorageService          $storage
     */
    public function __construct(StorageService $storage, DataCollectorTranslator $translator = null)
    {
        $this->dataCollector = $translator;
        $this->storage = $storage;
    }

    public function onTerminate(Event $event)
    {
        if (null === $this->dataCollector) {
            return;
        }

        $messages = $this->dataCollector->getCollectedMessages();
        foreach ($messages as $message) {
            if (DataCollectorTranslator::MESSAGE_MISSING === $message['state']) {
                $m = new Message($message['id'], $message['domain'], $message['locale'], $message['translation']);
                $this->storage->create($m);
            }
        }
    }
}
