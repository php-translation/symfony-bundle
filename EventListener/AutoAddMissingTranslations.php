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

use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Translation\DataCollectorTranslator;
use Translation\Bundle\Service\StorageService;
use Translation\Common\Model\Message;

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
     */
    public function __construct(StorageService $storage, DataCollectorTranslator $translator = null)
    {
        $this->dataCollector = $translator;
        $this->storage = $storage;
    }

    public function onTerminate(TerminateEvent $event): void
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

// PostResponseEvent have been renamed into ResponseEvent in sf 4.3
// @see https://github.com/symfony/symfony/blob/master/UPGRADE-4.3.md#httpkernel
// To be removed once sf ^4.3 become the minimum supported version.
if (!\class_exists(TerminateEvent::class) && \class_exists(PostResponseEvent::class)) {
    \class_alias(PostResponseEvent::class, TerminateEvent::class);
}
