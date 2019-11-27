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
use Twig\Environment;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\Binary\EqualBinary;
use Twig\Node\Expression\ConditionalExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * Applies the value of the "desc" filter if the "trans" filter has no
 * translations.
 *
 * This is only active in your development environment.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class DefaultApplyingNodeVisitor extends AbstractNodeVisitor
{
    /**
     * @var bool
     */
    private $enabled = true;

    public function setEnabled(bool $bool): void
    {
        $this->enabled = $bool;
    }

    public function doEnterNode(Node $node, Environment $env): Node
    {
        if (!$this->enabled) {
            return $node;
        }

        if (!($node instanceof FilterExpression && 'desc' === $node->getNode('filter')->getAttribute('value'))) {
            return $node;
        }

        $transNode = $node->getNode('node');
        while ($transNode instanceof FilterExpression
                   && 'trans' !== $transNode->getNode('filter')->getAttribute('value')
                   && 'transchoice' !== $transNode->getNode('filter')->getAttribute('value')) {
            $transNode = $transNode->getNode('node');
        }

        if (!$transNode instanceof FilterExpression) {
            throw new \RuntimeException(\sprintf('The "desc" filter must be applied after a "trans", or "transchoice" filter.'));
        }

        $wrappingNode = $node->getNode('node');
        $testNode = clone $wrappingNode;
        $defaultNode = $node->getNode('arguments')->getNode(0);

        // if the |transchoice filter is used, delegate the call to the TranslationExtension
        // so that we can catch a possible exception when the default translation has not yet
        // been extracted
        if ('transchoice' === $transNode->getNode('filter')->getAttribute('value')) {
            $transchoiceArguments = new ArrayExpression([], $transNode->getTemplateLine());
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
            $testNode->getNode('arguments')->setNode(0, new ArrayExpression([], $lineno));

            // wrap the default node in a |replace filter
            $defaultNode = new FilterExpression(
                clone $node->getNode('arguments')->getNode(0),
                new ConstantExpression('replace', $lineno),
                new Node([
                    clone $wrappingNode->getNode('arguments')->getNode(0),
                ]),
                $lineno
            );
        }

        $condition = new ConditionalExpression(
            new EqualBinary($testNode, $transNode->getNode('node'), $wrappingNode->getTemplateLine()),
            $defaultNode,
            clone $wrappingNode,
            $wrappingNode->getTemplateLine()
        );
        $node->setNode('node', $condition);

        return $node;
    }

    public function doLeaveNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function getPriority(): int
    {
        return -2;
    }
}
