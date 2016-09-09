<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@sellerlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the PHP Standards package
 */

namespace SellerLabs\Standards\Console;

use SellerLabs\Standards\Style\StyleConfig;
use Symfony\CS\ConfigInterface;
use Symfony\CS\Console\Command\FixCommand as BaseCommand;
use Symfony\CS\Fixer;

/**
 * Class FixCommand.
 *
 * @author Eduardo Trujillo <ed@sellerlabs.com>
 * @package SellerLabs\Standards\Console
 */
class FixCommand extends BaseCommand
{
    /**
     * @param Fixer|null $fixer
     * @param ConfigInterface|null $config
     */
    public function __construct(
        Fixer $fixer = null,
        ConfigInterface $config = null
    ) {
        parent::__construct($fixer, $config ?: new StyleConfig());

        $this->setName('style:fix');
        $this->setDescription('Fixes a directory or a file.');
    }
}
