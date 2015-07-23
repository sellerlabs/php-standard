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
use PHP_CodeSniffer_CLI as CLI;
use PHP_CodeSniffer_File  as File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class LintCommand
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Standards\Console
 */
class LintCommand extends Command
{
    public function __construct()
    {
        parent::__construct('style:lint');

        $this->setDescription('Checks the code for issues and common bugs.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->getFormatter()->setStyle(
            'file',
            new OutputFormatterStyle('white', 'default', ['bold'])
        );
        $output->getFormatter()->setStyle(
            'source',
            new OutputFormatterStyle('blue', 'default', [])
        );
        $output->getFormatter()->setStyle(
            'success',
            new OutputFormatterStyle('white', 'green', [])
        );

        $finder = new Finder();
        $phpcs = new CodeSniffer(0);

        $phpcs->allowedFileExtensions = ['php'];

        $phpcsCli = new CLI();
        $phpcsCli->errorSeverity = PHPCS_DEFAULT_ERROR_SEV;
        $phpcsCli->warningSeverity = PHPCS_DEFAULT_WARN_SEV;
        $phpcsCli->dieOnUnknownArg = false;
        $phpcsCli->setCommandLineValues(['--colors', '-p', '--report=full']);
        $phpcs->setCli($phpcsCli);

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

    protected function renderMessages(array $messages, OutputInterface $output)
    {
        foreach ($messages as $line => $lineErrors) {
            foreach ($lineErrors as $column => $columnErrors) {
                foreach ($columnErrors as $error) {
                    $output->writeln([
                        ">>  $line:$column  <source>" . $error['source'] .
                        '</source>',
                        '    ' . $error['message']
                    ]);
                }
            }
        }
    }
}
