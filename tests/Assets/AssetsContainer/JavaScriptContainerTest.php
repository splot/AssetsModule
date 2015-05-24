<?php
namespace Splot\AssetsModule\Tests\Assets\AssetsContainer;

use Splot\Framework\Testing\ApplicationTestCase;

use Splot\AssetsModule\Assets\AssetsContainer\JavaScriptContainer;
use Splot\AssetsModule\Assets\AssetsFinder;

/**
 * @coversDefaultClass \Splot\AssetsModule\Assets\AssetsContainer\JavaScriptContainer
 */
class JavaScriptContainerTest extends ApplicationTestCase
{

    public static $applicationClass = 'Splot\AssetsModule\Tests\Assets\Stubs\TestApplication';

    public function testType() {
        $container = $this->provideJavaScriptContainer();
        $this->assertEquals('js', $container->getType());
    }

    public function testPrintingAssets() {
        $container = $this->provideJavaScriptContainer();

        // first add few assets
        $container->addAsset('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', 'lib', 99999);
        $container->addAsset('/js/lib/jquery.min.js', 'page', -10);
        $container->addAsset('/js/map.js', 'custom', -10);
        $container->addAsset('@SplotAssetsTestModule::adipiscit.js');
        $container->addAsset('@SplotAssetsTestModule::lipsum.js', 'page', 9999);
        $container->addAsset('@SplotAssetsTestModule::overwritten.js', 'app');
        $container->addAsset('@::index.js', 'app', 80);
        $container->addAsset('/js/contact.js', 'custom');

        $this->assertEquals('<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" data-package="lib"></script>
<script type="text/javascript" src="/app/js/index.js" data-package="app"></script>
<script type="text/javascript" src="/custom/splotassetstest/js/overwritten.js" data-package="app"></script>
<script type="text/javascript" src="/assets/splotassetstest/js/lipsum.js" data-package="page"></script>
<script type="text/javascript" src="/assets/splotassetstest/js/adipiscit.js" data-package="page"></script>
<script type="text/javascript" src="/js/lib/jquery.min.js" data-package="page"></script>
<script type="text/javascript" src="/js/contact.js" data-package="custom"></script>
<script type="text/javascript" src="/js/map.js" data-package="custom"></script>
', $container->printAssets());
    }

    protected function provideMocks() {
        $mocks = array(
            'finder' => new AssetsFinder(
                $this->application,
                $this->application->getContainer()->get('resource_finder'),
                $this->application->getContainer()->getParameter('web_dir'),
                'app',
                'assets',
                'custom'
            ),
            'type' => null
        );
        $mocks['minifier'] = $this->getMockBuilder('Splot\AssetsModule\Assets\AssetsMinifier')
            ->disableOriginalConstructor()
            ->getMock();
        $mocks['minify'] = false;
        return $mocks;
    }

    protected function provideJavaScriptContainer(array $mocks = array()) {
        if (empty($mocks)) {
            $mocks = $this->provideMocks();
        }

        return new JavaScriptContainer($mocks['finder'], $mocks['minifier'], $mocks['minify'], $mocks['type']);
    }

}
