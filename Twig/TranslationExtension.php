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

use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Bundle\Twig\Visitor\DefaultApplyingNodeVisitor;
use Translation\Bundle\Twig\Visitor\NormalizingNodeVisitor;
use Translation\Bundle\Twig\Visitor\RemovingNodeVisitor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class TranslationExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface|TranslatorBagInterface
     */
    private $translator;

    /**
     * @var bool
     */
    private $debug;

    public function __construct($translator, bool $debug = false)
    {
        // The TranslatorInterface has been deprecated in favor of Symfony\Contracts\Translation\TranslatorInterface in sf4.2.
        // Use this class to type hint event & remove the following condition once sf ^4.2 become the minimum supported version.
        // @see https://github.com/symfony/symfony/blob/master/UPGRADE-4.2.md#translation
        if (!$translator instanceof LegacyTranslatorInterface && !$translator instanceof TranslatorInterface) {
            throw new \InvalidArgumentException('Cannot deal with given translator.');
        }

        $this->translator = $translator;
        $this->debug = $debug;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('desc', [$this, 'desc']),
            new TwigFilter('meaning', [$this, 'meaning']),
        ];
    }

    public function getNodeVisitors(): array
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

    public function transchoiceWithDefault(string $message, string $defaultMessage, int $count, array $arguments = [], ?string $domain = null, ?string $locale = null): string
    {
        if (null === $domain) {
            $domain = 'messages';
        }

        if (false === $this->translator->getCatalogue($locale)->defines($message, $domain)) {
            return $this->translator->transChoice($defaultMessage, $count, \array_merge(['%count%' => $count], $arguments), $domain, $locale);
        }

        return $this->translator->transChoice($message, $count, \array_merge(['%count%' => $count], $arguments), $domain, $locale);
    }

    /**
     * @param mixed $v
     *
     * @return mixed
     */
    public function desc($v)
    {
        return $v;
    }

    /**
     * @param mixed $v
     *
     * @return mixed
     */
    public function meaning($v)
    {
        return $v;
    }

    public function getName(): string
    {
        return 'php-translation';
    }
}
