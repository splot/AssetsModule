<?php
/**
 * Assets container.
 * 
 * @package SplotAssetsModule
 * @subpackage Assets
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Assets;

use MD\Foundation\Debug\Debugger;
use MD\Foundation\Utils\ArrayUtils;
use MD\Foundation\Utils\ObjectUtils;
use MD\Foundation\Utils\StringUtils;

use Splot\AssetsModule\Assets\Asset;
use Splot\AssetsModule\Assets\AssetsFinder;
use Splot\AssetsModule\Assets\AssetsMinifier;

abstract class AssetsContainer
{

    const DEFAULT_PACKAGE = 'page';

    /**
     * Assets type for this container.
     * 
     * @var string
     */
    protected $_type;

    /**
     * Placeholder that is inserted in a template to mark where the assets should be injected.
     * 
     * @var string
     */
    protected $_placeholder;

    /**
     * List of all included assets.
     * 
     * @var array
     */
    protected $_assets = array();

    /**
     * Sorted list of all included assets.
     * 
     * Categorized by package name as well.
     * 
     * @var array
     */
    protected $_sortedAssets = array();

    /**
     * Sort order of packages.
     * 
     * @var array
     */
    protected $_packages = array('lib', 'app', self::DEFAULT_PACKAGE);

    /**
     * Assets finder service.
     * 
     * @var AssetsFinder
     */
    protected $_finder;

    /**
     * Asset minifier.
     * 
     * @var Minifier
     */
    protected $_minifier;

    /**
     * Should minify assets?
     * 
     * @var boolean
     */
    protected $_minify = false;

    /**
     * Constructor.
     * 
     * @param AssetsFinder $finder Assets finder service.
     * @param AssetsMinifier $minfier Assets minifier appropriate for the type of the container (js/css).
     * @param boolean $minify [optional] Should assets be minified when printing them out? Default: false.
     * @param string $type Type of assets (for resolving their paths and URL's).
     */
    public function __construct(AssetsFinder $finder, AssetsMinifier $minifier, $minify = false, $type = null) {
        $this->_finder = $finder;
        $this->_minifier = $minifier;
        $this->_minify = $minify;

        if ($type) {
            $this->_type = $type;
        }
    }

    /**
     * Prints out HTML tags that should be used to embed all added assets.
     * 
     * @return string
     */
    abstract public function printAssets();

    /**
     * Adds an asset.
     *
     * Can use GLOB patterns when adding assets - they will get expanded and each asset will be added separately
     * with the same parameters (package and priority).
     *
     * Returns an array collection of added assets and their data.
     * 
     * @param string $resource Resource link to the asset.
     * @param string $package [optional] Package name for this asset. Default: self::DEFAULT_PACKAGE.
     * @param int $priority [optional] Priority of this asset. The higher it is, the earlier in the output it will be embedded. Default: 0.
     * @return array
     */
    public function addAsset($resource, $package = self::DEFAULT_PACKAGE, $priority = 0, array $options = array()) {
        $resources = $this->_finder->expand($resource, $this->_type);

        $assets = array();

        foreach($resources as $resource) {
            $asset = new Asset(
                $resource,
                $this->_finder->getAssetPath($resource, $this->_type),
                $this->_finder->getAssetUrl($resource, $this->_type),
                $package,
                $priority
            );
            
            $asset->setMinify(isset($options['minify']) ? $options['minify'] : $this->_minify);

            $assets[] = $asset;
        }

        $this->_assets = array_merge($this->_assets, $assets);

        // reset sorted assets
        $this->_sortedAssets = array();

        return $assets;
    }

    /**
     * Sorts all assets by priority and categorizes them by package.
     * 
     * Packages are also sorted by package order defined in $this->_packages.
     */
    protected function sortAssets() {
        if (!empty($this->_sortedAssets)) {
            return $this->_sortedAssets;
        }

        $this->_sortedAssets = array();

        // sort all assets by priority
        $sortedAssets = ObjectUtils::multiSort($this->_assets, 'priority', true);

        // group them by packages
        $packagedAssets = ObjectUtils::groupBy($sortedAssets, 'package');

        // now add the standard packages in correct order
        foreach($this->_packages as $name) {
            if (isset($packagedAssets[$name])) {
                // move the whole package to sorted assets
                $this->_sortedAssets[$name] = $packagedAssets[$name];
                unset($packagedAssets[$name]);
            }
        }

        // now move the rest
        $this->_sortedAssets = array_merge($this->_sortedAssets, $packagedAssets);
    }

    /**
     * Removes all assets from the container.
     */
    public function clearAssets() {
        $this->_assets = array();
        $this->_sortedAssets = array();
    }

    /*************************
     * SETTERS AND GETTERS
     *************************/
    /**
     * Returns assets type for this container.
     * 
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * Returns all added assets.
     * 
     * @return array
     */
    public function getAssets() {
        return $this->_assets;
    }

    /**
     * Returns all assets sorted by priority and categorized by packages.
     * 
     * @return array
     */
    public function getSortedAssets() {
        $this->sortAssets();
        return $this->_sortedAssets;
    }

    /**
     * Returns placeholder under which this container should output HTML tags for the assets.
     * 
     * @return string
     */
    public function getPlaceholder() {
        if ($this->_placeholder) {
            return $this->_placeholder;
        }

        $this->_placeholder = '<!-- SPLOT_ASSETS_MODULE_PLACEHOLDER_'. StringUtils::random() .'_'. md5(Debugger::getClass($this) . StringUtils::random() . time()) .' -->';
        return $this->_placeholder;
    }

}