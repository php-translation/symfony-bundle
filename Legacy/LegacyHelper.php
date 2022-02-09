<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Legacy;

use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;

/**
 * A legacy helper to suppress deprecations on RequestStack.
 *
 * @author Victor Bocharsky <bocharsky.bw@gmail.com>
 */
class LegacyHelper
{
    public static function getMainRequest(RequestStack $requestStack)
    {
        if (method_exists($requestStack, 'getMainRequest')) {
            return $requestStack->getMainRequest();
        }

        return $requestStack->getMasterRequest();
    }

    /**
     * @param array[string $id, string $parent, ?bool $isPublic] $legacyServices
     */
    public static function registerDeprecatedServices(ServicesConfigurator $servicesConfigurator, array $legacyServices)
    {
        foreach ($legacyServices as $legacyService) {
            $id = $legacyService[0];
            $parent = $legacyService[1];
            $isPublic = $legacyService[2] ?? false;

            // Declare legacy services to remove in next major release
            $service = $servicesConfigurator->set($id)
                ->parent($parent);

            if (Kernel::VERSION_ID < 50100) {
                $service->deprecate('Since php-translation/symfony-bundle 0.10.0: The "%service_id%" service is deprecated. You should stop using it, as it will soon be removed.');
            } else {
                $service->deprecate('php-translation/symfony-bundle', '0.10.0', 'The "%service_id%" service is deprecated. You should stop using it, as it will soon be removed.');
            }

            if ($isPublic) {
                $service->public();
            }
        }
    }
}
