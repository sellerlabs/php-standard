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

use Chromabits\Standards\Chroma\Anchor;
use Chromabits\Standards\Style\RootDirectories;
use PHP_CodeSniffer as CodeSniffer;
use PHP_CodeSniffer_File  as File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class LintCommand.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Standards\Console
 */
class LintCommand extends Command
{
    /**
     * Construct an instance of LintCommand.
     */
    public function __construct()
    {
        parent::__construct('style:lint');

        $this->setDescription('Checks the code for issues and common bugs.');
    }

    /**
     * Setup the formatter.
     *
     * @param OutputFormatterInterface $formatter
     */
    protected function setupFormatters(OutputFormatterInterface $formatter)
    {
        $formatter->setStyle(
            'file',
            new OutputFormatterStyle('white', 'default', ['bold'])
        );
        $formatter->setStyle(
            'source',
            new OutputFormatterStyle('blue', 'default', [])
        );
        $formatter->setStyle(
            'success',
            new OutputFormatterStyle('white', 'green', [])
        );
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
        $this->setupFormatters($output->getFormatter());

        $finder = new Finder();
        $phpcs = new CodeSniffer(0);

        $existing = [];
        foreach (RootDirectories::getEnforceable() as $directory) {
            if (file_exists($directory) && is_dir($directory)) {
                $existing[] = $directory;
            }
        }

        $files = $finder->files()->in($existing)
            ->notName('*Sniff.php')
            ->ignoreUnreadableDirs()
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->name('*.php');

        $phpcs->reporting->startTiming();

        $phpcs->initStandard(Anchor::getDirectory());

        $files = array_keys(iterator_to_array($files->getIterator()));
        $processed = [];
        $withErrors = [];
        $withWarnings = [];

        foreach ($files as $file) {
            $done = $phpcs->processFile($file);

            if ($done->getErrorCount() > 0) {
                $output->write('E');
                $withErrors[] = $done;

                if ($done->getWarningCount() > 0) {
                    $withWarnings[] = $done;
                }
            } elseif ($done->getWarningCount() > 0) {
                $output->write('W');
                $withWarnings[] = $done;
            } else {
                $output->write('.');
            }

            $processed[] = $done;
        }

        $this->renderSummary($withErrors, $withWarnings, $output);
    }

    /**
     * Render the error and warning summary.
     *
     * @param array $withErrors
     * @param array $withWarnings
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function renderSummary(
        array $withErrors,
        array $withWarnings,
        OutputInterface $output
    ) {
        $output->writeln("\n");

        if (count($withErrors)) {
            $output->writeln('<error>Errors:</error>');

            $this->renderErrors($withErrors, $output);
        }

        if (count($withWarnings)) {
            $output->writeln('<comment>Warning:</comment>');

            $this->renderWarnings($withWarnings, $output);
        }

        if (count($withErrors) === 0 && count($withWarnings) === 0) {
            $output->writeln('<success>GREAT JOB! IT\'S BEAUTIFUL</success>');

            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Render errors into the console.
     *
     * @param File[] $files
     * @param OutputInterface $output
     */
    protected function renderErrors(array $files, OutputInterface $output)
    {
        foreach ($files as $file) {
            $output->writeln('<file>' . $file->getFilename() . '</file>');

            $this->renderMessages($file->getErrors(), $output);

            $output->writeln('');
        }
    }

    /**
     * Render warnings into the console.
     *
     * @param File[] $files
     * @param OutputInterface $output
     */
    protected function renderWarnings(array $files, OutputInterface $output)
    {
        foreach ($files as $file) {
            $output->writeln('<file>' . $file->getFilename() . '</file>');

            $this->renderMessages($file->getWarnings(), $output);

            $output->writeln('');
        }
    }

    /**
     * Render individual error and warning messages.
     *
     * @param array $messages
     * @param OutputInterface $output
     */
    protected function renderMessages(array $messages, OutputInterface $output)
    {
        foreach ($messages as $line => $lineErrors) {
            foreach ($lineErrors as $column => $columnErrors) {
                foreach ($columnErrors as $error) {
                    $output->writeln([
                        ">>  $line:$column<source>" . $error['source'] .
                        '</source>',
                        '    ' . $error['message'],
                    ]);
                }
            }
        }
    }
}
