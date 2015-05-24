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

use Splot\Framework\Composer\AbstractScriptHandler;

class ScriptHandler extends AbstractScriptHandler
{

	/**
	 * Installs assets of the application and all registered modules as symlinks inside the web/ directory (or wherever else specified by the config).
	 * 
	 * @param Event $event Composer event.
	 */
	public static function installAssets(Event $event) {
        $application = self::boot();
        $console = $application->getContainer()->get('console');
        $console->call('assets:install');
    }

}