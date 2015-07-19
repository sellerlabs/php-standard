<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the PHP Standards package
 */

namespace Chromabits\Standards\Style;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\FixerInterface;

/**
 * Class StyleConfig
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Standards\Style
 */
class StyleConfig extends Config
{
    /**
     * Construct an instance of a StyleConfig.
     *
     * @param string $name
     * @param string $description
     * @param null $finder
     */
    public function __construct(
        $name = 'chroma',
        $description = 'Chroma default configuration',
        $finder = null
    ) {
        parent::__construct($name, $description);

        $this->finder = coalesce($finder, $this->makeFinder());

        $this->level(FixerInterface::NONE_LEVEL);
        $this->fixers([
            'encoding', 'short_tag', 'braces', 'elseif', 'eof_ending',
            'function_call_space', 'function_declaration', 'indentation',
            'line_after_namespace', 'linefeed', 'lowercase_constants',
            'lowercase_keywords', 'method_argument_space', 'multiple_use',
            'parenthesis', 'php_closing_tag', 'trailing_spaces', 'visibility',
            'duplicate_semicolon', 'extra_empty_lines',
            'multiline_array_trailing_comma', 'new_with_braces',
            'object_operator', 'operators_spaces', 'remove_lines_between_uses',
            'return', 'single_array_no_trailing_comma',
            'spaces_before_semicolon', 'spaces_cast', 'standardize_not_equal',
            'ternary_spaces', 'whitespacy_lines', 'concat_with_spaces',
            'multiline_spaces_before_semicolon', 'short_array_syntax',
        ]);
    }

    /**
     * Make the default finder for Chromabits projects.
     *
     * @return \Symfony\Component\Finder\Finder
     */
    protected function makeFinder()
    {
        $directories = RootDirectories::getEnforceable();
        $found = [];

        $filesystem = new Filesystem();

        foreach ($directories as $directory) {
            if ($filesystem->exists($directory) && is_dir($directory)) {
                $found[] = $directory;
            }
        }

        return DefaultFinder::create()->in($found);
    }
}
