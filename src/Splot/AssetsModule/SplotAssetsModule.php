<?php
/**
 * Splot Framework module that takes care of management of assets, including JavaScript and Stylesheets (CSS).
 * 
 * @package SplotAssetsModule
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule;

use Splot\Framework\Modules\AbstractModule;
use Splot\Framework\Events\WillSendResponse;

use Splot\AssetsModule\Assets\AssetsFinder;
use Splot\AssetsModule\Assets\AssetsContainer\JavaScriptContainer;
use Splot\AssetsModule\Assets\AssetsContainer\StylesheetContainer;
use Splot\AssetsModule\EventListener\InjectAssets;
use Splot\AssetsModule\Twig\Extension\AssetsExtension;

class SplotAssetsModule extends AbstractModule
{

    protected $commandNamespace = 'assets';

    public function configure() {
        $config = $this->getConfig();
        $container = $this->container;

        /*
         * SERVICES
         */
        // register assets finder service
        $container->set('assets_finder', function($c) use ($config) {
            return new AssetsFinder(
                $c->get('application'),
                $c->get('resource_finder'),
                $c->getParameter('web_dir'),
                $config->get('application_dir'),
                $config->get('modules_dir'),
                $config->get('overwritten_dir')
            );
        });
        $container->set('assets.finder', function($c) {
            return $c->get('assets_finder');
        });

        // register assets containers services
        $container->set('javascripts', function($c) {
            return new JavaScriptContainer($c->get('assets_finder'));
        });
        $container->set('assets.javascripts', function($c) {
            return $c->get('javascripts');
        });

        $container->set('stylesheets', function($c) {
            return new StylesheetContainer($c->get('assets_finder'));
        });
        $container->set('assets.stylesheets', function($c) {
            return $c->get('stylesheets');
        });

        /*
         * EVENT LISTENERS
         */
        $container->get('event_manager')->subscribe(WillSendResponse::getName(), function(WillSendResponse $event) use ($container) {
            $injector = new InjectAssets($container->get('javascripts'), $container->get('stylesheets'));
            $injector->injectAssetsOnResponse($event);
        }, -9999);

    }

    public function run() {
        if ($this->container->has('twig')) {
            $extension = new AssetsExtension(
                $this->container->get('assets.finder'),
                $this->container->get('assets.javascripts'),
                $this->container->get('assets.stylesheets')
            );
            $this->container->get('twig')->addExtension($extension);
        }
    }

}