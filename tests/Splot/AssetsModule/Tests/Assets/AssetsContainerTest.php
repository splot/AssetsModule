<?php
namespace Splot\AssetsModule\Tests\Assets;

use MD\Foundation\Utils\ArrayUtils;

use Splot\Framework\Testing\TestCase;

use Splot\AssetsModule\Assets\AssetsContainer;
use Splot\AssetsModule\Assets\AssetsFinder;

use Splot\AssetsModule\Tests\Assets\Stubs\AssetsTestModule\SplotAssetsTestModule;

/**
 * @coversDefaultClass \Splot\AssetsModule\Assets\AssetsContainer
 */
class AssetsContainerTest extends TestCase
{

    public function setUp() {
        $basePath = rtrim(__DIR__, '/') .'/' . 'Stubs/';

        $this->_options = array(
            'applicationDir' => $basePath .'app/',
            'webDir' => $basePath .'web/'
        );
        parent::setUp();
        $this->_application->bootModule(new SplotAssetsTestModule());
    }

    /**
     * @covers ::__construct()
     * @covers ::getType()
     */
    public function testConstructingWithType() {
        $mocks = $this->provideMocks();
        $mocks['type'] = 'js';
        $container = $this->provideAssetsContainer($mocks);

        $this->assertEquals('js', $container->getType());
    }

    /**
     * @dataProvider provideSingleAssets
     * @covers ::addAsset
     * @covers ::getAssets
     */
    public function testAddingSingleAssets($asset, array $expected) {
        $mocks = $this->provideMocks();
        $mocks['type'] = 'js';
        $container = $this->provideAssetsContainer($mocks);

        $result = call_user_func_array(array($container, 'addAsset'), $asset);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertCount(1, $container->getAssets());

        $this->assertEquals($expected, $result[0]);
    }

    /**
     * @dataProvider provideGlobAssets
     * @covers ::addAsset
     * @covers ::getAssets
     */
    public function testAddingGlobAssets($pattern, $count) {
        $mocks = $this->provideMocks();
        $mocks['type'] = 'js';
        $container = $this->provideAssetsContainer($mocks);

        $result = $container->addAsset($pattern);
        $this->assertCount($count, $result);
        $this->assertCount($count, $container->getAssets());
    }

    public function testAddingGlobAssetsStructure() {
        $mocks = $this->provideMocks();
        $mocks['type'] = 'js';
        $container = $this->provideAssetsContainer($mocks);

        $result = $container->addAsset('@/js/*.js');
        $asset = $result[0];
        $this->assertInternalType('string', $asset['path']);
        $this->assertInternalType('string', $asset['url']);
    }

    /**
     * @covers ::getSortedAssets()
     * @covers ::sortAssets()
     */
    public function testSortingAssets() {
        $mocks = $this->provideMocks();
        $mocks['type'] = 'js';
        $container = $this->provideAssetsContainer($mocks);

        // first add few assets
        $container->addAsset('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', 'lib', 99999);
        $container->addAsset('@/js/lib/jquery.min.js', 'page', -10);
        $container->addAsset('@/js/map.js', 'custom', -10);
        $container->addAsset('SplotAssetsTestModule::adipiscit.js');
        $container->addAsset('SplotAssetsTestModule::lipsum.js', 'page', 9999);
        $container->addAsset('SplotAssetsTestModule::overwritten.js', 'app');
        $container->addAsset('::index.js', 'app', 80);
        $container->addAsset('@/js/contact.js', 'custom');

        // then get the order
        $order = $container->getSortedAssets();

        // verify the order
        $this->assertEquals(array('lib', 'app', 'page', 'custom'), array_keys($order));

        $parsedOrder = array();
        foreach($order as $package => $assets) {
            $parsedOrder[$package] = ArrayUtils::pluck($assets, 'resource');
        }

        $this->assertEquals(array(
            'lib' => array('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'),
            'app' => array('::index.js', 'SplotAssetsTestModule::overwritten.js'),
            'page' => array('SplotAssetsTestModule::lipsum.js', 'SplotAssetsTestModule::adipiscit.js', '@/js/lib/jquery.min.js'),
            'custom' => array('@/js/contact.js', '@/js/map.js')
        ), $parsedOrder);

        // get the order again to make sure it's the same (and cover memory cache)
        $this->assertEquals($order, $container->getSortedAssets());
        
        // add few more assets
        $container->addAsset('@/js/*.js', 'page');
        $container->addAsset('SplotAssetsTestModule::**/*.js', 'app');

        // get new order and make sure it's different than the original one (to test that adding new assets resets the ordering)
        $newOrder = $container->getSortedAssets();
        $this->assertNotEquals($order, $newOrder);

        // verify new order
        $this->assertEquals(array('lib', 'app', 'page', 'custom'), array_keys($newOrder));

        $parsedNewOrder = array();
        foreach($newOrder as $package => $assets) {
            $parsedNewOrder[$package] = ArrayUtils::pluck($assets, 'resource');
        }

        $this->assertEquals(array(
            'lib' => array('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'),
            'app' => array('::index.js', 'SplotAssetsTestModule::overwritten.js', 'SplotAssetsTestModule::Lorem/ipsum.js', 'SplotAssetsTestModule::Lorem/Dolor/sit.js'),
            'page' => array('SplotAssetsTestModule::lipsum.js', 'SplotAssetsTestModule::adipiscit.js', '@/js/contact.js', '@/js/index.js', '@/js/map.js', '@/js/lib/jquery.min.js'),
            'custom' => array('@/js/contact.js', '@/js/map.js')
        ), $parsedNewOrder);
    }

    public function testGettingTheSamePlaceholder() {
        $container = $this->provideAssetsContainer();

        $placeholder = $container->getPlaceholder();
        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals($placeholder, $container->getPlaceholder());
        }
    }

    public function testTwoContainersGettingDifferentPlaceholders() {
        $container1 = $this->provideAssetsContainer();
        $container2 = $this->provideAssetsContainer();

        $this->assertNotEquals($container1->getPlaceholder(), $container2->getPlaceholder());
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

    protected function provideAssetsContainer(array $mocks = array()) {
        if (empty($mocks)) {
            $mocks = $this->provideMocks();
        }

        return $this->getMock('Splot\AssetsModule\Assets\AssetsContainer', array('printAssets'), array($mocks['finder'], $mocks['type']));
    }

    public function provideSingleAssets() {
        $basePath = rtrim(__DIR__, '/') . '/' . 'Stubs/';
        $appPath = $basePath .'app/';
        $webPath = $basePath .'web/';
        $testModulePath = $basePath .'AssetsTestModule/Resources/public/';

        return array(
            array(
                array('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'),
                array(
                    'resource' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js',
                    'package' => 'page',
                    'priority' => 0,
                    'path' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js',
                    'url' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'
                ),
            ),
            array(
                array('SplotAssetsTestModule::adipiscit.js', 'app', 10),
                array(
                    'resource' => 'SplotAssetsTestModule::adipiscit.js',
                    'package' => 'app',
                    'priority' => 10,
                    'path' => $testModulePath .'js/adipiscit.js',
                    'url' => '/assets/splotassetstest/js/adipiscit.js'
                )
            ),
            array(
                array('SplotAssetsTestModule:Lorem:Dolor/sit.js', 'lib', -80),
                array(
                    'resource' => 'SplotAssetsTestModule:Lorem:Dolor/sit.js',
                    'package' => 'lib',
                    'priority' => -80,
                    'path' => $testModulePath .'Lorem/js/Dolor/sit.js',
                    'url' => '/assets/splotassetstest/Lorem/js/Dolor/sit.js'
                )
            ),
            array(
                array('::index.js', 'custom'),
                array(
                    'resource' => '::index.js',
                    'package' => 'custom',
                    'priority' => 0,
                    'path' => $appPath .'Resources/public/js/index.js',
                    'url' => '/app/js/index.js'
                )
            ),
            array(
                array('@/js/lib/jquery.min.js', 'vendor'),
                array(
                    'resource' => '@/js/lib/jquery.min.js',
                    'package' => 'vendor',
                    'priority' => 0,
                    'path' => $webPath .'js/lib/jquery.min.js',
                    'url' => '/js/lib/jquery.min.js'
                )
            )
        );
    }

    public function provideGlobAssets() {
        return array(
            array('::index.js', 1),
            array('@/js/*.js', 3),
            array('SplotAssetsTestModule::*.js', 5),
            array('SplotAssetsTestModule::**/*.js', 2)
        );
    }

}
