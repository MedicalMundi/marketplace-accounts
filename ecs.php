<?php declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;


return function (ECSConfig $ecsConfig): void {
    $ecsConfig->cacheDirectory(__DIR__ . '/var/cache_tools/ecs');
    $ecsConfig->paths([
        //__DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/_iam/src',
        __DIR__ . '/_iam/tests',
        __DIR__ . '/_notifier/src',
        __DIR__ . '/_notifier/tests',
        __DIR__ . '/rector.php',
    ]);

    $ecsConfig->skip([
        __DIR__ . '/src/Kernel.php',
        __DIR__ . '/tests/bootstrap.php',
        BlankLineAfterOpeningTagFixer::class,
    ]);

    // this way you add a single rule
    $ecsConfig->rules([
        BlankLineAfterNamespaceFixer::class,
        DeclareStrictTypesFixer::class,
        FullyQualifiedStrictTypesFixer::class,
        NativeFunctionInvocationFixer::class,
        NoUnusedImportsFixer::class,
        OrderedImportsFixer::class,
        StrictComparisonFixer::class,
    ]);

    $docHeader = <<<'EOF'
This file is part of the medicalmundi/marketplace-accounts

@copyright (c) 2024 MedicalMundi

This software consists of voluntary contributions made by many individuals
{@link https://github.com/medicalmundi/marketplace-accounts/graphs/contributors developer} and is licensed under the MIT license.

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
@license https://github.com/MedicalMundi/marketplace-accounts/blob/main/LICENSE MIT
EOF;

    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
        'comment_type' => 'comment',
        'header' => \trim($docHeader),
        'location' => 'after_declare_strict',
        'separate' => 'both',
    ]);

    // this way you can add sets - group of rules
    $ecsConfig->sets([
        // run and fix, one by one
        SetList::SPACES,
        SetList::ARRAY,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
        SetList::COMMENTS,
        SetList::PSR_12,
    ]);
};
