<?php
/**
 * Interface for a minifier that performs actual minification (obfuscation) of an asset.
 * 
 * @package SplotAssetsModule
 * @subpackage Assets
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Assets;

use Splot\AssetsModule\Assets\Asset;

interface MinifierInterface
{

    /**
     * Minify the given asset.
     * 
     * @param  Asset  $asset Asset to be minified.
     */
    function minifyAsset(Asset $asset);

}