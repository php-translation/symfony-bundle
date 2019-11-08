<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Translation\Bundle\Model\Configuration;

trait BundleTrait
{
    private function configureBundleDirs(InputInterface $input, Configuration $config): void
    {
        if ($bundleName = $input->getOption('bundle')) {
            if (0 === \strpos($bundleName, '@')) {
                if (false === $pos = \strpos($bundleName, '/')) {
                    $bundleName = \substr($bundleName, 1);
                } else {
                    $bundleName = \substr($bundleName, 1, $pos - 2);
                }
            }

            /** @var Bundle $bundle */
            $bundle = $this->getApplication()
                ->getKernel()
                ->getBundle($bundleName)
            ;

            $config->reconfigureBundleDirs($bundle->getPath(), $bundle->getPath().'/Resources/translations');
        }
    }
}
