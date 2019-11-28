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

/**
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class DefaultApplyingNodeVisitorTest extends BaseTwigTestCase
{
    public function testApply(): void
    {
        $this->assertEquals(
            $this->parse('apply_default_value_compiled.html.twig', true),
            $this->parse('apply_default_value.html.twig', true)
        );
    }
}
