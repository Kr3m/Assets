<?php

namespace Bonfire\Assets\Filters;

use Bonfire\Assets\Asset;

class CSSCompress implements FilterInterface {

    public function run (Asset $asset)
    {
        $contents = $asset->getContent();

        // Remove comments
        $contents = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $contents);

        // Remove space after colons
        $contents = str_replace(': ', ':', $contents);

        // Remove space before braces
        $contents = str_replace(' {', '{', $contents);

        // Remove Whitespace
        $contents = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $contents);
        
        $asset->setContent($contents);
    }

    //--------------------------------------------------------------------


}