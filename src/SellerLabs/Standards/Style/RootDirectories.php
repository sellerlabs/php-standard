<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@sellerlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the PHP Standards package
 */

namespace SellerLabs\Standards\Style;

use SellerLabs\Nucleus\Support\Enum;

/**
 * Class RootDirectories.
 *
 * @author Eduardo Trujillo <ed@sellerlabs.com>
 * @package SellerLabs\Standards\Style
 */
class RootDirectories extends Enum
{
    // General PHP code
    const SOURCE = 'src';
    const TESTS = 'tests';

    // General non-source directories
    const PUB = 'public';
    const RESOURCES = 'resources';

    // External code
    const VENDOR = 'vendor';

    // Laravel projects will also have some additional directories with source
    // code.
    const APP = 'app';
    const BOOTSTRAP = 'bootstrap';
    const CONFIG = 'config';
    const DATABASE = 'database';
    const STORAGE = 'storage';

    /**
     * Get the list of directories that should have PHP coding standard rules
     * enforced.
     *
     * @return array
     */
    public static function getEnforceable()
    {
        return [
            static::SOURCE,
            static::TESTS,

            static::APP,
            static::BOOTSTRAP,
            static::CONFIG,
            static::DATABASE,
        ];
    }

    /**
     * Get a list of discouraged directories, and instructions on how to fix.
     *
     * @return array
     */
    public static function getDiscouraged()
    {
        return [
            // General bad dirs
            'public_html' => 'This is very old-school. Use `public` instead.',

            // Only a few bits of Laravel 5 are discouraged.
            static::APP => 'Develop your application as a library and use the'
                . ' `src` directory instead.',
            static::DATABASE => 'If you have raw SQL migrations, move them to'
                . ' the `resources` directory. If you have code migrations,'
                . ' move them to the `src` directory',

            // Laravel 4 is generally discouraged
            'app/commands' => 'Eww, Laravel 4. Port to Console or Commands'
                . ' namespaces.',
            'app/config' => 'Eww, Laravel 4. Move to `config`.',
            'app/controllers' => 'Eww, Laravel 4. Port to namespaces.',
            'app/database' => 'Eww, Laravel 4. Port to namespaces.',
            'app/lang' => 'Eww, Laravel 4. Move to `resources/lang`.',
            'app/models' => 'Eww, Laravel 4. Port to a models namespace.',
            'app/start' => 'Eww, Laravel 4. Port to service providers',
            'app/storage' => 'Eww, Laravel 4. Move to `storage`.',
            'app/tests' => 'Eww, Laravel 4. Move to `tests`.',
            'app/views' => 'Eww, Laravel 4. Move to `resources/views`.',
        ];
    }
}
