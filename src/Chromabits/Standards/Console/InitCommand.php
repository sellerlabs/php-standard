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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class InitCommand
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Standards\Console
 */
class InitCommand extends Command
{
    /**
     * Construct an instance of a InitCommand.
     */
    public function __construct()
    {
        parent::__construct('style:init');

        $this->setDescription('Prepares a new project.');
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
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $copyrightYear = date("Y");
        $packageName = 'Unknown';
        $packageOwner = 'Contributors';

        if ($input->hasOption('name')) {
            $packageName = $input->getOption('name');
        } else {
            $packageName = $helper->ask($input, $output, new Question(
                'What is the package name? '
            ));
        }

        if ($input->hasOption('owner')) {
            $packageOwner = $input->getOption('owner');
        } else {
            $packageOwner = $helper->ask($input, $output, new Question(
                'Who is the copyright owner of the package? '
            ));
        }

        $finder = new Finder();
        $fileIterator = $finder->in('.')->name('*formatter.yml')->files()
            ->getIterator();
        $fileIterator->next();
        $file = $fileIterator->current();
        $template = $file->getContents();

        $template = str_replace('<year>', $copyrightYear, $template);
        $template = str_replace('<owner>', $packageOwner, $template);
        $template = str_replace('<package>', $packageName, $template);

        $filesystem = new Filesystem();
        $filesystem->dumpFile('.formatter.yml', $template);

        return 0;
    }
}
