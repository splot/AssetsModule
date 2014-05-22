<?php
/**
 * Generic assets minifier that combines assets into one and then delegates
 * the minification task to an injected minifier specific to the asset type.
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
use Splot\AssetsModule\Assets\MinifierInterface;

class AssetsMinifier
{

    /**
     * Actual minifier that performs minification of an asset.
     * 
     * @var MinifierInterface
     */
    protected $minifier;

    /**
     * Application web dir.
     * 
     * @var string
     */
    protected $webDir;

    /**
     * Base URL at which the minified files should be located.
     * 
     * @var [type]
     */
    protected $baseUrl;

    /**
     * Path to a directory in which the minified assets will be stored.
     * 
     * @var string
     */
    protected $targetDir;

    /**
     * File extension for combined assets file specific to the asset type, ie css/js.
     * 
     * @var string
     */
    protected $fileExtension;

    /**
     * Constructor.
     *
     * @param MinifierInterface $minifier Actual minifier that performs minification of an asset.
     *                                    This allows for dynamic configuration of used minifiers.
     * @param string $webDir        Application web dir.
     * @param string $baseUrl       Base URL at which the minified files should be located.
     * @param string $fileExtension File extension for combined assets file specific to the asset type, ie css/js.
     */
    public function __construct(MinifierInterface $minifier, $webDir, $baseUrl, $fileExtension) {
        $this->minifier = $minifier;
        $this->webDir = rtrim($webDir, '/') .'/';
        $this->baseUrl = '/'. trim($baseUrl, '/') .'/';
        $this->targetDir = $this->webDir . ltrim($this->baseUrl, '/');
        $this->fileExtension = $fileExtension;
    }

    /**
     * Minifies (combines and obfuscates) the given collection of assets and returns
     * a collection of minified assets.
     * 
     * @param  array  $assets Collection of Asset objects.
     * @param  string $prefix [optional] Prefix that the minified file should have. Default: null.
     * @return array
     */
    public function minify(array $assets, $prefix = null) {
        // if the target dir does not exist then create it
        if (!is_dir($this->targetDir)) {
            mkdir($this->targetDir, 0775, true);
        }
        
        $minified = array();

        $current = array();
        foreach($assets as $asset) {
            // if stepped on an asset that should not be minified then dump all already collected assets
            // then dump this asset
            // and then start a new collection of assets to dump
            if (
                !$asset->getMinify()
                || $asset->isRemote()
                || !file_exists($asset->getPath())
            ) {
                $minified[] = $this->doMinify($current, $prefix);
                $minified[] = $asset;
                $current = array();
                continue;
            }

            $current[] = $asset;
        }

        // and finally dump any collected assets
        $minified[] = $this->doMinify($current, $prefix);

        // remove any null values
        $minified = array_diff($minified, array(null));

        return $minified;
    }

    /**
     * Actually does combine and minify the given collection of assets
     * and returns the single minified asset.
     *
     * May return null if empty collection given.
     * 
     * @param  array  $assets Collection of Asset objects.
     * @param  string $prefix [optional] Prefix that the minified file should have. Default: null.
     * @return Asset|null
     */
    protected function doMinify(array $assets, $prefix = null) {
        // ignore when empty collection passed
        if (empty($assets)) {
            return null;
        }

        $minFileName = $this->buildCombinedFileName($assets, $prefix);
        $url = $this->baseUrl . $minFileName;
        $path = $this->targetDir . $minFileName;
        $combinedAsset = new Asset('@min', $path, $url);

        // if such file has already been generated then just return already
        if (file_exists($path)) {
            return $combinedAsset;
        }

        // otherwise we need to write all the assets into this one to concatenate it
        // stream from all assets files to a single file for minimal memory footprint
        touch($path);
        $minFile = fopen($path, 'wb');
        foreach($assets as $asset) {
            $assetPath = $asset->getPath();
            if (file_exists($assetPath)) {
                $assetFile = fopen($assetPath, 'rb');
                while (!feof($assetFile)) {
                    fwrite($minFile, fread($assetFile, 8192));
                }
                fwrite($minFile, PHP_EOL); // append new line as well
                fclose($assetFile);
            }
        }
        fclose($minFile);
        
        // and now perform minification on that combined asset by delegating it
        // to an injected minifier
        // this allows for dynamic configuration of used minifiers
        $this->minifier->minifyAsset($combinedAsset);

        // all done, return the combined asset reference
        return $combinedAsset;
    }

    /**
     * Builds a unique file name based on various data about the given collection of assets.
     *
     * @param  array  $assets Collection of Asset objects.
     * @param  string $prefix [optional] Prefix that the minified file should have. Default: null.
     * @return string
     */
    protected function buildCombinedFileName(array $assets, $prefix) {
        $name = $prefix ? $prefix .'_' : '';

        $filesInfo = array();
        foreach($assets as $asset) {
            $filesInfo[] = $asset->getPath() .'@'. filemtime($asset->getPath());
        }

        $name .= md5(implode('::::', $filesInfo)) .'.'. $this->fileExtension;
        return $name;
    }

}