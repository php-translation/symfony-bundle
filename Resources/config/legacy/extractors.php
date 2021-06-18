<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Translation\Bundle\Legacy\LegacyHelper;
use Translation\Extractor\FileExtractor\PHPFileExtractor;
use Translation\Extractor\FileExtractor\TwigFileExtractor;
use Translation\Extractor\Visitor\Php\SourceLocationContainerVisitor;
use Translation\Extractor\Visitor\Php\Symfony\ContainerAwareTrans;
use Translation\Extractor\Visitor\Php\Symfony\ContainerAwareTransChoice;
use Translation\Extractor\Visitor\Php\Symfony\FlashMessage;
use Translation\Extractor\Visitor\Php\Symfony\FormTypeChoices;
use Translation\Extractor\Visitor\Php\Symfony\FormTypeEmptyValue;
use Translation\Extractor\Visitor\Php\Symfony\FormTypeHelp;
use Translation\Extractor\Visitor\Php\Symfony\FormTypeInvalidMessage;
use Translation\Extractor\Visitor\Php\Symfony\FormTypeLabelExplicit;
use Translation\Extractor\Visitor\Php\Symfony\FormTypeLabelImplicit;
use Translation\Extractor\Visitor\Php\Symfony\FormTypePlaceholder;
use Translation\Extractor\Visitor\Php\Symfony\ValidationAnnotation;
use Translation\Extractor\Visitor\Twig\TwigVisitor;

return function (ContainerConfigurator $configurator) {
    LegacyHelper::registerDeprecatedServices($configurator->services(), [
        ['php_translation.extractor.php', PHPFileExtractor::class],
        ['php_translation.extractor.twig', TwigFileExtractor::class],
        ['php_translation.extractor.php.visitor.ContainerAwareTrans', ContainerAwareTrans::class],
        ['php_translation.extractor.php.visitor.ContainerAwareTransChoice', ContainerAwareTransChoice::class],
        ['php_translation.extractor.php.visitor.FlashMessage', FlashMessage::class],
        ['php_translation.extractor.php.visitor.FormTypeChoices', FormTypeChoices::class],
        ['php_translation.extractor.php.visitor.FormTypeEmptyValue', FormTypeEmptyValue::class],
        ['php_translation.extractor.php.visitor.FormTypeHelp', FormTypeHelp::class],
        ['php_translation.extractor.php.visitor.FormTypeInvalidMessage', FormTypeInvalidMessage::class],
        ['php_translation.extractor.php.visitor.FormTypeLabelExplicit', FormTypeLabelExplicit::class],
        ['php_translation.extractor.php.visitor.FormTypeLabelImplicit', FormTypeLabelImplicit::class],
        ['php_translation.extractor.php.visitor.FormTypePlaceholder', FormTypePlaceholder::class],
        ['php_translation.extractor.php.visitor.ValidationAnnotation', ValidationAnnotation::class],
        ['php_translation.extractor.php.visitor.SourceLocationContainerVisitor', SourceLocationContainerVisitor::class],
        ['php_translation.extractor.twig.factory.twig', TwigVisitor::class],
    ]);
};
