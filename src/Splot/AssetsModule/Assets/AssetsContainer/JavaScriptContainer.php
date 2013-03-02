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

use Splot\AssetsModule\Assets\AssetsContainer;

class JavaScriptContainer extends AssetsContainer
{

	/**
	 * Returns <script> tags for all added javascripts.
	 * 
	 * @return string
	 */
	public function printAssets() {
		$output = '';

		foreach($this->getSortedAssets() as $name => $resources) {
			foreach($resources as $asset) {
				$output .= '<script type="text/javascript" src="'. $this->_finder->getAssetUrl($asset, 'js') .'" data-package="'. $name .'"></script>'. NL;
			}
		}

		return $output;
	}

}