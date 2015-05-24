<?php
/**
 * JavaScript minifier that uses JSqueeze library for minification.
 * 
 * @package SplotAssetsModule
 * @subpackage Minifier
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Minifier\JavaScript;

use JSqueeze as JSqueezeParser;

use Splot\AssetsModule\Assets\Asset;
use Splot\AssetsModule\Assets\MinifierInterface;

class JSqueeze implements MinifierInterface
{

    /**
     * Minifies the given JavaScript asset.
     * 
     * @param  Asset  $asset JavaScript asset to be minified.
     */
    public function minifyAsset(Asset $asset) {
        $squeeze = new JSqueezeParser();

        file_put_contents($asset->getPath(), $squeeze->squeeze(
            file_get_contents($asset->getPath()),
            true, // single line
            true, // keep important comments
            false // don't use jsqueeze special vars
        ));
    }

}