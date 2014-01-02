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

use MD\Foundation\Exceptions\InvalidArgumentException;
use MD\Foundation\Utils\FilesystemUtils;

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
     * Path to the public web directory.
     * 
     * @var string
     */
    protected $_webDir;

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
     * URL of publicly available directory where overwritten modules assets are stored.
     * 
     * @var string
     */
    protected $_overwrittenModuleAssetsDir;

    /**
     * Cache in which the finder stores already resolved assets urls.
     * 
     * @var array
     */
    protected $_urlCache = array();

    /**
     * Cache in which the finder stores already resolved assets paths.
     * 
     * @var array
     */
    protected $_pathsCache = array();

    /**
     * Constructor.
     * 
     * @param AbstractApplication $application Application.
     * @param Finder $finder Resource finder.
     * @param string $applicationAssetsDir Directory for application assets.
     * @param string $modulesAssetsDir Directory for modules assets.
     * @param string $overwrittenModuleAssetsDir Directory for modules assets that were overwritten in the app directory.
     */
    public function __construct(
        AbstractApplication $application,
        Finder $finder,
        $webDir,
        $applicationAssetsDir,
        $modulesAssetsDir,
        $overwrittenModulesAssetsDir
    ) {
        $this->_application = $application;
        $this->_finder = $finder;
        $this->_webDir = $webDir;
        $this->_applicationAssetsDir = '/'. trim($applicationAssetsDir, '/') .'/';
        $this->_modulesAssetsDir = '/'. trim($modulesAssetsDir, '/') .'/';
        $this->_overwrittenModuleAssetsDir = '/'. trim($overwrittenModulesAssetsDir, '/') .'/';
    }

    /**
     * Returns URL for the given asset resource.
     * 
     * @param string $resource Asset name.
     * @param string $type [optional] Sub directory in which this asset is. Default: ''.
     * @return string
     * 
     * @throws ResourceNotFoundException When the given resource doesn't exist.
     */
    public function getAssetUrl($resource, $type = '') {
        $cacheKey = $type .'#'. $resource;
        if (isset($this->_urlCache[$cacheKey])) {
            return $this->_urlCache[$cacheKey];
        }

        // check if external asset and if so return the original
        if (stripos($resource, 'http://') === 0 || stripos($resource, 'https://') === 0 || stripos($resource, '//') === 0) {
            $this->_urlCache[$cacheKey] = $resource;
            return $resource;
        }

        // check if resource even exists
        try {
            $file = $this->getAssetPath($resource, $type);
        } catch(ResourceNotFoundException $e) {
            throw new ResourceNotFoundException('Could not find asset "'. $resource .'".'); // rethrow with a different message
        }

        // check if asset from web dir
        if (stripos($resource, '@') === 0) {
            $url = '/'. mb_substr($file, mb_strlen($this->_webDir));
            $this->_urlCache[$cacheKey] = $url;
            return $url;
        }

        // adjust and parse the resource name
        list($resource, $type) = $this->transformSubdir($resource, $type);
        list($moduleName, $subDir, $resourceFile) = explode(':', $resource);

        $appAssetsDir = $this->_application->getApplicationDir() .'Resources'. DS .'public'. DS;

        if (!empty($moduleName)) {
            $module = $this->_application->getModule($moduleName);
            $moduleAssetsDir = $module->getModuleDir() .'Resources'. DS .'public'. DS;
            $moduleOverwrittenAssetsDir = $this->_application->getApplicationDir() .'Resources'. DS . $module->getName() . DS .'public'. DS;

            $moduleAssetName = preg_replace('/module$/', '', mb_strtolower($module->getName())) .'/';
        }

        // from app dir?
        if (stripos($file, $appAssetsDir) === 0) {
            $url = $this->_applicationAssetsDir . mb_substr($file, mb_strlen($appAssetsDir));
        } else if (stripos($file, $moduleOverwrittenAssetsDir) === 0) {
            // from overwritten module dir?
            $url = $this->_overwrittenModuleAssetsDir . $moduleAssetName . mb_substr($file, mb_strlen($moduleOverwrittenAssetsDir));
        } else {
            // from module dir then
            $url = $this->_modulesAssetsDir . $moduleAssetName . mb_substr($file, mb_strlen($moduleAssetsDir));
        }

        $this->_urlCache[$cacheKey] = $url;
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
        $cacheKey = $type .'#'. $resource;
        if (isset($this->_pathsCache[$cacheKey])) {
            return $this->_pathsCache[$cacheKey];
        }

        // check if external asset and if so return the original
        if (stripos($resource, 'http://') === 0 || stripos($resource, 'https://') === 0 || stripos($resource, '//') === 0) {
            $this->_pathsCache[$cacheKey] = $resource;
            return $resource;
        }

        // check if asset from web dir
        if (stripos($resource, '@') === 0) {
            $path = $this->_webDir . ltrim(mb_substr($resource, 1), DS);

            if (!file_exists($path)) {
                throw new ResourceNotFoundException('Resource "'. $resource .'" not found in web dir.');
            }

            $this->_pathsCache[$cacheKey] = $path;
            return $path;
        }

        list($resource, $type) = $this->transformSubdir($resource, $type);
        $type = !empty($type) ? DS . trim($type, DS) : $type;

        // delegate this task to resource finder
        $path = $this->_finder->findResource($resource, 'public'. $type);

        $this->_pathsCache[$cacheKey] = $path;
        return $path;
    }

    /**
     * Expands GLOB assets pattern.
     * 
     * @param  string $resource Resource name or pattern to be expanded.
     * @param  string $type     Asset type.
     * @return array
     */
    public function expand($resource, $type = '') {
        // check if external asset and if so return the original
        if (stripos($resource, 'http://') === 0 || stripos($resource, 'https://') === 0 || stripos($resource, '//') === 0) {
            return array($resource);
        }

        // check if asset from web dir
        if (stripos($resource, '@') === 0) {
            $pattern = ltrim(mb_substr($resource, 1), DS);
            $webDirLength = mb_strlen($this->_webDir);

            $files = FilesystemUtils::glob($this->_webDir . $pattern, FilesystemUtils::GLOB_ROOTFIRST | GLOB_BRACE);
            $resources = array();
            foreach($files as $file) {
                $resources[] = '@/'. mb_substr($file, $webDirLength);
            }

            return $resources;
        }

        list($moduleName, $subDir, $filePattern) = explode(':', $resource);
        list($resource, $type) = $this->transformSubdir($resource, $type);
        $type = !empty($type) ? DS . trim($type, DS) : $type;

        // delegate this task to resource finder
        $resources = $this->_finder->expand($resource, 'public'. $type);

        // we need to fix the sub dir there
        if (!empty($subDir)) {
            foreach($resources as $i => $resource) {
                $resources[$i] = str_replace('::', ':'. $subDir .':', $resource);
            }
        }

        return $resources;
    }

    /*****************************************
     * HELPERS
     *****************************************/
    /**
     * Transforms the resource name for the given type to one matching all criteria in the app resource finder.
     * 
     * @param string $resource
     * @param string $type [optional] Sub directory in which this asset is. Default: ''.
     * @return string
     */
    protected function transformSubdir($resource, $type = '') {
        $nameArray = explode(':', $resource);

        if (count($nameArray) !== 3) {
             throw new InvalidArgumentException('in format "ModuleName:[subDir]:filename"', $resource);
        }

        if (!empty($nameArray[1])) {
            $type = $nameArray[1] . DS . ltrim($type, DS);
            $nameArray[1] = '';
        }

        return array(
            implode(':', $nameArray),
            $type
        );
    }

}