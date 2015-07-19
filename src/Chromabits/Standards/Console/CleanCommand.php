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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanCommand
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Standards\Console
 */
class CleanCommand extends Command
{
    /**
     * Construct an instance of a CleanCommand.
     */
    public function __construct()
    {
        parent::__construct('style:clean');

        $this->setDescription('Runs all fix and format operations.');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $fixCommand = new FixCommand();
        $formatCommand = new FormatCommand();

        $output->writeln('<info>Running style:fix...</info>');
        $fixCommand->run(new ArrayInput([]), $output);

        $output->writeln('<info>Running style:format...</info>');
        $formatCommand->run(new ArrayInput([]), $output);

        return 0;
    }
}
