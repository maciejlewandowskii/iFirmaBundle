<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php');

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0'                         => true,
        '@PHP83Migration'                    => true,
        '@Symfony'                           => true,
        '@Symfony:risky'                     => true,

        // Strict type safety
        'declare_strict_types'               => true,
        'strict_param'                       => true,
        'strict_comparison'                  => true,

        // Imports
        'ordered_imports'                    => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                  => true,
        'global_namespace_import'            => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'fully_qualified_strict_types'       => true,

        // Arrays & collections
        'array_syntax'                       => ['syntax' => 'short'],
        'list_syntax'                        => ['syntax' => 'short'],
        'trailing_comma_in_multiline'        => ['elements' => ['arrays', 'arguments', 'parameters', 'match']],
        'no_multiline_whitespace_around_double_arrow' => true,

        // Operators & control flow
        'concat_space'                       => ['spacing' => 'one'],
        'binary_operator_spaces'             => ['default' => 'single_space'],
        'ternary_to_null_coalescing'         => true,
        'modernize_types_casting'            => true,
        'no_useless_else'                    => true,
        'no_useless_return'                  => true,

        // PHPDoc
        'phpdoc_align'                       => ['align' => 'left'],
        'phpdoc_order'                       => true,
        'phpdoc_separation'                  => true,
        'phpdoc_summary'                     => false,
        'phpdoc_trim'                        => true,
        'phpdoc_var_without_name'            => true,
        'no_superfluous_phpdoc_tags'         => ['remove_inheritdoc' => true, 'allow_mixed' => false],

        // Classes
        'final_class'                        => false,
        'self_accessor'                      => true,
        'protected_to_private'               => true,
        'no_null_property_initialization'    => true,
        'class_attributes_separation'        => ['elements' => ['method' => 'one', 'property' => 'one']],

        // Strings
        'single_quote'                       => true,
        'heredoc_to_nowdoc'                  => true,
        'explicit_string_variable'           => true,

        // Whitespace
        'blank_line_before_statement'        => ['statements' => ['return', 'throw', 'try', 'if', 'foreach', 'for', 'while']],
        'method_chaining_indentation'        => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],

        // Misc
        'mb_str_functions'                   => true,
        'random_api_migration'               => true,
        'pow_to_exponentiation'              => true,
        'use_arrow_functions'                => true,
        'no_unreachable_default_argument_value' => true,
        'date_time_immutable'                => true,
    ])
    ->setFinder($finder);
