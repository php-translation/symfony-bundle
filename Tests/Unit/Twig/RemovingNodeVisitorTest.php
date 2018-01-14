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

class RemovingNodeVisitorTest extends BaseTwigTestCase
{
    public function testRemovalWithSimpleTemplate()
    {
        $expected = $this->parse('simple_template_compiled.html.twig');
        $actual = $this->parse('simple_template.html.twig');

        $this->assertEquals($expected, $actual);
    }
}
