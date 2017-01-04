<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\EventListener;

use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\Router;

/**
 * Adds Javascript/CSS files to the Response if the Activator returns true.
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class EditInPlaceResponseListener
{
    const HTML = <<<'HTML'
<!-- TranslationBundle -->
<link rel="stylesheet" type="text/css" href="%s">

<script type="text/javascript" src="%s"></script>
<script type="text/javascript" src="%s"></script>

<script type="text/javascript">
window.onload = function() {
    TranslationBundleEditInPlace("%s");
}
</script>
<!-- /TranslationBundle -->
HTML;

    /**
     * @var ActivatorInterface
     */
    private $activator;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Packages
     */
    private $packages;

    /**
     * @var string
     */
    private $configName;

    public function __construct(ActivatorInterface $activator, Router $router, Packages $packages, $configName = 'default')
    {
        $this->activator = $activator;
        $this->router = $router;
        $this->packages = $packages;
        $this->configName = $configName;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->activator->checkRequest($request)) {
            $content = $event->getResponse()->getContent();

            // Clean the content for malformed tags in attributes or encoded tags
            $content = preg_replace("@=\\s*[\"']\\s*(<x-trans.+<\\/x-trans>)\\s*[\"']@mi", "=\"🚫 Can't be translated here. 🚫\"", $content);
            $content = preg_replace('@&lt;x-trans.+data-key=&quot;([^&]+)&quot;.+&lt;\\/x-trans&gt;@mi', '🚫 $1 🚫', $content);

            $html = sprintf(
                self::HTML,
                $this->packages->getUrl('bundles/translation/css/content-tools.min.css'),
                $this->packages->getUrl('bundles/translation/js/content-tools.min.js'),
                $this->packages->getUrl('bundles/translation/js/editInPlace.js'),

                $this->router->generate('translation_edit_in_place_update', [
                    'configName' => $this->configName,
                    'locale' => $event->getRequest()->getLocale(),
                ])
            );
            $content = str_replace('</body>', $html."\n".'</body>', $content);

            $response = $event->getResponse();

            // Remove the cache because we do not want the modified page to be cached
            $response->headers->set('cache-control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('pragma', 'no-cache');
            $response->headers->set('expires', '0');

            $event->getResponse()->setContent($content);
        }
    }
}
