<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\EditInPlace;

use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\Router;

/**
 * Adds Javascript/CSS files to the response if the request matches the requirements
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class ResponseListener
{
    const HTML = <<<HTML
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
     * @var Activator
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

    public function __construct(Activator $activator, Router $router, Packages $packages, $configName = 'default')
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

            // Clean the response: no tags in attributes, no encoded tags
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

            // @todo remove cache header / force NON CACHE response
            $event->getResponse()->setContent($content);
        }
    }
}
