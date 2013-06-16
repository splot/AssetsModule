<?php
/**
 * Assets installator.
 * 
 * Should be executed as post-update and post-install commands on composer.
 * 
 * @package SplotAssetsModule
 * @subpackage Composer
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Composer;

use Composer\Script\Event;

use MD\Foundation\Utils\StringUtils;

use Splot\Framework\Composer\AbstractScriptHandler;
use Splot\Framework\Modules\AbstractModule;

class ScriptHandler extends AbstractScriptHandler
{

	/**
	 * Installs assets of the application and all registered modules as symlinks inside the web/ directory (or wherever else specified by the config).
	 * 
	 * @param Event $event Composer event.
	 */
	public static function installAssets(Event $event) {
		$application = self::bootApplication();

		// get SplotAssetsModule and its config, and filesystem
		$assetsModule = $application->getModule('SplotAssetsModule');
		$config = $assetsModule->getConfig();

		// create dirs 
		$rootDir = self::getContainer()->getParameter('root_dir');
		$applicationAssetsDir = $rootDir . $config->get('application_dir');
		$moduleAssetsDir = $rootDir . $config->get('modules_dir');

		// install global application assets
		static::installApplicationAssets($application->getApplicationDir(), $applicationAssetsDir);

		// install assets for all modules
		$modules = $application->getModules();
		foreach($modules as $module) {
			static::installModuleAssets($module, $moduleAssetsDir);
		}
	}

	/**
	 * Installs global application assets as a symlink at $linkDir.
	 * 
	 * @param string $applicationDir Application directory.
	 * @param string $linkDir Where the link should be located.
	 */
	protected static function installApplicationAssets($applicationDir, $linkDir) {
		$filesystem = self::getContainer()->get('filesystem');
		$rootDir = self::getContainer()->getParameter('root_dir');
		$linkDir = rtrim($linkDir, '/');

		$applicationAssetsDir = $applicationDir .'Resources/public';

		// break if there are no assets added to the application
		if (!is_dir($applicationAssetsDir)) {
			return false;
		}

		echo 'Installing application assets... '. $filesystem->makePathRelative($linkDir, $rootDir) . NL . NL;

		$filesystem->remove($linkDir);
		$filesystem->symlink($applicationAssetsDir, $linkDir, false);
	}

	/**
	 * Installs module assets as a symlink at $linkDir.
	 * 
	 * @param AbstractModule $module Module for which assets should be installed.
	 * @param string $linkDir Where the link should be located.
	 */
	protected static function installModuleAssets(AbstractModule $module, $linkDir) {
		$filesystem = self::getContainer()->get('filesystem');
		$rootDir = self::getContainer()->getParameter('root_dir');
		$linkDir = rtrim($linkDir, '/') .'/';

		$moduleAssetsDir = $module->getModuleDir() .'Resources/public';

		// break if no assets have been added to this module
		if (!file_exists($moduleAssetsDir) || !is_dir($moduleAssetsDir)) {
			return false;
		}

		$moduleLinkDir = $linkDir . preg_replace('/module$/', '', strtolower($module->getName()));

		echo 'Installing assets for "'. $module->getName() .'" in '. $filesystem->makePathRelative($moduleLinkDir, $rootDir) . NL;

		$filesystem->remove($moduleLinkDir);
		$filesystem->symlink($moduleAssetsDir, $moduleLinkDir, false);
	}

}