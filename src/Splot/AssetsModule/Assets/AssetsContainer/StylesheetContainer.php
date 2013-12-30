<?php
/**
 * CSS Assets container.
 * 
 * @package SplotAssetsModule
 * @subpackage Assets
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Assets\AssetsContainer;

use Splot\AssetsModule\Assets\AssetsContainer;

class StylesheetContainer extends AssetsContainer
{

	/**
	 * Assets type for this container.
	 * 
	 * @var string
	 */
	protected $_type = 'css';

	/**
	 * Returns <link> tags for all included stylesheets.
	 * 
	 * @return string
	 */
	public function printAssets() {
		$output = '';

		foreach($this->getSortedAssets() as $package => $assets) {
			foreach($assets as $asset) {
				$output .= '<link rel="stylesheet" href="'. $asset['url'] .'" data-package="'. $package .'">'. NL;
			}
		}

		return $output;
	}
	
}