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
use MD\Foundation\Utils\StringUtils;

use Splot\AssetsModule\Assets\AssetsFinder;

abstract class AssetsContainer
{

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
	protected $_packages = array('lib', 'app', 'page');

	/**
	 * Assets finder service.
	 * 
	 * @var AssetsFinder
	 */
	protected $_finder;

	/**
	 * Constructor.
	 * 
	 * @param AssetsFinder $finder Assets finder service.
	 */
	public function __construct(AssetsFinder $finder) {
		$this->_finder = $finder;
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
	 * @param string $resource Resource link to the asset.
	 * @param string $package [optional] Package name for this asset. Default: 'app'.
	 * @param int $priority [optional] Priority of this asset. The higher it is, the earlier in the output it will be embedded. Default: 0.
	 */
	public function addAsset($resource, $package = 'app', $priority = 0) {
		$this->_assets[$resource] = array(
			'resource' => $resource,
			'package' => $package,
			'priority' => $priority
		);

		// reset sorted assets
		$this->_sortedAssets = array();
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

		$sortedAssets = ArrayUtils::multiSort($this->_assets, 'priority', true);
		$packagedAssets = ArrayUtils::categorizeByKey($sortedAssets, 'package');
		$sortedPackagedAssets = array();

		foreach($this->_packages as $name) {
			if (isset($packagedAssets[$name])) {
				$sortedPackagedAssets[$name] = ArrayUtils::keyFilter($packagedAssets[$name], 'resource');
				unset($packagedAssets[$name]);
			}
		}

        foreach($packagedAssets as $package => $assets) {
            $packagedAssets[$package] = ArrayUtils::keyFilter($assets, 'resource');
        }

		$this->_sortedAssets = array_merge($sortedPackagedAssets, $packagedAssets);
	}

	/*
	 * SETTERS AND GETTERS
	 */
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