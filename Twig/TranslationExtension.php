<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;
use Translation\Bundle\Twig\Visitor\DefaultApplyingNodeVisitor;
use Translation\Bundle\Twig\Visitor\NormalizingNodeVisitor;
use Translation\Bundle\Twig\Visitor\RemovingNodeVisitor;

/**
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class TranslationExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param TranslatorInterface $translator
     * @param bool                $debug
     */
    public function __construct(TranslatorInterface $translator, $debug = false)
    {
        $this->translator = $translator;
        $this->debug = $debug;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('desc', [$this, 'desc']),
            new \Twig_SimpleFilter('meaning', [$this, 'meaning']),
        ];
    }

    /**
     * @return array
     */
    public function getNodeVisitors()
    {
        $visitors = [
            new NormalizingNodeVisitor(),
            new RemovingNodeVisitor(),
        ];

        if ($this->debug) {
            $visitors[] = new DefaultApplyingNodeVisitor();
        }

        return $visitors;
    }

    /**
     * @param string      $message
     * @param string      $defaultMessage
     * @param int         $count
     * @param array       $arguments
     * @param null|string $domain
     * @param null|string $locale
     *
     * @return string
     */
    public function transchoiceWithDefault($message, $defaultMessage, $count, array $arguments = [], $domain = null, $locale = null)
    {
        if (null === $domain) {
            $domain = 'messages';
        }

        if (false === $this->translator->getCatalogue($locale)->defines($message, $domain)) {
            return $this->translator->transChoice($defaultMessage, $count, array_merge(['%count%' => $count], $arguments), $domain, $locale);
        }

        return $this->translator->transChoice($message, $count, array_merge(['%count%' => $count], $arguments), $domain, $locale);
    }

    /**
     * @param $v
     *
     * @return mixed
     */
    public function desc($v)
    {
        return $v;
    }

    /**
     * @param $v
     *
     * @return mixed
     */
    public function meaning($v)
    {
        return $v;
    }

    public function getName()
    {
        return 'php-translation';
    }
}
