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
	 * Returns <link> tags for all included stylesheets.
	 * 
	 * @return string
	 */
	public function printAssets() {
		$output = '';

		foreach($this->getSortedAssets() as $name => $resources) {
			foreach($resources as $asset) {
				$output .= '<link rel="stylesheet" href="'. $this->_finder->getAssetUrl($asset, 'css') .'" data-package="'. $name .'">'. NL;
			}
		}

		return $output;
	}
	
}