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
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

abstract class AbstractNodeVisitor implements NodeVisitorInterface
{

    abstract protected function doEnterNode(Node $node, Environment $env): Node;

    abstract protected function doLeaveNode(Node $node, Environment $env): Node;

    public function enterNode(Node $node, Environment $env): Node
    {
        return $this->doEnterNode($node, $env);
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        return $this->doLeaveNode($node, $env);
    }

    protected function getValueFromNode(Node $node): ?string
    {
        if (Environment::VERSION_ID >= 31200) {
            return $node->getAttribute('twig_callable')->getName();
        } else {
            return $node->getNode('filter')->getAttribute('value');
        }
    }
}
