<?php
/**
 * JavaScript Assets container.
 * 
 * @package SplotAssetsModule
 * @subpackage Twig
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Twig\Extension;

use Splot\AssetsModule\Assets\AssetsContainer\JavaScriptContainer;
use Splot\AssetsModule\Assets\AssetsContainer\StylesheetContainer;
use Splot\AssetsModule\Assets\AssetsFinder;

class AssetsExtension extends \Twig_Extension
{

	/**
	 * Assets finder.
	 * 
	 * @var AssetsFinder
	 */
	protected $_finder;

	/**
	 * Javascript assets container.
	 * 
	 * @var JavaScriptContainer
	 */
	protected $_javascripts;

	/**
	 * Stylesheets assets container.
	 * 
	 * @var StylesheetContainer
	 */
	protected $_stylesheets;

	/**
	 * Constructor.
	 * 
	 * @param Finder $resourceFinder Resource finder.
	 * @param JavaScriptContainer $javascripts JavaScript container service.
	 * @param StylesheetContainer $stylesheets Stylesheets container service.
	 */
	public function __construct(AssetsFinder $finder, JavaScriptContainer $javascripts, StylesheetContainer $stylesheets) {
		$this->_finder = $finder;
		$this->_javascripts = $javascripts;
		$this->_stylesheets = $stylesheets;
	}

	/**
	 * Returns Twig functions registered by this extension.
	 * 
	 * @return array
	 */
	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction('javascript', array($this, 'addJavaScript')),
			new \Twig_SimpleFunction('javascripts', array($this, 'addJavaScriptsPlaceholder'), array('is_safe' => array('html'))),
			new \Twig_SimpleFunction('stylesheet', array($this, 'addStyleSheet')),
			new \Twig_SimpleFunction('stylesheets', array($this, 'addStyleSheetsPlaceholder'), array('is_safe' => array('html'))),
			new \Twig_SimpleFunction('asset', array($this, 'getAssetUrl')),
			new \Twig_SimpleFunction('asset_path', array($this, 'getAssetPath'))
		);
	}

	/**
	 * Returns the name of this extension.
	 * 
	 * @return string
	 */
	public function getName() {
		return 'splot_assets';
	}

	/*
	 * EXTENSION FUNCTIONS
	 */
	/**
	 * Adds a JavaScript asset to its container.
	 * 
	 * @param string $resource Resource link to the JavaScript file.
	 * @param string $package Package in which to put this asset.
	 * @param int $priority [optional] Priority of this asset. Default: 0.
	 */
	public function addJavaScript($resource, $package = 'app', $priority = 0) {
		$this->_javascripts->addAsset($resource, $package, $priority);
	}

	/**
	 * Adds a StyleSheet asset to its container.
	 * 
	 * @param string $resource Resource link to the StyleSheet file.
	 * @param string $package Package in which to put this asset.
	 * @param int $priority [optional] Priority of this asset. Default: 0.
	 */
	public function addStyleSheet($resource, $package = 'app', $priority = 0) {
		$this->_stylesheets->addAsset($resource, $package, $priority);
	}

	/**
	 * Returns JavaScript container placeholder to mark the place where JavaScripts should be output.
	 * 
	 * @return string
	 */
	public function addJavaScriptsPlaceholder() {
		return $this->_javascripts->getPlaceholder();
	}

	/**
	 * Returns StyleSheets container placeholder to mark the place where JavaScripts should be output.
	 * 
	 * @return string
	 */
	public function addStyleSheetsPlaceholder() {
		return $this->_stylesheets->getPlaceholder();
	}

	/**
	 * Returns URL of an asset.
	 * 
	 * @param string $resource Resource link to the asset.
	 * @return string
	 */
	public function getAssetUrl($resource) {
		return $this->_finder->getAssetUrl($resource);
	}

	/**
	 * Returns file path to an asset.
	 * 
	 * @param string $resource Resource link to the asset.
	 * @return string
	 */
	public function getAssetPath($resource) {
		return $this->_finder->getAssetPath($resource);
	}

}