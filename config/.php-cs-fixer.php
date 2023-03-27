<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/../')
    ->exclude(
        [
            'var',
            'node_modules',
            'upgrades',
        ]
    )->notPath(
        [
            'public/index.php',
            'public/api.php',
            'config/bootstrap.php',
            'src/Kernel.php',
        ]
    );

return (new PhpCsFixer\Config())
    ->setCacheFile(__DIR__ . '/../var/.php_cs.cache')
    ->setRules(
        [
            '@PSR12' => true,
            'blank_line_after_opening_tag' => true,
            'braces' => ['allow_single_line_closure' => true],
            'compact_nullable_typehint' => true,
            'single_quote' => true,
            'concat_space' => ['spacing' => 'one'],
            'declare_equal_normalize' => ['space' => 'none'],
            'function_typehint_space' => true,
            'new_with_braces' => true,
            'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
            'no_empty_statement' => true,
            'no_leading_import_slash' => true,
            'no_leading_namespace_whitespace' => true,
            'no_whitespace_in_blank_line' => true,
            'return_type_declaration' => ['space_before' => 'none'],
            'single_trait_insert_per_statement' => true,
            'array_syntax' => ['syntax' => 'short'],
            'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
            'declare_strict_types' => true
        ]
    )
    ->setFinder($finder);
