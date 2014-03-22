<?php
namespace Splot\AssetsModule\Tests\Assets\Stubs;

use Splot\Framework\Testing\Stubs\TestApplication as Base_TestApplication;

use Splot\AssetsModule\Tests\Assets\Stubs\AssetsTestModule\SplotAssetsTestModule;

class TestApplication extends Base_TestApplication
{

    protected $name = 'AssetsTestApplication';
    protected $version = 'test';

    public function loadParameters() {
        $basePath = rtrim(realpath(dirname(__FILE__))) .'/';
        return array(
            'application_dir' => $basePath .'app/',
            'web_dir' => $basePath .'web/'
        );
    }

    public function loadModules() {
        return array(
            new SplotAssetsTestModule()
        );
    }

}