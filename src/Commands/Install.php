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
        $webDir = $this->getParameter('web_dir');
        $applicationAssetsDir = $webDir . $config->get('application_dir');
        $moduleAssetsDir = $webDir . $config->get('modules_dir');
        $overwrittenAssetsDir = $webDir . $config->get('overwritten_dir');

        // install global application assets
        $this->installApplicationAssets($this->getParameter('application_dir'), $applicationAssetsDir);

        // install assets for all modules
        $modules = $application->getModules();
        foreach($modules as $module) {
            $this->installModuleAssets($module, $moduleAssetsDir);

            // also install overwritten assets dirs
            $this->installOverwrittenModuleAssets($module, $this->getParameter('application_dir'), $overwrittenAssetsDir);
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

    /**
     * Installs module assets that were overwritten in the application's resources dir.
     * 
     * @param AbstractModule $module Module for which assets should be installed.
     * @param string $applicationDir Application directory.
     * @param string $linkDir Where the link should be located.
     */
    protected function installOverwrittenModuleAssets(AbstractModule $module, $applicationDir, $linkDir) {
        $filesystem = $this->get('filesystem');
        $rootDir = $this->getParameter('root_dir');
        $linkDir = rtrim($linkDir, '/') .'/';

        $overwrittenAssetsDir = $applicationDir .'Resources/'. $module->getName() .'/public';

        // break if there are no overwritten assets for this module
        if (!is_dir($overwrittenAssetsDir)) {
            return false;
        }

        $overwrittenLinkDir = $linkDir . preg_replace('/module$/', '', strtolower($module->getName()));

        $this->writeln('Installing overwritten assets for <info>'. $module->getName() .'</info> into <comment>'. $filesystem->makePathRelative($overwrittenLinkDir, $rootDir) .'</comment>');

        $filesystem->remove($overwrittenLinkDir);
        $filesystem->symlink($overwrittenAssetsDir, $overwrittenLinkDir, false);
    }

}