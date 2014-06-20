<?php

namespace Bonfire\Assets\Filters;

use Bonfire\Assets\Asset;

/**
 * Filters assets through JsMinPlus.
 *
 * All credit for the filter itself is mentioned in the file itself.
 *
 * @link http://crisp.tweakblogs.net/blog/1665/a-new-javascript-minifier-jsmin%2B.html#more
 * @author Brunoais <brunoaiss@gmail.com>
 */
class JSMinPlus implements FilterInterface {

    public function run (Asset $asset)
    {
        $contents = \JSMinPlus::minify($asset->getContent());

        $asset->setContent($contents);
    }

    //--------------------------------------------------------------------

}