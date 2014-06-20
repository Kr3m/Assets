<?php

namespace Bonfire\Assets\Filters;

use Bonfire\Assets\Asset;

/**
 * Filters assets through JsMin.
 *
 * All credit for the filter itself is mentioned in the file itself.
 *
 * @link https://raw.github.com/mrclay/minify/master/min/lib/JSMin.php
 * @author Brunoais <brunoaiss@gmail.com>
 */
class JSMin implements FilterInterface {

    public function run (Asset $asset)
    {
        $contents = \JSMin::minify($asset->getContent());

        $asset->setContent($contents);
    }

    //--------------------------------------------------------------------

}