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

use Chromabits\Standards\Style\RootDirectories;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ValidateCommand.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Standards\Console
 */
class ValidateCommand extends Command
{
    /**
     * Construct an instance of a ValidateCommand.
     */
    public function __construct()
    {
        parent::__construct('style:validate');

        $this->setDescription('Validate the organization of the project.');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();
        $discouraged = RootDirectories::getDiscouraged();
        $found = [];

        foreach ($discouraged as $directory => $comment) {
            if ($filesystem->exists($directory) && is_dir($directory)) {
                $found[] = [$directory, $comment];
            }
        }

        if (count($found) == 0) {
            $output->writeln('<fg=green>No issues found.</fg=green>');

            return 0;
        }

        $output->writeln('The following issues where found:');

        $table = new Table($output);

        $table->setHeaders(['Directory', 'Comment']);
        $table->setRows($found);
        $table->render();

        return 1;
    }
}
