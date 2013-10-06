<?php
/**
 * Assets finder.
 * 
 * @package SplotAssetsModule
 * @subpackage Assets
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Assets;

use Splot\Framework\Application\AbstractApplication;
use Splot\Framework\Resources\Finder;
use Splot\Framework\Resources\Exceptions\ResourceNotFoundException;

class AssetsFinder
{

	/**
	 * Application instance from which the finder gets modules.
	 * 
	 * @var AbstractApplication
	 */
	protected $_application;

	/**
	 * Resource finder service.
	 * 
	 * @var Finder
	 */
	protected $_finder;

	/**
	 * URL of publicly available directory where application assets are stored.
	 * 
	 * @var string
	 */
	protected $_applicationAssetsDir;

	/**
	 * URL of publicly available directory where modules assets are stored.
	 * 
	 * @var string
	 */
	protected $_modulesAssetsDir;

	/**
	 * Cache in which the finder stores already resolved assets.
	 * 
	 * @var array
	 */
	protected $_cache = array();

	/**
	 * Constructor.
	 * 
	 * @param AbstractApplication $application Application.
	 * @param Finder $finder Resource finder.
	 * @param string $applicationAssetsDir Directory for application assets.
	 * @param string $modulesAssetsDir Directory for modules assets.
	 */
	public function __construct(AbstractApplication $application, Finder $finder, $applicationAssetsDir, $modulesAssetsDir) {
		$this->_application = $application;
		$this->_finder = $finder;
		$this->_applicationAssetsDir = preg_replace('/^web/', '', $applicationAssetsDir);
		$this->_modulesAssetsDir = preg_replace('/^web/', '', $modulesAssetsDir);
	}

	/**
	 * Returns URL for the given asset resource.
	 * 
	 * @param string $resource
	 * @param string $type [optional] Sub directory in which this asset is. Default: ''.
	 * @return string
	 * 
	 * @throws ResourceNotFoundException When the given resource doesn't exist.
	 */
	public function getAssetUrl($resource, $type = '') {
		// try to resolve from cache
		if (isset($this->_cache[$resource])) {
			return $this->_cache[$resource];
		}

        // check if external asset and if so return the original
        if (stripos($resource, 'http://') === 0 || stripos($resource, 'https://') === 0 || stripos($resource, '//') === 0) {
            return $resource;
        }

		// check if asset even exists
		if (!$this->getAssetPath($resource, $type)) {
			throw new ResourceNotFoundException('Could not find asset "'. $resource .'".');
		}

		$nameArray = explode(':', $resource);

		if (empty($nameArray[0])) {
			$mainDir = rtrim($this->_applicationAssetsDir, '/') .'/';
		} else {
			$module = $this->_application->getModule($nameArray[0]);
			if (!$module) {
				throw new ResourceNotFoundException('There is no module "'. $nameArray[0] .'" registered, so cannot find its asset.');
			}
			$mainDir = rtrim($this->_modulesAssetsDir, '/') .'/'. preg_replace('/module$/', '', strtolower($module->getName())) .'/';
		}

		$type = trim($type, DS);
		$type = empty($type) ? null : $type . DS;
		$subDir = trim(str_replace(NS, DS, $nameArray[1]), DS);
		$subDir = empty($subDir) ? null : $subDir . DS;

		$url = $mainDir . $type . $subDir . $nameArray[2];
		$url = '/'. ltrim($url, '/'); // make sure is absolute url

		$this->_cache[$resource] = $url;
		return $url;
	}

	/**
	 * Returns path to an asset resource.
	 * 
	 * @param string $resource
	 * @param string $type [optional] Sub directory in which this asset is. Default: ''.
	 * @return string
	 * 
	 * @throws ResourceNotFoundException When the given resource doesn't exist.
	 */
	public function getAssetPath($resource, $type = '') {
		// delegate this task to resource finder
		$type = (empty($type)) ? '' : DS . $type;
		return $this->_finder->find($resource, 'public'. $type);
	}

}