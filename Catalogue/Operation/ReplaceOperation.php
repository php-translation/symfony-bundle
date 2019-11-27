<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Catalogue\Operation;

use Symfony\Component\Translation\Catalogue\AbstractOperation;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\MetadataAwareInterface;

/**
 * This will merge and replace all values in $target with values from $source.
 * It is the equivalent of running array_merge($target, $source). When in conflict,
 * always take values from $source.
 *
 * This operation is metadata aware. It will do the same recursive merge on metadata.
 *
 * all = source ∪ target = {x: x ∈ source ∨ x ∈ target}
 * new = all ∖ target = {x: x ∈ source ∧ x ∉ target}
 * obsolete = target ∖ all = {x: x ∈ target ∧ x ∉ source}
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ReplaceOperation extends AbstractOperation
{
    protected function processDomain($domain): void
    {
        $this->messages[$domain] = [
            'all' => [],
            'new' => [],
            'obsolete' => [],
        ];
        if (\defined(\sprintf('%s::INTL_DOMAIN_SUFFIX', MessageCatalogueInterface::class))) {
            $intlDomain = $domain.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX;
        } else {
            $intlDomain = $domain;
        }

        foreach ($this->source->all($domain) as $id => $message) {
            $messageDomain = $this->source->defines($id, $intlDomain) ? $intlDomain : $domain;

            if (!$this->target->has($id, $domain)) {
                // No merge required
                $translation = $message;
                $this->messages[$domain]['new'][$id] = $message;
                $resultMeta = $this->getMetadata($this->source, $messageDomain, $id);
            } else {
                // Merge required
                $translation = $message ?? $this->target->get($id, $domain);
                $resultMeta = null;
                $sourceMeta = $this->getMetadata($this->source, $messageDomain, $id);
                $targetMeta = $this->getMetadata($this->target, $this->target->defines($id, $intlDomain) ? $intlDomain : $domain, $id);
                if (\is_array($sourceMeta) && \is_array($targetMeta)) {
                    // We can only merge meta if both is an array
                    $resultMeta = $this->mergeMetadata($sourceMeta, $targetMeta);
                } elseif (!empty($sourceMeta)) {
                    $resultMeta = $sourceMeta;
                } else {
                    // Assert: true === empty($sourceMeta);
                    $resultMeta = $targetMeta;
                }
            }

            $this->messages[$domain]['all'][$id] = $translation;
            $this->result->add([$id => $translation], $messageDomain);

            if (!empty($resultMeta)) {
                $this->result->setMetadata($id, $resultMeta, $messageDomain);
            }
        }

        foreach ($this->target->all($domain) as $id => $message) {
            if ($this->result->has($id, $domain)) {
                // We've already merged this
                // That message was in source
                continue;
            }

            $messageDomain = $this->target->defines($id, $intlDomain) ? $intlDomain : $domain;
            $this->messages[$domain]['all'][$id] = $message;
            $this->messages[$domain]['obsolete'][$id] = $message;
            $this->result->add([$id => $message], $messageDomain);

            $resultMeta = $this->getMetadata($this->target, $messageDomain, $id);
            if (!empty($resultMeta)) {
                $this->result->setMetadata($id, $resultMeta, $messageDomain);
            }
        }
    }

    /**
     * @param MessageCatalogueInterface|MetadataAwareInterface $catalogue
     *
     * @return array|string|mixed|null Can return anything..
     */
    private function getMetadata($catalogue, string $domain, string $key = '')
    {
        if (!$this->target instanceof MetadataAwareInterface) {
            return [];
        }

        return $catalogue->getMetadata($key, $domain);
    }

    private function mergeMetadata(?array $source, ?array $target): array
    {
        if (empty($source) && empty($target)) {
            return [];
        }

        if (empty($source)) {
            return $target;
        }

        if (empty($target)) {
            return $source;
        }

        if (!\is_array($source) || !\is_array($target)) {
            return $source;
        }

        return $this->doMergeMetadata($source, $target);
    }

    private function doMergeMetadata(array $source, array $target): array
    {
        $isTargetArrayAssociative = $this->isArrayAssociative($target);
        foreach ($target as $key => $value) {
            if ($isTargetArrayAssociative) {
                if (isset($source[$key]) && $source[$key] !== $value) {
                    if (\is_array($source[$key]) && \is_array($value)) {
                        // If both arrays, do recursive call
                        $source[$key] = $this->doMergeMetadata($source[$key], $value);
                    }
                    // Else, use value form $source
                } else {
                    // Add new value
                    $source[$key] = $value;
                }
                // if sequential
            } elseif (!\in_array($value, $source, true)) {
                $source[] = $value;
            }
        }

        return $source;
    }

    public function isArrayAssociative(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }

        return \array_keys($arr) !== \range(0, \count($arr) - 1);
    }
}
