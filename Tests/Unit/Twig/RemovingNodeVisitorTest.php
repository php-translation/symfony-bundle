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

use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class RemovingNodeVisitorTest extends BaseTwigTestCase
{
    public function testRemovalWithSimpleTemplate(): void
    {
        // transchoice tag have been definively removed in sf ^5.0
        // Remove this condition & *with_transchoice templates once sf ^5.0 is the minimum supported version.
        if (\version_compare(Kernel::VERSION, 5.0, '<')) {
            $expected = $this->parse('simple_template_compiled_with_transchoice.html.twig');
            $actual = $this->parse('simple_template_with_transchoice.html.twig');
        } else {
            $expected = $this->parse('simple_template_compiled.html.twig');
            $actual = $this->parse('simple_template.html.twig');
        }

        $this->assertEquals($expected, $actual);
    }
}
