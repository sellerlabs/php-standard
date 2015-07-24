<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the PHP Standards package
 */

use PHP_CodeSniffer_Sniff as BaseSniff;

/**
 * Class Chroma_Sniffs_Functions_OpeningFunctionBraceSniff.
 *
 * Similar to OpeningFunctionBraceBsdAllmanSniff but it ignores cases where
 * the function is multi-line. In these cases, the closing parenthesis is likely
 * to be on the same line as the opening bracket
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Standards\Chroma\Sniffs\Functions
 */
class Chroma_Sniffs_Functions_OpeningFunctionBraceSniff implements BaseSniff
{
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_FUNCTION,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $file The file being scanned.
     * @param int $stackPtr The position of the current token in the
     * stack passed in $tokens.
     *
     */
    public function process(PHP_CodeSniffer_File $file, $stackPtr)
    {
        $tokens = $file->getTokens();

        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            return;
        }

        // The end of the function occurs at the end of the argument list. Its
        // like this because some people like to break long function
        // declarations over multiple lines.
        $openingBrace = $tokens[$stackPtr]['scope_opener'];
        $parenthesisOpener = $tokens[$stackPtr]['parenthesis_opener'];
        $parenthesisCloser = $tokens[$stackPtr]['parenthesis_closer'];

        $functionStartLine = $tokens[$parenthesisOpener]['line'];
        $functionLine = $tokens[$parenthesisCloser]['line'];
        $braceLine = $tokens[$openingBrace]['line'];

        $lineDifference = ($braceLine - $functionLine);
        $isMultiline = ($functionStartLine != $functionLine);

        if ($lineDifference === 0 && !$isMultiline) {
            $error = 'Opening brace should be on a new line';
            $fix = $file->addFixableError(
                $error,
                $openingBrace,
                'BraceOnSameLine'
            );

            if ($fix === true) {
                $file->fixer->beginChangeset();
                $indent = $file->findFirstOnLine([], $openingBrace);

                if ($tokens[$indent]['code'] === T_WHITESPACE) {
                    $file->fixer->addContentBefore(
                        $openingBrace,
                        $tokens[$indent]['content']
                    );
                }

                $file->fixer->addNewlineBefore($openingBrace);
                $file->fixer->endChangeset();
            }

            $file->recordMetric(
                $stackPtr,
                'Function opening brace placement',
                'same line'
            );
        } else {
            if ($lineDifference > 1) {
                $error = 'Opening brace should be on the line after the'
                    . ' declaration; found %s blank line(s)';
                $data = [($lineDifference - 1)];
                $fix = $file->addFixableError(
                    $error,
                    $openingBrace,
                    'BraceSpacing',
                    $data
                );

                if ($fix === true) {
                    $afterCloser = $parenthesisCloser + 1;
                    for ($i = $afterCloser; $i < $openingBrace; $i++) {
                        if ($tokens[$i]['line'] === $braceLine) {
                            $file->fixer->addNewLineBefore($i);
                            break;
                        }

                        $file->fixer->replaceToken($i, '');
                    }
                }
            }
        }

        $next = $file->findNext(
            T_WHITESPACE,
            ($openingBrace + 1),
            null,
            true
        );

        if ($tokens[$next]['line'] === $tokens[$openingBrace]['line']) {
            if ($next === $tokens[$stackPtr]['scope_closer']) {
                // Ignore empty functions.
                return;
            }

            $error = 'Opening brace must be the last content on the line';
            $fix = $file->addFixableError(
                $error,
                $openingBrace,
                'ContentAfterBrace'
            );

            if ($fix === true) {
                $file->fixer->addNewline($openingBrace);
            }
        }

        // Only continue checking if the opening brace looks good.
        if ($lineDifference !== 1) {
            return;
        }

        // We need to actually find the first piece of content on this line,
        // as if this is a method with tokens before it (public, static etc)
        // or an if with an else before it, then we need to start the scope
        // checking from there, rather than the current token.
        $lineStart = $stackPtr;
        while (
            ($lineStart = $file->findPrevious(
                T_WHITESPACE,
                ($lineStart - 1),
                null,
                false)
            ) !== false) {
            $position = strpos(
                $tokens[$lineStart]['content'],
                $file->eolChar
            );
            if ($position !== false) {
                break;
            }
        }

        // We found a new line, now go forward and find the first
        // non-whitespace token.
        $lineStart = $file->findNext(T_WHITESPACE, $lineStart, null, true);

        // The opening brace is on the correct line, now it needs to be
        // checked to be correctly indented.
        $startColumn = $tokens[$lineStart]['column'];
        $braceIndent = $tokens[$openingBrace]['column'];

        if ($braceIndent !== $startColumn) {
            $expected = ($startColumn - 1);
            $found = ($braceIndent - 1);

            $error = 'Opening brace indented incorrectly;'
                . ' expected %s spaces, found %s';
            $data = [
                $expected,
                $found,
            ];

            $fix = $file->addFixableError(
                $error,
                $openingBrace,
                'BraceIndent',
                $data
            );

            if ($fix === true) {
                $indent = str_repeat(' ', $expected);

                if ($found === 0) {
                    $file->fixer->addContentBefore($openingBrace, $indent);
                } else {
                    $file->fixer->replaceToken(
                        ($openingBrace - 1),
                        $indent
                    );
                }
            }
        }

        $file->recordMetric(
            $stackPtr,
            'Function opening brace placement',
            'new line'
        );
    }
}
