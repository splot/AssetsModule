<?php
namespace Splot\AssetsModule\Commands;

use Splot\Framework\Console\AbstractCommand;
use Splot\Framework\Modules\AbstractModule;

class Install extends AbstractCommand 
{

    protected static $name = 'install';
    protected static $description = 'Installs assets from application and its modules into a public dir.';

    /**
     * Installs assets from application and its modules into a public dir.
     */
    public function execute() {
        $this->writeln();

        $application = $this->get('application');

        // get SplotAssetsModule and its config, and filesystem
        $assetsModule = $application->getModule('SplotAssetsModule');
        $config = $assetsModule->getConfig();

        // create dirs 
        $rootDir = $this->getParameter('root_dir');
        $applicationAssetsDir = $rootDir . $config->get('application_dir');
        $moduleAssetsDir = $rootDir . $config->get('modules_dir');

        // install global application assets
        $this->installApplicationAssets($application->getApplicationDir(), $applicationAssetsDir);

        // install assets for all modules
        $modules = $application->getModules();
        foreach($modules as $module) {
            $this->installModuleAssets($module, $moduleAssetsDir);
        }

        $this->writeln('Done.');
    }

    /**
     * Installs global application assets as a symlink at $linkDir.
     * 
     * @param string $applicationDir Application directory.
     * @param string $linkDir Where the link should be located.
     */
    protected function installApplicationAssets($applicationDir, $linkDir) {
        $filesystem = $this->get('filesystem');
        $rootDir = $this->getParameter('root_dir');
        $linkDir = rtrim($linkDir, '/');

        $applicationAssetsDir = $applicationDir .'Resources/public';

        // break if there are no assets added to the application
        if (!is_dir($applicationAssetsDir)) {
            return false;
        }

        $this->writeln('Installing <info>application</info> assets into <comment>'. $filesystem->makePathRelative($linkDir, $rootDir) .'</comment>');

        $filesystem->remove($linkDir);
        $filesystem->symlink($applicationAssetsDir, $linkDir, false);
    }

    /**
     * Installs module assets as a symlink at $linkDir.
     * 
     * @param AbstractModule $module Module for which assets should be installed.
     * @param string $linkDir Where the link should be located.
     */
    protected function installModuleAssets(AbstractModule $module, $linkDir) {
        $filesystem = $this->get('filesystem');
        $rootDir = $this->getParameter('root_dir');
        $linkDir = rtrim($linkDir, '/') .'/';

        $moduleAssetsDir = $module->getModuleDir() .'Resources/public';

        // break if no assets have been added to this module
        if (!file_exists($moduleAssetsDir) || !is_dir($moduleAssetsDir)) {
            return false;
        }

        $moduleLinkDir = $linkDir . preg_replace('/module$/', '', strtolower($module->getName()));

        $this->writeln('Installing assets for <info>'. $module->getName() .'</info> into <comment>'. $filesystem->makePathRelative($moduleLinkDir, $rootDir) .'</comment>');

        $filesystem->remove($moduleLinkDir);
        $filesystem->symlink($moduleAssetsDir, $moduleLinkDir, false);
    }

}