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
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class InitCommand.
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

        $copyrightYear = date('Y');
        $packageName = 'Unknown';
        $packageOwner = 'Contributors';
        $packageOwnerEmail = '';

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
                'Who is the package owner? '
            ));
        }

        if ($input->hasOption('email')) {
            $packageOwnerEmail = $input->getOption('email');
        } else {
            $packageOwnerEmail = $helper->ask($input, $output, new Question(
                'What is the contact email of the owner? (Optional) ',
                ''
            ));
        }

        $template = file_get_contents(
            __DIR__ . '/../../../../resources/project/formatter.yml'
        );

        $template = str_replace('<year>', $copyrightYear, $template);
        $template = str_replace('<owner>', $packageOwner, $template);
        $template = str_replace('<package>', $packageName, $template);
        $template = str_replace('<email>', $packageOwnerEmail, $template);

        $filesystem = new Filesystem();

        if ($filesystem->exists('.formatter.yml')) {
            if ($helper->ask($input, $output, new ConfirmationQuestion(
                '.formatter.yml already exists. Overwrite? (y/N) ',
                false
            ))) {
                $filesystem->dumpFile('.formatter.yml', $template);
                $output->writeln('Wrote .formatter.yml');
            }
        } else {
            $filesystem->dumpFile('.formatter.yml', $template);
            $output->writeln('Wrote .formatter.yml');
        }

        if (!$filesystem->exists('.editorconfig')) {
            $filesystem->dumpFile(
                '.editorconfig',
                file_get_contents(
                    __DIR__ . '/../../../../resources/project/editorconfig'
                )
            );
            $output->writeln('Wrote .editorconfig');
        }

        return 0;
    }
}
