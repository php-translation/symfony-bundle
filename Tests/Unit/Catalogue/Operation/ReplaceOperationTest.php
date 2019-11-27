<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\Catalogue\Operation;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\Bundle\Catalogue\Operation\ReplaceOperation;

class ReplaceOperationTest extends TestCase
{
    public function testGetMessagesFromSingleDomain(): void
    {
        $operation = $this->createOperation(
            new MessageCatalogue('en', ['messages' => ['a' => 'new_a', 'c' => 'new_c']]),
            new MessageCatalogue('en', ['messages' => ['a' => 'old_a', 'b' => 'old_b']])
        );

        $this->assertEquals(
            ['a' => 'new_a', 'b' => 'old_b', 'c' => 'new_c'],
            $operation->getMessages('messages')
        );

        $this->assertEquals(
            ['c' => 'new_c'],
            $operation->getNewMessages('messages')
        );

        $this->assertEquals(
            ['b' => 'old_b'],
            $operation->getObsoleteMessages('messages')
        );
    }

    public function testGetResultFromSingleDomain(): void
    {
        $this->assertEquals(
            new MessageCatalogue('en', [
                'messages' => ['a' => 'new_a', 'b' => 'old_b', 'c' => 'new_c'],
            ]),
            $this->createOperation(
                new MessageCatalogue('en', ['messages' => ['a' => 'new_a', 'c' => 'new_c']]),
                new MessageCatalogue('en', ['messages' => ['a' => 'old_a', 'b' => 'old_b']])
            )->getResult()
        );
    }

    public function testGetResultWithNullValues(): void
    {
        $this->assertEquals(
            new MessageCatalogue('en', [
                'messages' => ['a' => 'old_a', 'b' => 'old_b', 'c' => null],
            ]),
            $this->createOperation(
                new MessageCatalogue('en', ['messages' => ['a' => null, 'c' => null]]),
                new MessageCatalogue('en', ['messages' => ['a' => 'old_a', 'b' => 'old_b']])
            )->getResult()
        );
    }

    public function testGetResultWithMetadata(): void
    {
        $leftCatalogue = new MessageCatalogue('en', ['messages' => ['a' => 'new_a', 'b' => 'new_b']]);
        $leftCatalogue->setMetadata('a', 'foo', 'messages');
        $leftCatalogue->setMetadata('b', 'bar', 'messages');
        $rightCatalogue = new MessageCatalogue('en', ['messages' => ['b' => 'old_b', 'c' => 'old_c']]);
        $rightCatalogue->setMetadata('b', 'baz', 'messages');
        $rightCatalogue->setMetadata('c', 'qux', 'messages');

        $mergedCatalogue = new MessageCatalogue('en', ['messages' => ['a' => 'new_a', 'b' => 'new_b', 'c' => 'old_c']]);
        $mergedCatalogue->setMetadata('a', 'foo', 'messages');
        $mergedCatalogue->setMetadata('b', 'bar', 'messages');
        $mergedCatalogue->setMetadata('c', 'qux', 'messages');

        $this->assertEquals(
            $mergedCatalogue,
            $this->createOperation($leftCatalogue, $rightCatalogue)->getResult()
        );
    }

    public function testGetResultWithArrayMetadata(): void
    {
        $leftCatalogue = new MessageCatalogue('en', ['messages' => ['a' => 'new_a', 'b' => 'new_b']]);
        $notes = [
            ['category' => 'note1', 'content' => 'a'],
            ['category' => 'note2', 'content' => 'b'],
        ];
        $leftCatalogue->setMetadata('a', ['notes' => ['test']], 'messages');
        $leftCatalogue->setMetadata('b', ['notes' => $notes, 'meta0' => 'zz', 'meta1' => 'yy'], 'messages');

        $rightCatalogue = new MessageCatalogue('en', ['messages' => ['b' => 'old_b', 'c' => 'old_c']]);
        $notes = [
            ['category' => 'note2', 'content' => 'b'],
            ['category' => 'note2', 'content' => 'c'],
        ];
        $rightCatalogue->setMetadata('b', ['notes' => $notes, 'meta0' => 'aa', 'meta2' => 'xx'], 'messages');
        $rightCatalogue->setMetadata('c', 'qux', 'messages');

        $mergedCatalogue = new MessageCatalogue('en', ['messages' => ['a' => 'new_a', 'b' => 'new_b', 'c' => 'old_c']]);
        $mergedNotes = [
            ['category' => 'note1', 'content' => 'a'],
            ['category' => 'note2', 'content' => 'b'],
            ['category' => 'note2', 'content' => 'c'],
        ];
        $mergedCatalogue->setMetadata('a', ['notes' => ['test']], 'messages');
        $mergedCatalogue->setMetadata('b', ['notes' => $mergedNotes, 'meta0' => 'zz',  'meta1' => 'yy', 'meta2' => 'xx'], 'messages');
        $mergedCatalogue->setMetadata('c', 'qux', 'messages');

        $resultCatalogue = $this->createOperation($leftCatalogue, $rightCatalogue)->getResult();

        $this->assertEquals(['notes' => ['test']], $resultCatalogue->getMetadata('a'));
        $this->assertEquals('qux', $resultCatalogue->getMetadata('c'));

        $bMeta = $resultCatalogue->getMetadata('b');
        $this->assertCount(4, $bMeta);
        $this->assertEquals('zz', $bMeta['meta0']);
        $this->assertEquals('yy', $bMeta['meta1']);
        $this->assertEquals('xx', $bMeta['meta2']);
        $this->assertCount(3, $bMeta['notes']);
        foreach ($mergedNotes as $note) {
            $this->assertContains($note, $bMeta['notes']);
        }
    }

    protected function createOperation(MessageCatalogueInterface $source, MessageCatalogueInterface $target): ReplaceOperation
    {
        return new ReplaceOperation($source, $target);
    }
}
