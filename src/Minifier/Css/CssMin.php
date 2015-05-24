<?php
/**
 * CSS minifier that uses CssMin library for minification.
 * 
 * @package SplotAssetsModule
 * @subpackage Minifier
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Minifier\Css;

use CssMin as CssMinParser;

use Splot\AssetsModule\Assets\Asset;
use Splot\AssetsModule\Assets\MinifierInterface;

class CssMin implements MinifierInterface
{

    /**
     * Minifies the given CSS asset.
     * 
     * @param  Asset  $asset CSS asset to be minified.
     */
    public function minifyAsset(Asset $asset) {
        file_put_contents(
            $asset->getPath(),
            CssMinParser::minify(
                file_get_contents($asset->getPath()),
                array(),
                array()
            )
        );
    }

}