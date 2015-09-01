<?php

class Chroma_Sniffs_files_MatchingCaseSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack
     * passed in $tokens.
     *
     * @return int
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $fileName = basename($phpcsFile->getFilename());
        $tokens = $phpcsFile->getTokens();

        $nextClass = $phpcsFile->findNext([
            T_CLASS,
            T_INTERFACE,
            T_TRAIT
        ], ($stackPtr + 1));

        // If there are no classes, interfaces, or traits, ignore.
        if ($nextClass == null) {
            return ($phpcsFile->numTokens + 1);
        }

        $expected = $tokens[$nextClass + 2]['content'] . '.php';

        if ($expected !== $fileName) {
            $phpcsFile->addError(
                'Filename "%s" doesn\'t match the expected filename "%s"',
                $stackPtr,
                'NotFound',
                [
                    $fileName,
                    $expected
                ]
            );

            $phpcsFile->recordMetric($stackPtr, 'PSR-0 filename', 'no');
        }

        $phpcsFile->recordMetric($stackPtr, 'PSR-0 filename', 'yes');

        // Ignore the rest of the file.
        return ($phpcsFile->numTokens + 1);
    }
}
