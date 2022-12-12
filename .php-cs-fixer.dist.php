<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PHP54Migration' => true,
    '@PSR2' => true,
    'array_indentation' => true,
    'binary_operator_spaces' => ['operators' => ['=>' => 'align_single_space_minimal']],
    'elseif' => false,
    'no_extra_blank_lines' => true,
    'no_multiline_whitespace_around_double_arrow' => true,
    'no_multiple_statements_per_line' => true,
    'no_trailing_comma_in_singleline' => true,
    'no_whitespace_before_comma_in_array' => ['after_heredoc' => true],
    'normalize_index_brace' => true,
    'single_line_comment_spacing' => true,
    'single_line_comment_style' => true,
    'trailing_comma_in_multiline' => ['after_heredoc' => true],
    'trim_array_spaces' => true,
    'whitespace_after_comma_in_array' => ['ensure_single_space' => true],
])
    ->setFinder($finder)
;
