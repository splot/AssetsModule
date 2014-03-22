<?php
namespace Splot\AssetsModule\Tests\Assets\AssetsContainer;

use Splot\Framework\Testing\ApplicationTestCase;

use Splot\AssetsModule\Assets\AssetsContainer\StylesheetContainer;
use Splot\AssetsModule\Assets\AssetsFinder;

/**
 * @coversDefaultClass \Splot\AssetsModule\Assets\AssetsContainer\StylesheetContainer
 */
class StylesheetContainerTest extends ApplicationTestCase
{

    public static $_applicationClass = 'Splot\AssetsModule\Tests\Assets\Stubs\TestApplication';

    public function testType() {
        $container = $this->provideStylesheetContainer();
        $this->assertEquals('css', $container->getType());
    }

    public function testPrintingAssets() {
        $container = $this->provideStylesheetContainer();

        // first add few assets
        $container->addAsset('::app.css', 'app');
        $container->addAsset('SplotAssetsTestModule::overwrite.css', 'lib');
        $container->addAsset('SplotAssetsTestModule::test.css');
        $container->addAsset('@/css/web.css', 'app');

        $this->assertEquals('<link rel="stylesheet" href="/custom/splotassetstest/css/overwrite.css" data-package="lib">
<link rel="stylesheet" href="/app/css/app.css" data-package="app">
<link rel="stylesheet" href="/css/web.css" data-package="app">
<link rel="stylesheet" href="/assets/splotassetstest/css/test.css" data-package="page">
', $container->printAssets());
    }

    protected function provideMocks() {
        return array(
            'finder' => new AssetsFinder(
                $this->_application,
                $this->_application->getContainer()->get('resource_finder'),
                $this->_application->getContainer()->getParameter('web_dir'),
                'app',
                'assets',
                'custom'
            ),
            'type' => null
        );
    }

    protected function provideStylesheetContainer(array $mocks = array()) {
        if (empty($mocks)) {
            $mocks = $this->provideMocks();
        }

        return new StylesheetContainer($mocks['finder'], $mocks['type']);
    }

}
