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
 * Class Application.
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
        parent::__construct('phpstd', '0.1.7');

        $fix = new FixCommand();
        $init = new InitCommand();
        $clean = new CleanCommand();
        $validate = new ValidateCommand();
        $format = new FormatCommand();
        $lint = new LintCommand();

        $init->setAliases(['init']);
        $clean->setAliases(['clean']);
        $validate->setAliases(['validate']);
        $lint->setAliases(['lint']);

        $this->add($fix);
        $this->add($format);
        $this->add($init);
        $this->add($clean);
        $this->add($validate);
        $this->add($lint);
    }
}
