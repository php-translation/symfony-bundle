<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Twig\Visitor;

use Twig\Environment;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * Removes translation metadata filters from the AST.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class RemovingNodeVisitor extends AbstractNodeVisitor
{
    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @param bool $bool
     */
    public function setEnabled(bool $bool): void
    {
        $this->enabled = $bool;
    }

    /**
     * @param Node        $node
     * @param Environment $env
     *
     * @return Node
     */
    protected function doEnterNode(Node $node, Environment $env): Node
    {
        if ($this->enabled && $node instanceof FilterExpression) {
            $name = $node->getNode('filter')->getAttribute('value');

            if ('desc' === $name || 'meaning' === $name) {
                return $this->enterNode($node->getNode('node'), $env);
            }
        }

        return $node;
    }

    /**
     * @param Node        $node
     * @param Environment $env
     *
     * @return Node
     */
    protected function doLeaveNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return -1;
    }
}
