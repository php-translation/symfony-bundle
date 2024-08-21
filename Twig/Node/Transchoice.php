<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;

class Transchoice extends AbstractExpression
{
    public function __construct(ArrayExpression $arguments, $lineno)
    {
        parent::__construct(['arguments' => $arguments], [], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->raw(
            \sprintf(
                '$this->env->getExtension(\'%s\')->%s(',
                'Translation\Bundle\Twig\TranslationExtension',
                'transchoiceWithDefault'
            )
        );

        $first = true;
        /** @var ArrayExpression $node */
        $node = $this->getNode('arguments');
        foreach ($node->getKeyValuePairs() as $pair) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $first = false;

            $compiler->subcompile($pair['value']);
        }

        $compiler->raw(')');
    }
}
