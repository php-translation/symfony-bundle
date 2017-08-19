<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\Catalogue;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogue;
use Translation\Bundle\Catalogue\CatalogueManager;
use Translation\Bundle\Model\CatalogueMessage;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CatalogueManagerTest extends TestCase
{
    public function testGetMessages()
    {
        $manager = new CatalogueManager();
        $catA = new MessageCatalogue('en', ['a' => 'aTrans', 'b' => 'bTrans']);
        $catB = new MessageCatalogue('fr', ['a' => 'aTransFr', 'c' => 'cTransFr']);
        $manager->load([$catA, $catB]);
        $messages = $manager->getMessages('en', 'message');

        $this->assertCount(2, $messages);
    }

    public function testFinMessagesNoMetadata()
    {
        $manager = new CatalogueManager();
        $catA = new MessageCatalogue('en', ['a' => 'aTrans', 'b' => 'bTrans']);
        $catB = new MessageCatalogue('fr', ['a' => 'aTransFr', 'c' => 'cTransFr']);
        $manager->load([$catA, $catB]);
        $messages = $manager->findMessages(['locale' => 'en']);

        $this->assertCount(2, $messages);
    }

    public function testFinMessages()
    {
        $manager = new CatalogueManager();
        $catA = new MessageCatalogue('en', ['a' => 'aTrans', 'b' => 'bTrans', 'c' => 'cTrans', 'd' => 'dTrans']);
        $catA->setMetadata('a', ['notes' => ['category' => 'state', 'content' => 'new']]);
        $catA->setMetadata('b', ['notes' => ['category' => 'state', 'content' => 'obsolete']]);
        $catA->setMetadata('d', ['notes' => ['category' => 'approved', 'content' => 'true']]);

        $catB = new MessageCatalogue('fr', ['a' => 'aTransFr', 'c' => 'cTransFr', 'e' => 'eTransFr']);
        $catA->setMetadata('c', ['notes' => ['category' => 'approved', 'content' => 'true']]);
        $catA->setMetadata('e', ['notes' => ['category' => 'approved', 'content' => 'true']]);

        $manager->load([$catA, $catB]);

        // Only one approved en message
        $messages = $manager->findMessages(['locale' => 'en', 'isApproved' => true]);
        $this->assertCount(1, $messages);
        $this->assertEquals('d', $messages[0]->getKey());

        $messages = $manager->findMessages(['isApproved' => true]);
        $this->assertCount(3, $messages);
        $keys = array_map(function (CatalogueMessage $message) {
            return $message->getKey();
        }, $messages);
        $this->assertContains('c', $keys);
        $this->assertContains('d', $keys);
        $this->assertContains('e', $keys);

        $messages = $manager->findMessages(['isNew' => true]);
        $this->assertCount(1, $messages);
        $this->assertEquals('a', $messages[0]->getKey());

        $messages = $manager->findMessages(['isObsolete' => true]);
        $this->assertCount(1, $messages);
        $this->assertEquals('b', $messages[0]->getKey());
    }
}
