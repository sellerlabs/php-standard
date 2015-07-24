<?php

use PEAR_Sniffs_Commenting_FunctionCommentSniff as BaseSniff;
use PHP_CodeSniffer_File as File;

/**
 * Class FunctionCommentSniff
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Standards\Chroma\Sniffs\Commenting
 */
class Chroma_Sniffs_Commenting_FunctionCommentSniff extends BaseSniff
{
    protected static $useCache = [];

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $file The file being scanned.
     * @param int $stackPtr The position of the current token in the stack
     * passed in $tokens.
     */
    public function process(File $file, $stackPtr)
    {
        $tokens = $file->getTokens();
        $find = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $commentEnd = $file->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            // Inline comments might just be closing comments for
            // control structures or functions instead of function comments
            // using the wrong comment type. If there is other code on the line,
            // assume they relate to that code.
            $prev = $file->findPrevious($find, ($commentEnd - 1), null, true);
            if ($prev !== false
                && $tokens[$prev]['line'] === $tokens[$commentEnd]['line']
            ) {
                $commentEnd = $prev;
            }
        }

        // Should we ignore the missing comment? Only for tests.
        $functionNamePos = $file->findNext(T_STRING, $stackPtr);
        if (substr($tokens[$functionNamePos]['content'], 0, 4) === 'test') {
            return;
        }

        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        ) {
            $file->addError(
                'Missing function doc comment',
                $stackPtr,
                'Missing'
            );
            $file->recordMetric(
                $stackPtr,
                'Function has doc comment',
                'no'
            );

            return;
        } else {
            $file->recordMetric(
                $stackPtr,
                'Function has doc comment',
                'yes'
            );
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $file->addError(
                'You must use "/**" style comments for a function comment',
                $stackPtr,
                'WrongStyle'
            );

            return;
        }

        if ($tokens[$commentEnd]['line'] !== ($tokens[$stackPtr]['line'] - 1)) {
            $error = 'There must be no blank lines after the function comment';
            $file->addError($error, $commentEnd, 'SpacingAfter');
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@see') {
                // Make sure the tag isn't empty.
                $string = $file->findNext(
                    T_DOC_COMMENT_STRING,
                    $tag,
                    $commentEnd
                );
                if ($string === false
                    || $tokens[$string]['line'] !== $tokens[$tag]['line']
                ) {
                    $error = 'Content missing for @see tag in function comment';
                    $file->addError($error, $tag, 'EmptySees');
                }
            }
        }

        $this->processReturn($file, $stackPtr, $commentStart);
        $this->processThrows($file, $stackPtr, $commentStart);
        $this->processParams($file, $stackPtr, $commentStart);
    }

    /**
     * Process a @param comment .
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param int $commentStart
     */
    protected function processParams(
        File $phpcsFile,
        $stackPtr,
        $commentStart
    ) {
        $tokens = $phpcsFile->getTokens();

        $params = [];
        $maxType = 0;
        $maxVar = 0;

        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag) {
            if ($tokens[$tag]['content'] !== '@param') {
                continue;
            }

            $type = '';
            $typeSpace = 0;
            $var = '';
            $varSpace = 0;
            $comment = '';
            if ($tokens[($tag + 2)]['code'] === T_DOC_COMMENT_STRING) {
                $matches = [];
                preg_match(
                    '/([^$&]+)(?:((?:\$|&)[^\s]+)(?:(\s+)(.*))?)?/',
                    $tokens[($tag + 2)]['content'],
                    $matches
                );

                $typeLen = strlen($matches[1]);
                $type = trim($matches[1]);
                $typeSpace = ($typeLen - strlen($type));
                $typeLen = strlen($type);
                if ($typeLen > $maxType) {
                    $maxType = $typeLen;
                }

                if (isset($matches[2]) === true) {
                    $var = $matches[2];
                    $varLen = strlen($var);
                    if ($varLen > $maxVar) {
                        $maxVar = $varLen;
                    }

                    if (isset($matches[4]) === true) {
                        $varSpace = strlen($matches[3]);
                        $comment = $matches[4];

                        // Any strings until the next tag belong to this comment.
                        if (isset($tokens[$commentStart]['comment_tags'][($pos
                                    + 1)]) === true
                        ) {
                            $end = $tokens[$commentStart]['comment_tags'][($pos
                                + 1)];
                        } else {
                            $end = $tokens[$commentStart]['comment_closer'];
                        }

                        for ($i = ($tag + 3); $i < $end; $i++) {
                            if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                                $comment .= ' ' . $tokens[$i]['content'];
                            }
                        }
                    }

                    //$error = 'Missing parameter comment';
                    //$phpcsFile->addError($error, $tag, 'MissingParamComment');
                } else {
                    $error = 'Missing parameter name';
                    $phpcsFile->addError($error, $tag, 'MissingParamName');
                }
            } else {
                $error = 'Missing parameter type';
                $phpcsFile->addError($error, $tag, 'MissingParamType');
            }

            $params[] = [
                'tag' => $tag,
                'type' => $type,
                'var' => $var,
                'comment' => $comment,
                'type_space' => $typeSpace,
                'var_space' => $varSpace,
            ];
        }

        $realParams = $phpcsFile->getMethodParameters($stackPtr);
        $foundParams = [];

        foreach ($params as $pos => $param) {
            if ($param['var'] === '') {
                continue;
            }

            if (array_key_exists('type', $param)) {
                $types = explode('|', $param['type']);

                foreach ($types as $type) {
                    $this->checkParamType($type, $param['tag'], $phpcsFile);
                }
            }

            $foundParams[] = $param['var'];

            // Check number of spaces after the type.
            $spaces = 1;
            if ($param['type_space'] !== $spaces) {
                $error = 'Expected %s spaces after parameter type; %s found';
                $data = [
                    $spaces,
                    $param['type_space'],
                ];

                $fix = $phpcsFile->addFixableError(
                    $error,
                    $param['tag'],
                    'SpacingAfterParamType',
                    $data
                );
                if ($fix === true) {
                    $content = $param['type'];
                    $content .= str_repeat(' ', $spaces);
                    $content .= $param['var'];
                    $content .= str_repeat(' ', $param['var_space']);
                    $content .= $param['comment'];
                    $phpcsFile->fixer->replaceToken(
                        ($param['tag'] + 2),
                        $content
                    );
                }
            }

            // Make sure the param name is correct.
            if (isset($realParams[$pos]) === true) {
                $realName = $realParams[$pos]['name'];
                if ($realName !== $param['var']) {
                    $code = 'ParamNameNoMatch';
                    $data = [
                        $param['var'],
                        $realName,
                    ];

                    $error = 'Doc comment for parameter %s does not match ';
                    if (strtolower($param['var']) === strtolower($realName)) {
                        $error .= 'case of ';
                        $code = 'ParamNameNoCaseMatch';
                    }

                    $error .= 'actual variable name %s';

                    $phpcsFile->addError($error, $param['tag'], $code, $data);
                }
            } else {
                if (substr($param['var'], -4) !== ',...') {
                    // We must have an extra parameter comment.
                    $error = 'Superfluous parameter comment';
                    $phpcsFile->addError(
                        $error,
                        $param['tag'],
                        'ExtraParamComment'
                    );
                }
            }

            if ($param['comment'] === '') {
                continue;
            }

            // Check number of spaces after the var name.
            $spaces = 1;
            if ($param['var_space'] !== $spaces) {
                $error = 'Expected %s spaces after parameter name; %s found';
                $data = [
                    $spaces,
                    $param['var_space'],
                ];

                $fix = $phpcsFile->addFixableError(
                    $error,
                    $param['tag'],
                    'SpacingAfterParamName',
                    $data
                );

                if ($fix === true) {
                    $content = $param['type'];
                    $content .= str_repeat(' ', $param['type_space']);
                    $content .= $param['var'];
                    $content .= str_repeat(' ', $spaces);
                    $content .= $param['comment'];
                    $phpcsFile->fixer->replaceToken(
                        ($param['tag'] + 2),
                        $content
                    );
                }
            }
        }
    }

    /**
     * Find all use statements.
     *
     * @param PHP_CodeSniffer_File $file
     *
     * @return array
     */
    protected function findUseStatements(File $file)
    {
        if (array_key_exists($file->getFilename(), static::$useCache)) {
            return static::$useCache[$file->getFilename()];
        }

        $tokens = $file->getTokens();
        $usePosition = $file->findNext(T_USE, 0);
        $useStatements = [];

        while ($usePosition !== false) {
            if ($this->shouldIgnoreUse($file, $usePosition)) {
                $usePosition = $file->findNext(T_USE, $usePosition + 1);
                continue;
            }

            $fullPath = '';
            $lastString = '';
            $alias = '';

            $useEnd = $file->findNext(
                [T_SEMICOLON, T_COMMA],
                $usePosition
            );
            $useFullPathEnd = $file->findNext(
                [T_SEMICOLON, T_COMMA, T_WHITESPACE],
                $usePosition + 2
            );

            $afterUse = $file->findNext(
                [T_STRING, T_NS_SEPARATOR],
                $usePosition
            );

            while ($afterUse !== false) {
                $fullPath .= $tokens[$afterUse]['content'];

                if ($tokens[$afterUse]['code'] == T_STRING) {
                    $lastString = $tokens[$afterUse]['content'];
                }

                $afterUse = $file->findNext(
                    [T_STRING, T_NS_SEPARATOR],
                    $afterUse + 1,
                    $useFullPathEnd
                );
            }

            if ($useFullPathEnd != $useEnd) {
                if ($tokens[$useFullPathEnd + 1]['code'] !== T_AS) {
                    continue;
                }

                if ($tokens[$useFullPathEnd + 3]['code'] === T_STRING) {
                    $alias = $tokens[$useFullPathEnd + 3]['content'];
                }
            } else {
                $alias = $lastString;
            }

            $useStatements[$fullPath] = $alias;

            $usePosition = $file->findNext(T_USE, $usePosition + 1);
        }

        static::$useCache[$file->getFilename()] = $useStatements;

        return $useStatements;
    }

    /**
     * Check whether or not a USE statement should be ignored.
     *
     * @param PHP_CodeSniffer_File $file
     * @param $stackPtr
     *
     * @return bool
     */
    protected function shouldIgnoreUse(File $file, $stackPtr)
    {
        $tokens = $file->getTokens();

        // Ignore USE keywords inside closures.
        $next = $file->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($tokens[$next]['code'] === T_OPEN_PARENTHESIS) {
            return true;
        }

        // Ignore USE keywords for traits.
        if ($file->hasCondition($stackPtr, [T_CLASS, T_TRAIT]) === true) {
            return true;
        }

        return false;
    }

    protected function checkParamType($type, $pos, File $file)
    {
        if (empty($type)) {
            return;
        }

        if (strpos($type, '\\') !== false) {
            $file->addError(
                'Docblock types should use the basic class name, not the full'
                . ' path.',
                $pos,
                'DocblockFullType'
            );

            return;
        }

        $aliases = array_values($this->findUseStatements($file));

        $type = $this->resolveArrayType($type);

        if ($this->isPrimitiveType($type)) {
            return;
        }

        if (in_array($type, $aliases)) {
            return;
        }

        $directory = dirname($file->phpcs->realpath($file->getFilename()));
        if (file_exists($directory . '/' . $type . '.php')) {
            return;
        }

        $file->addError(
            'The type %s was not found in the class use statements.',
            $pos,
            'DockblockTypeImport',
            [$type]
        );
    }

    /**
     * Attempt to resolve the type of an array.
     *
     * @param $type
     *
     * @return string
     */
    protected function resolveArrayType($type)
    {
        if (strrpos($type, '[]', -2) !== false) {
            return substr($type, 0, strlen($type) - 2);
        }

        return $type;
    }

    /**
     * Check if a type is primitive.
     *
     * @param $type
     *
     * @return bool
     */
    protected function isPrimitiveType($type)
    {
        switch ($type) {
            case 'string':
            case 'int':
            case 'integer':
            case 'bool':
            case 'boolean':
            case 'float':
            case 'double':
            case 'string':
            case 'null':
            case 'array';
            case 'mixed':
            case 'static':
            case 'self':
            case 'callable':
            case 'resource':
            case 'object':
                return true;
            default:
                return false;
        }
    }

    /**
     * Process the return comment of this function comment.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token
     *                                           in the stack passed in
     *     $tokens.
     * @param int $commentStart The position in the stack where the comment
     *     started.
     *
     * @return void
     */
    protected function processReturn(
        PHP_CodeSniffer_File $phpcsFile,
        $stackPtr,
        $commentStart
    ) {
        $tokens = $phpcsFile->getTokens();

        $return = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@return') {
                if ($return !== null) {
                    $error =
                        'Only 1 @return tag is allowed in a function comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateReturn');

                    return;
                }

                if ($tokens[$tag + 2]['code'] === T_DOC_COMMENT_STRING) {
                    $types = explode('|', $tokens[$tag + 2]['content']);

                    foreach ($types as $type) {
                        $this->checkParamType($type, $tag + 2, $phpcsFile);
                    }
                }

                $return = $tag;
            }
        }

        return;
    }

    /**
     * Extract the first namespace found in the file.
     *
     * @param PHP_CodeSniffer_File $file
     *
     * @return string
     */
    protected function extractNamespace(
        File $file
    ) {
        $namespace = '';

        $tokens = $file->getTokens();

        $prev = $file->findNext(T_NAMESPACE, 0);

        for ($i = $prev + 2; $i < count($tokens); $i++) {
            if (!in_array($tokens[$i]['code'], [T_STRING, T_NS_SEPARATOR])) {
                break;
            }

            $namespace .= $tokens[$i]['content'];
        }

        return $namespace;
    }

    protected function beginsWith($haystack, $needle) {
        // Search backwards starting from haystack length characters from the
        // end.
        return $needle === ""
            || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
}
