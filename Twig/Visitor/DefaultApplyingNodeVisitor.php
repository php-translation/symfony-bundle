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

use Translation\Bundle\Twig\Node\Transchoice;

/**
 * Applies the value of the "desc" filter if the "trans" filter has no
 * translations.
 *
 * This is only active in your development environment.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class DefaultApplyingNodeVisitor extends \Twig_BaseNodeVisitor
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
    public function doEnterNode(\Twig_Node $node, \Twig_Environment $env)
    {
        if (!$this->enabled) {
            return $node;
        }

        if (!($node instanceof \Twig_Node_Expression_Filter && 'desc' === $node->getNode('filter')->getAttribute('value'))) {
            return $node;
        }

        $transNode = $node->getNode('node');
        while ($transNode instanceof \Twig_Node_Expression_Filter
                   && 'trans' !== $transNode->getNode('filter')->getAttribute('value')
                   && 'transchoice' !== $transNode->getNode('filter')->getAttribute('value')) {
            $transNode = $transNode->getNode('node');
        }

        if (!$transNode instanceof \Twig_Node_Expression_Filter) {
            throw new \RuntimeException(sprintf('The "desc" filter must be applied after a "trans", or "transchoice" filter.'));
        }

        $wrappingNode = $node->getNode('node');
        $testNode = clone $wrappingNode;
        $defaultNode = $node->getNode('arguments')->getNode(0);

        // if the |transchoice filter is used, delegate the call to the TranslationExtension
        // so that we can catch a possible exception when the default translation has not yet
        // been extracted
        if ('transchoice' === $transNode->getNode('filter')->getAttribute('value')) {
            $transchoiceArguments = new \Twig_Node_Expression_Array([], $transNode->getTemplateLine());
            $transchoiceArguments->addElement($wrappingNode->getNode('node'));
            $transchoiceArguments->addElement($defaultNode);
            foreach ($wrappingNode->getNode('arguments') as $arg) {
                $transchoiceArguments->addElement($arg);
            }

            $transchoiceNode = new Transchoice($transchoiceArguments, $transNode->getTemplateLine());
            $node->setNode('node', $transchoiceNode);

            return $node;
        }

        // if the |trans filter has replacements parameters
        // (e.g. |trans({'%foo%': 'bar'}))
        if ($wrappingNode->getNode('arguments')->hasNode(0)) {
            $lineno = $wrappingNode->getTemplateLine();

            // remove the replacements from the test node
            $testNode->setNode('arguments', clone $testNode->getNode('arguments'));
            $testNode->getNode('arguments')->setNode(0, new \Twig_Node_Expression_Array([], $lineno));

            // wrap the default node in a |replace filter
            $defaultNode = new \Twig_Node_Expression_Filter(
                clone $node->getNode('arguments')->getNode(0),
                new \Twig_Node_Expression_Constant('replace', $lineno),
                new \Twig_Node([
                    clone $wrappingNode->getNode('arguments')->getNode(0),
                ]),
                $lineno
            );
        }

        $condition = new \Twig_Node_Expression_Conditional(
            new \Twig_Node_Expression_Binary_Equal($testNode, $transNode->getNode('node'), $wrappingNode->getTemplateLine()),
            $defaultNode,
            clone $wrappingNode,
            $wrappingNode->getTemplateLine()
        );
        $node->setNode('node', $condition);

        return $node;
    }

    /**
     * @param \Twig_Node        $node
     * @param \Twig_Environment $env
     *
     * @return \Twig_Node
     */
    public function doLeaveNode(\Twig_Node $node, \Twig_Environment $env)
    {
        return $node;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return -2;
    }
}
