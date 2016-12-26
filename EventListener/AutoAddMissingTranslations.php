<?php

namespace Translation\Bundle\EventListener;

use Translation\Common\Model\Message;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Translation\DataCollectorTranslator;
use Translation\Bundle\Service\StorageService;

/**
 *
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AutoAddMissingTranslations
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
     *
     * @param DataCollectorTranslator $translator
     * @param StorageService $storage
     */
    public function __construct(StorageService $storage, DataCollectorTranslator $translator = null)
    {
        $this->dataCollector = $translator;
        $this->storage = $storage;

    }

    public function onTerminate(Event $event)
    {
        if ($this->dataCollector === null) {
            return;
        }

        $messages = $this->dataCollector->getCollectedMessages();
        foreach ($messages as $message) {
            if ($message['state'] === DataCollectorTranslator::MESSAGE_MISSING) {
                $m = new Message($message);
                $this->storage->create($m);
            }
        }
    }
}
