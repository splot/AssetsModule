<?php
/**
 * Dummy minifier that does nothing.
 * 
 * @package SplotAssetsModule
 * @subpackage Minifier
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Minifier;

use Splot\AssetsModule\Assets\Asset;
use Splot\AssetsModule\Assets\MinifierInterface;

class NullMinifier implements MinifierInterface
{

    /**
     * Does nothing.
     * 
     * @param  Asset  $asset Asset to be minified.
     */
    public function minifyAsset(Asset $asset) {
        // noop
    }

}