<?php

/**
 * Chroma Class Comment Sniff
 *
 * Makes sure a class comment exists and has a minimum set of parameters
 * (author, package) and enforces a simple order on every file
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 */
class Chroma_Sniffs_Commenting_ClassCommentSniff extends PEAR_Sniffs_Commenting_ClassCommentSniff
{
    /**
     * Custom tag definitions for the Chroma standard
     *
     * @var array
     */
    protected $tags = [
        '@category' => [
            'required' => false,
            'allow_multiple' => false,
        ],
       '@author' => [
            'required' => true,
            'allow_multiple' => true,
        ],
       '@copyright' => [
            'required' => false,
            'allow_multiple' => true,
        ],
       '@license' => [
            'required' => false,
            'allow_multiple' => false,
        ],
       '@version' => [
            'required' => false,
            'allow_multiple' => false,
        ],
       '@link' => [
            'required' => false,
            'allow_multiple' => true,
        ],
       '@see' => [
            'required' => false,
            'allow_multiple' => true,
        ],
       '@since' => [
            'required' => false,
            'allow_multiple' => false,
        ],
       '@deprecated' => [
            'required' => false,
            'allow_multiple' => false,
        ],
       '@package' => [
            'required' => true,
            'allow_multiple' => false,
        ],
       '@subpackage' => [
            'required' => false,
            'allow_multiple' => false,
        ],
    ];
}
