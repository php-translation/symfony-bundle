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
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Translation\Bundle\EditInPlace\ActivatorInterface;

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
     * @var UrlGeneratorInterface
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

    /**
     * Determines whether the message for untranslatable content like placeholders will be rendered.
     *
     * @var bool
     */
    private $showUntranslatable;

    public function __construct(ActivatorInterface $activator, UrlGeneratorInterface $router, Packages $packages, string $configName = 'default', bool $showUntranslatable = true)
    {
        $this->activator = $activator;
        $this->router = $router;
        $this->packages = $packages;
        $this->configName = $configName;
        $this->showUntranslatable = $showUntranslatable;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->activator->checkRequest($request)) {
            return;
        }

        $content = $event->getResponse()->getContent();

        if (false === $content) {
            return;
        }

        // Clean the content for malformed tags in attributes or encoded tags
        $replacement = "\"$1🚫 Can't be translated here. 🚫\"";
        $pattern = "@\\s*[\"']\\s*(.[a-zA-Z]+:|)(<x-trans.+data-value=\"([^&\"]+)\".+?(?=<\\/x-trans)<\\/x-trans>)\\s*[\"']@mi";
        if (!$this->showUntranslatable) {
            $replacement = '"$3"';
        }
        $content = \preg_replace($pattern, $replacement, $content);

        // Remove escaped content (e.g. Javascript)
        $pattern = '@&lt;x-trans.+data-key=&quot;([^&]+)&quot;.+data-value=&quot;([^&]+)&quot;.+&lt;\\/x-trans&gt;@mi';
        $replacement = '🚫 $1 🚫';
        if (!$this->showUntranslatable) {
            $replacement = '$2';
        }
        $content = \preg_replace($pattern, $replacement, $content);

        $html = \sprintf(
            self::HTML,
            $this->packages->getUrl('bundles/translation/css/content-tools.min.css'),
            $this->packages->getUrl('bundles/translation/js/content-tools.min.js'),
            $this->packages->getUrl('bundles/translation/js/editInPlace.js'),

            $this->router->generate('translation_edit_in_place_update', [
                'configName' => $this->configName,
                'locale' => $event->getRequest()->getLocale(),
            ])
        );
        $content = \str_replace('</body>', $html."\n".'</body>', $content);

        $response = $event->getResponse();

        // Remove the cache because we do not want the modified page to be cached
        $response->headers->set('cache-control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('pragma', 'no-cache');
        $response->headers->set('expires', '0');

        $event->getResponse()->setContent($content);
    }
}

// FilterResponseEvent have been renamed into ResponseEvent in sf 4.3
// @see https://github.com/symfony/symfony/blob/master/UPGRADE-4.3.md#httpkernel
// To be removed once sf ^4.3 become the minimum supported version.
if (!\class_exists(ResponseEvent::class) && \class_exists(FilterResponseEvent::class)) {
    \class_alias(FilterResponseEvent::class, ResponseEvent::class);
}
