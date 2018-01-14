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

/**
 * Performs equivalence transformations on the AST to ensure that
 * subsequent visitors do not need to be aware of different syntaxes.
 *
 * E.g. "foo" ~ "bar" ~ "baz" would become "foobarbaz"
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class NormalizingNodeVisitor extends \Twig_BaseNodeVisitor
{
    /**
     * @param \Twig_Node        $node
     * @param \Twig_Environment $env
     *
     * @return \Twig_Node
     */
    protected function doEnterNode(\Twig_Node $node, \Twig_Environment $env)
    {
        return $node;
    }

    /**
     * @param \Twig_Node        $node
     * @param \Twig_Environment $env
     *
     * @return \Twig_Node_Expression_Constant|\Twig_Node
     */
    protected function doLeaveNode(\Twig_Node $node, \Twig_Environment $env)
    {
        if ($node instanceof \Twig_Node_Expression_Binary_Concat
            && ($left = $node->getNode('left')) instanceof \Twig_Node_Expression_Constant
            && ($right = $node->getNode('right')) instanceof \Twig_Node_Expression_Constant) {
            return new \Twig_Node_Expression_Constant($left->getAttribute('value').$right->getAttribute('value'), $left->getTemplateLine());
        }

        return $node;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return -3;
    }
}
