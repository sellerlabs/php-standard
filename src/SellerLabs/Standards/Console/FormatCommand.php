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

use SellerLabs\Standards\Style\RootDirectories;
use Mmoreram\PHPFormatter\Command\HeaderCommand;
use Mmoreram\PHPFormatter\Command\UseSortCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FormatCommand.
 *
 * @author Eduardo Trujillo <ed@sellerlabs.com>
 * @package SellerLabs\Standards\Console
 */
class FormatCommand extends Command
{
    /**
     * Construct an instance of a FormatCommand.
     */
    public function __construct()
    {
        parent::__construct('style:format');

        $this->setDescription(
            'Format the codebase with file headers and statement sorting.'
        );

        $this->addOption('dry-run', null, InputOption::VALUE_NONE);
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $headerCommand = new HeaderCommand();
        $sortCommand = new UseSortCommand();
        $filesystem = new Filesystem();

        foreach (RootDirectories::getEnforceable() as $directory) {
            if ($filesystem->exists($directory) && is_dir($directory)) {
                $formatInput = [
                    'path' => $directory,
                ];

                if ($input->getOption('dry-run')) {
                    $formatInput['--dry-run'] = true;
                }

                $output->writeln('<comment>' . $directory . '</comment>');
                $headerCommand->run(new ArrayInput($formatInput), $output);
                $sortCommand->run(new ArrayInput($formatInput), $output);
            }
        }

        return 0;
    }
}
