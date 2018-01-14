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

class Transchoice extends \Twig_Node_Expression
{
    public function __construct(\Twig_Node_Expression_Array $arguments, $lineno)
    {
        parent::__construct(['arguments' => $arguments], [], $lineno);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->raw(
            sprintf(
                '$this->env->getExtension(\'%s\')->%s(',
                'Translation\Bundle\Twig\TranslationExtension',
                'transchoiceWithDefault'
            )
        );

        $first = true;
        foreach ($this->getNode('arguments')->getKeyValuePairs() as $pair) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $first = false;

            $compiler->subcompile($pair['value']);
        }

        $compiler->raw(')');
    }
}
