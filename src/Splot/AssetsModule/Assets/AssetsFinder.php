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
     * Cache in which the finder stores already resolved assets.
     * 
     * @var array
     */
    protected $_cache = array();

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
     * @param string $resource
     * @param string $type [optional] Sub directory in which this asset is. Default: ''.
     * @return string
     * 
     * @throws ResourceNotFoundException When the given resource doesn't exist.
     */
    public function getAssetUrl($resource, $type = '') {
        $cacheKey = $type .'#'. $resource;
        // try to resolve from cache
        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        // check if external asset and if so return the original
        if (stripos($resource, 'http://') === 0 || stripos($resource, 'https://') === 0 || stripos($resource, '//') === 0) {
            $this->_cache[$cacheKey] = $resource;
            return $this->_cache[$cacheKey];
        }

        // check if asset even exists
        try {
            $files = $this->getAssetPath($resource, $type);
            $files = !is_array($files) ? array($files) : $files;
        } catch(ResourceNotFoundException $e) {
            // rethrow with a different message
            throw new ResourceNotFoundException('Could not find asset "'. $resource .'".');
        }

        $urls = array();

        // check if asset from web dir
        if (stripos($resource, '@') === 0) {
            foreach($files as $file) {
                $urls[] = '/'. substr($file, strlen($this->_webDir));
            }

            $this->_cache[$cacheKey] = count($urls) === 1 ? $urls[0] : $urls;
            return $this->_cache[$cacheKey];
        }

        // asset from app or module, so parse some sections of it
        list($resource, $type) = $this->transformSubdir($resource, $type);
        $nameArray = explode(':', $resource);

        $appAssetsDir = $this->_application->getApplicationDir() .'Resources/public/';
        $appAssetsDirLength = strlen($appAssetsDir);

        if (!empty($nameArray[0])) {
            $module = $this->_application->getModule($nameArray[0]);
            $moduleAssetName = preg_replace('/module$/', '', strtolower($module->getName())) .'/';
            $moduleAssetsDir = $module->getModuleDir() .'Resources/public/';
            $moduleAssetsDirLength = strlen($moduleAssetsDir);

            $moduleOverwrittenAssetsDir = $this->_application->getApplicationDir() .'Resources/'. $module->getName() .'/public/';
            $moduleOverwrittenAssetsDirLength = strlen($moduleOverwrittenAssetsDir);
        }

        foreach($files as $file) {
            // from app dir?
            if (stripos($file, $appAssetsDir) === 0) {
                $urls[] = $this->_applicationAssetsDir . substr($file, $appAssetsDirLength);
                continue;
            }

            // from overwritten module dir?
            if (stripos($file, $moduleOverwrittenAssetsDir) === 0) {
                $urls[] = $this->_overwrittenModuleAssetsDir . $moduleAssetName . substr($file, $moduleOverwrittenAssetsDirLength);
                continue;
            }

            // from module dir then
            $urls[] = $this->_modulesAssetsDir . $moduleAssetName . substr($file, $moduleAssetsDirLength);
        }

        $this->_cache[$cacheKey] = count($urls) === 1 ? $urls[0] : $urls;
        return $this->_cache[$cacheKey];
    }

    /**
     * Returns path to an asset resource.
     *
     * GLOB patterns are allowed in the file name section of the resource pattern.
     *
     * If using GLOB patterns and multiple files were found then they are returned in an array.
     * 
     * @param string $resource
     * @param string $type [optional] Sub directory in which this asset is. Default: ''.
     * @return string|array
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
            return $this->_pathsCache[$cacheKey];
        }

        // check if asset from web dir
        if (stripos($resource, '@') === 0) {
            $path = $this->_webDir . ltrim(substr($resource, 1), '/');
            $files = FilesystemUtils::glob($path, GLOB_NOCHECK | GLOB_BRACE);

            if ($files) {
                // if glob returned more than one file then just return it - it wouldn't return nonexistent files
                if (count($files) > 1) {
                    $this->_pathsCache[$cacheKey] = $files;
                    return $this->_pathsCache[$cacheKey];
                }

                $files = count($files) === 1 ? $files[0] : '';
            }

            if (!file_exists($files)) {
                throw new ResourceNotFoundException('Resource "'. $resource .'" not found in web dir.');
            }

            $this->_pathsCache[$cacheKey] = $files;
            return $this->_pathsCache[$cacheKey];
        }

        list($resource, $type) = $this->transformSubdir($resource, $type);
        $type = !empty($type) ? DS . trim($type, DS) : $type;

        // delegate this task to resource finder
        $files = $this->_finder->find($resource, 'public'. $type);

        $this->_pathsCache[$cacheKey] = $files;
        return $this->_pathsCache[$cacheKey];
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