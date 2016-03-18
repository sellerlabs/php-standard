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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanCommand.
 *
 * @author Eduardo Trujillo <ed@sellerlabs.com>
 * @package SellerLabs\Standards\Console
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

        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Pretend to run and show operation that would be performed.'
        );
        $this->addOption(
            'diff',
            null,
            InputOption::VALUE_NONE,
            'Show diff of changes.'
        );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $fixCommand = new FixCommand();
        $formatCommand = new FormatCommand();

        $fixInput = [];
        $formatInput = [];

        if ($input->getOption('dry-run')) {
            $fixInput['--dry-run'] = true;
            $formatInput['--dry-run'] = true;
        }

        if ($input->getOption('diff')) {
            $fixInput['--diff'] = true;
        }

        $output->writeln('<info>Running style:fix...</info>');
        $fixCommand->run(new ArrayInput($fixInput), $output);

        $output->writeln('<info>Running style:format...</info>');
        $formatCommand->run(new ArrayInput($formatInput), $output);

        return 0;
    }
}
