<?php
/**
 * JavaScript Assets container.
 * 
 * @package SplotAssetsModule
 * @subpackage Assets
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Assets\AssetsContainer;

use MD\Foundation\Utils\ArrayUtils;

use Splot\AssetsModule\Assets\AssetsContainer;

class JavaScriptContainer extends AssetsContainer
{

    /**
     * Assets type for this container.
     * 
     * @var string
     */
    protected $_type = 'js';

    /**
     * Returns <script> tags for all added javascripts.
     * 
     * @return string
     */
    public function printAssets() {
        $output = '';

        foreach($this->getSortedAssets() as $package => $assets) {
            // if minification enabled then replace the assets with minified ones
            if ($this->_minify) {
                $assets = $this->_minifier->minify($assets, $package);
            }

            foreach($assets as $asset) {
                $output .= '<script type="text/javascript" src="'. $asset->getUrl() .'" data-package="'. $package .'"></script>'. NL;
            }
        }

        return $output;
    }

}