<?php
namespace Splot\AssetsModule\Tests\Assets;

use Splot\Framework\Testing\TestCase;

use Splot\AssetsModule\Assets\AssetsFinder;

use Splot\AssetsModule\Tests\Assets\Stubs\AssetsTestModule\SplotAssetsTestModule;

class AssetsFinderTest extends TestCase
{

    public function setUp() {
        $basePath = rtrim(__DIR__, DS) . DS . 'Stubs/';

        $this->_options = array(
            'applicationDir' => $basePath .'app/',
            'webDir' => $basePath .'web/'
        );
        parent::setUp();
        $this->_application->bootModule(new SplotAssetsTestModule());
    }

    /**
     * @dataProvider provideAssetsWithUrls
     */
    public function testGetAssetUrl($asset, $resolved) {
        $finder = new AssetsFinder(
            $this->_application,
            $this->_application->getContainer()->get('resource_finder'),
            $this->_application->getContainer()->getParameter('web_dir'),
            'app',
            'assets',
            'custom'
        );

        $this->assertEquals($resolved, $finder->getAssetUrl($asset, 'js'));

        // call one once more to cover memory cache
        $this->assertEquals($resolved, $finder->getAssetUrl($asset, 'js'));
    }

    /**
     * @expectedException \Splot\Framework\Resources\Exceptions\ResourceNotFoundException
     */
    public function testGetAssetUrlInvalid() {
         $finder = new AssetsFinder(
            $this->_application,
            $this->_application->getContainer()->get('resource_finder'),
            $this->_application->getContainer()->getParameter('web_dir'),
            'app',
            'assets',
            'custom'
        );
         $finder->getAssetUrl('SplotAssetsTestModule::nonexistent.js', 'js');
    }

    /**
     * @expectedException \MD\Foundation\Exceptions\InvalidArgumentException
     */
    public function testGetAssetPathInvalid() {
        $finder = new AssetsFinder(
            $this->_application,
            $this->_application->getContainer()->get('resource_finder'),
            $this->_application->getContainer()->getParameter('web_dir'),
            'app',
            'assets',
            'custom'
        );
        $finder->getAssetPath('random');
    }

    /**
     * @expectedException \Splot\Framework\Resources\Exceptions\ResourceNotFoundException
     */
    public function testGetWebAssetPathInvalid() {
        $finder = new AssetsFinder(
            $this->_application,
            $this->_application->getContainer()->get('resource_finder'),
            $this->_application->getContainer()->getParameter('web_dir'),
            'app',
            'assets',
            'custom'
        );
        $path = $finder->getAssetPath('@/images/img.png');
    }

    /**
     * @dataProvider provideAssetsWithPaths
     */
    public function testGetAssetPath($asset, $path) {
        $finder = new AssetsFinder(
            $this->_application,
            $this->_application->getContainer()->get('resource_finder'),
            $this->_application->getContainer()->getParameter('web_dir'),
            'app',
            'assets',
            'custom'
        );

        $this->assertEquals($path, $finder->getAssetPath($asset, 'js'));

        // call one once more to cover memory cache
        $this->assertEquals($path, $finder->getAssetPath($asset, 'js'));
    }

    /**
     * @dataProvider provideAssetsForExpanding
     */
    public function testExpanding($asset, array $found) {
        $finder = new AssetsFinder(
            $this->_application,
            $this->_application->getContainer()->get('resource_finder'),
            $this->_application->getContainer()->getParameter('web_dir'),
            'app',
            'assets',
            'custom'
        );

        $this->assertEquals($found, $finder->expand($asset, 'js'));
    }

    public function provideAssetsWithUrls() {
        return array(
            array('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', 'http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'),
            array('https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', 'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'),
            array('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'),
            array('SplotAssetsTestModule::adipiscit.js', '/assets/splotassetstest/js/adipiscit.js'),
            array('SplotAssetsTestModule::Lorem/ipsum.js', '/assets/splotassetstest/js/Lorem/ipsum.js'),
            array('SplotAssetsTestModule::Lorem/Dolor/sit.js', '/assets/splotassetstest/js/Lorem/Dolor/sit.js'),
            array('SplotAssetsTestModule:Lorem:lipsum.js', '/assets/splotassetstest/Lorem/js/lipsum.js'),
            array('SplotAssetsTestModule:Lorem:Dolor/sit.js', '/assets/splotassetstest/Lorem/js/Dolor/sit.js'),
            array('SplotAssetsTestModule::Lorem/Dolor/sit.js', '/assets/splotassetstest/js/Lorem/Dolor/sit.js'),
            array('::index.js', '/app/js/index.js'), // need to set application dir in the tested application
            array('@/js/lib/jquery.min.js', '/js/lib/jquery.min.js'),
            array('@js/lib/jquery.min.js', '/js/lib/jquery.min.js')
        );
    }

    public function provideAssetsWithPaths() {
        $basePath = rtrim(__DIR__, DS) . DS . 'Stubs/';
        $appPath = $basePath .'app/';
        $webPath = $basePath .'web/';
        $testModulePath = $basePath .'AssetsTestModule/Resources/public/';

        return array(
            array('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', 'http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'),
            array('https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', 'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'),
            array('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'),
            array('SplotAssetsTestModule::adipiscit.js', $testModulePath .'js/adipiscit.js'),
            array('SplotAssetsTestModule::Lorem/ipsum.js', $testModulePath .'js/Lorem/ipsum.js'),
            array('SplotAssetsTestModule::Lorem/Dolor/sit.js', $testModulePath .'js/Lorem/Dolor/sit.js'),
            array('SplotAssetsTestModule:Lorem:lipsum.js', $testModulePath .'Lorem/js/lipsum.js'),
            array('SplotAssetsTestModule:Lorem:Dolor/sit.js', $testModulePath .'Lorem/js/Dolor/sit.js'),
            array('::index.js', $appPath .'Resources/public/js/index.js'),
            array('@/js/lib/jquery.min.js', $webPath .'js/lib/jquery.min.js'),
            array('@js/lib/jquery.min.js', $webPath .'js/lib/jquery.min.js')
        );
    }

    public function provideAssetsForExpanding() {
        return array(
            array('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', array(
                'http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'
            )),
            array('::index.js', array(
                '::index.js'
            )),
            array('SplotAssetsTestModule:Lorem:Dolor/sit.js', array(
                'SplotAssetsTestModule:Lorem:Dolor/sit.js'
            )),
            array('@/js/*.js', array(
                '@/js/contact.js',
                '@/js/index.js',
                '@/js/map.js'
            )),
            array('SplotAssetsTestModule::Lorem/*.js', array(
                'SplotAssetsTestModule::Lorem/ipsum.js'
            )),
            array('@/img/*.png', array()),
            array('SplotAssetsTestModule::*.js', array(
                'SplotAssetsTestModule::overwrite.js',
                'SplotAssetsTestModule::overwritten.js',
                'SplotAssetsTestModule::adipiscit.js',
                'SplotAssetsTestModule::lipsum.js',
                'SplotAssetsTestModule::lorem.js',
            )),

        );
    }

}
