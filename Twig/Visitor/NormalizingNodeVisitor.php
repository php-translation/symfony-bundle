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
use Twig\Node\Expression\Binary\ConcatBinary;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * Performs equivalence transformations on the AST to ensure that
 * subsequent visitors do not need to be aware of different syntaxes.
 *
 * E.g. "foo" ~ "bar" ~ "baz" would become "foobarbaz"
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class NormalizingNodeVisitor extends AbstractNodeVisitor
{
    protected function doEnterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    /**
     * @return ConstantExpression|Node
     */
    protected function doLeaveNode(Node $node, Environment $env): Node
    {
        if ($node instanceof ConcatBinary
            && ($left = $node->getNode('left')) instanceof ConstantExpression
            && ($right = $node->getNode('right')) instanceof ConstantExpression) {
            return new ConstantExpression($left->getAttribute('value').$right->getAttribute('value'), $left->getTemplateLine());
        }

        return $node;
    }

    public function getPriority(): int
    {
        return -3;
    }
}
