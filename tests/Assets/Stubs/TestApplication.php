<?php
namespace Splot\AssetsModule\Tests\Assets\Stubs;

use Splot\Framework\Testing\Stubs\TestApplication as Base_TestApplication;

use Splot\AssetsModule\Tests\Assets\Stubs\AssetsTestModule\SplotAssetsTestModule;

class TestApplication extends Base_TestApplication
{

    protected $name = 'AssetsTestApplication';
    protected $version = 'test';

    public function loadParameters($env, $debug) {
        return array(
            'application_dir' => __DIR__ .'/app',
            'web_dir' => __DIR__ .'/web'
        );
    }

    public function loadModules($env, $debug) {
        return array(
            new SplotAssetsTestModule()
        );
    }

}