<?php

use Generic_Sniffs_NamingConventions_CamelCapsFunctionNameSniff as Base;

/**
 * Class Chroma_Sniffs_NamingConventions_FunctionNameSniff
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 */
class Chroma_Sniffs_NamingConventions_FunctionNameSniff extends Base
{
    /**
     * Processes the tokens outside the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int $stackPtr The position where this token was found.
     */
    protected function processTokenOutsideScope(
        PHP_CodeSniffer_File $phpcsFile,
        $stackPtr
    ) {
        $functionName = $phpcsFile->getDeclarationName($stackPtr);
        if ($functionName === null) {
            // Ignore closures.
            return;
        }

        $errorData = [$functionName];

        // Is this a magic function. i.e., it is prefixed with "__".
        if (preg_match('|^__|', $functionName) !== 0) {
            $magicPart = strtolower(substr($functionName, 2));
            if (isset($this->magicFunctions[$magicPart]) === false) {
                $error = 'Function name "%s" is invalid; only PHP magic methods'
                    . ' should be prefixed with a double underscore';

                $phpcsFile->addError(
                    $error,
                    $stackPtr,
                    'FunctionDoubleUnderscore',
                    $errorData
                );
            }

            return;
        }

        // Ignore first underscore in functions prefixed with "_".
        $functionName = ltrim($functionName, '_');

        if (preg_match('/^[a-z][_a-z]*$/', $functionName) === false) {
            $error = 'Function name "%s" is not in snake case format';
            $phpcsFile->addError($error, $stackPtr, 'NotCamelCaps', $errorData);
            $phpcsFile->recordMetric(
                $stackPtr,
                'snake_case function name',
                'no'
            );
        } else {
            $phpcsFile->recordMetric(
                $stackPtr,
                'snake_case method name',
                'yes'
            );
        }

    }
}
