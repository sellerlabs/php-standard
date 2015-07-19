<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the PHP Standards package
 */

namespace Chromabits\Standards\Console;

use Symfony\Component\Console\Application as BaseApplication;

/**
 * Class Application
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Standards\Console
 */
class Application extends BaseApplication
{
    /**
     * Construct an instance of an Application.
     */
    public function __construct()
    {
        parent::__construct('phpstd', '0.1.2');

        $this->add(new FixCommand());
        $this->add(new FormatCommand());
        $this->add(new InitCommand());
        $this->add(new CleanCommand());
        $this->add(new ValidateCommand());
    }
}
