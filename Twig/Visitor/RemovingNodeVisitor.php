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
 * Removes translation metadata filters from the AST.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class RemovingNodeVisitor extends \Twig_BaseNodeVisitor
{
    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @param $bool
     */
    public function setEnabled($bool)
    {
        $this->enabled = (bool) $bool;
    }

    /**
     * @param \Twig_Node        $node
     * @param \Twig_Environment $env
     *
     * @return \Twig_Node
     */
    protected function doEnterNode(\Twig_Node $node, \Twig_Environment $env)
    {
        if ($this->enabled && $node instanceof \Twig_Node_Expression_Filter) {
            $name = $node->getNode('filter')->getAttribute('value');

            if ('desc' === $name || 'meaning' === $name) {
                return $this->enterNode($node->getNode('node'), $env);
            }
        }

        return $node;
    }

    /**
     * @param \Twig_Node        $node
     * @param \Twig_Environment $env
     *
     * @return \Twig_Node
     */
    protected function doLeaveNode(\Twig_Node $node, \Twig_Environment $env)
    {
        return $node;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return -1;
    }
}
