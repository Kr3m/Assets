<?php

namespace Bonfire\Assets\Filters;

use Bonfire\Assets\Asset;

class JSCompress implements FilterInterface {

    public function run (Asset $asset)
    {
        $contents = $asset->getContent();

        // Remove comments
        $contents = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $contents);

        // Remove other spaces before/after
        $contents = preg_replace(array('(( )+\))','(\)( )+)'), ')', $contents);

        $contents = str_replace(' (', '(', $contents);

        // Remove Whitespace
        $contents = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $contents);

        $asset->setContent($contents);
    }

    //--------------------------------------------------------------------


}