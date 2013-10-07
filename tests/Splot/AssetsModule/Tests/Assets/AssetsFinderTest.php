<?php
namespace Splot\AssetsModule\Tests\Assets;

use Splot\Framework\Testing\TestCase;

use Splot\AssetsModule\Assets\AssetsFinder;

use Splot\AssetsModule\Tests\Assets\Stubs\AssetsTestModule\SplotAssetsTestModule;

class AssetsFinderTest extends TestCase
{

    public function setUp() {
        parent::setUp();
        $this->_application->bootModule(new SplotAssetsTestModule());
    }

    public function testGetAssetUrl() {
        $finder = new AssetsFinder($this->_application, $this->_application->getContainer()->get('resource_finder'),
            'app', 'assets');

        $assets = array(
            'http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js',
            'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js',
            '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js' => '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js',
            'SplotAssetsTestModule::adipiscit.js' => '/assets/splotassetstest/js/adipiscit.js',
            'SplotAssetsTestModule::Lorem/ipsum.js' => '/assets/splotassetstest/js/Lorem/ipsum.js',
            'SplotAssetsTestModule::Lorem/Dolor/sit.js' => '/assets/splotassetstest/js/Lorem/Dolor/sit.js',
            'SplotAssetsTestModule:Lorem:lipsum.js' => '/assets/splotassetstest/Lorem/js/lipsum.js',
            'SplotAssetsTestModule:Lorem:Dolor/sit.js' => '/assets/splotassetstest/Lorem/js/Dolor/sit.js',
            'SplotAssetsTestModule::Lorem/Dolor/sit.js' => '/assets/splotassetstest/js/Lorem/Dolor/sit.js',
            //'::index.js' => '/app/js/index.js' // need to set application dir in the tested application
        );

        foreach($assets as $search => $result) {
            $this->assertEquals($result, $finder->getAssetUrl($search, 'js'));
        }

        // call one once more to check memory cache
        $this->assertEquals('/assets/splotassetstest/js/Lorem/Dolor/sit.js', $finder->getAssetUrl('SplotAssetsTestModule::Lorem/Dolor/sit.js', 'js'));
    }

    /**
     * @expectedException \Splot\Framework\Resources\Exceptions\ResourceNotFoundException
     */
    public function testGetAssetUrlInvalid() {
         $finder = new AssetsFinder($this->_application, $this->_application->getContainer()->get('resource_finder'),
            '', '');
         $finder->getAssetUrl('SplotAssetsTestModule::nonexistent.js', 'js');
    }

    /**
     * @expectedException \MD\Foundation\Exceptions\InvalidArgumentException
     */
    public function testGetAssetPathInvalid() {
        $finder = new AssetsFinder($this->_application, $this->_application->getContainer()->get('resource_finder'),
            '', '');
        $finder->getAssetPath('random');
    }

    public function testGetAssetPath() {
        $finder = new AssetsFinder($this->_application, $this->_application->getContainer()->get('resource_finder'),
            '', '');

        $basePath = rtrim(__DIR__, DS) . DS . 'Stubs/AssetsTestModule/Resources/public/';
        $assets = array(
            'SplotAssetsTestModule::adipiscit.js' => $basePath .'js/adipiscit.js',
            'SplotAssetsTestModule::Lorem/ipsum.js' => $basePath .'js/Lorem/ipsum.js',
            'SplotAssetsTestModule::Lorem/Dolor/sit.js' => $basePath .'js/Lorem/Dolor/sit.js',
            'SplotAssetsTestModule:Lorem:lipsum.js' => $basePath .'Lorem/js/lipsum.js',
            'SplotAssetsTestModule:Lorem:Dolor/sit.js' => $basePath .'Lorem/js/Dolor/sit.js'
        );

        foreach($assets as $search => $result) {
            $this->assertEquals($result, $finder->getAssetPath($search, 'js'));
        }
    }

}
