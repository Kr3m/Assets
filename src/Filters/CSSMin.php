<?php

namespace Bonfire\Assets\Filters;

use Bonfire\Assets\Asset;

/**
 * Filters assets through CssMin.
 *
 * @link http://code.google.com/p/cssmin
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class CSSMin implements FilterInterface {

    protected $filters = [
        "ImportImports"                 => false,
        "RemoveComments"                => true,
        "RemoveEmptyRulesets"           => true,
        "RemoveEmptyAtBlocks"           => true,
        "ConvertLevel3AtKeyframes"      => false,
        "ConvertLevel3Properties"       => false,
        "Variables"                     => true,
        "RemoveLastDelarationSemiColon" => true
    ];

    protected $plugins = [
        "Variables"                     => true,
        "ConvertFontWeight"             => false,
        "ConvertHslColors"              => false,
        "ConvertRgbColors"              => false,
        "ConvertNamedColors"            => false,
        "CompressColorValues"           => false,
        "CompressUnitValues"            => true,
        "CompressExpressionValues"      => true
    ];

    //--------------------------------------------------------------------

    public function __construct ($params = [])
    {
        if (isset($params['filters']) && is_array($params['filters']))
        {
            $this->filters = array_merge($this->filters, $params['filters']);
        }

        if (isset($params['plugins']) && is_array($params['plugins']))
        {
            $this->plugins = array_merge($this->plugins, $params['plugins']);
        }
    }
    
    //--------------------------------------------------------------------
    
    public function run (Asset $asset)
    {
        $contents = $asset->getContent();

        $contents = \CssMin::minify($contents, $this->filters, $this->plugins);

        $asset->setContent($contents);
    }

    //--------------------------------------------------------------------

}