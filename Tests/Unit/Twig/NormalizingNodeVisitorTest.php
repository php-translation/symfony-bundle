<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\Twig;

class NormalizingNodeVisitorTest extends BaseTwigTestCase
{
    public function testBinaryConcatOfConstants()
    {
        $this->assertEquals(
            $this->parse('binary_concat_of_constants_compiled.html.twig'),
            $this->parse('binary_concat_of_constants.html.twig')
        );
    }
}
