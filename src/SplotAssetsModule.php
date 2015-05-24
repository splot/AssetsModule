<?php
/**
 * Splot Framework module that takes care of management of assets,
 * including JavaScript and Stylesheets (CSS).
 * 
 * @package SplotAssetsModule
 * @author Michał Pałys-Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule;

use Splot\Framework\Modules\AbstractModule;

class SplotAssetsModule extends AbstractModule
{

    protected $commandNamespace = 'assets';

    public function configure() {
        parent::configure();

        $config = $this->getConfig();

        $this->container->setParameter('assets.application_dir', $config->get('application_dir'));
        $this->container->setParameter('assets.modules_dir', $config->get('modules_dir'));
        $this->container->setParameter('assets.overwritten_dir', $config->get('overwritten_dir'));

        $this->container->setParameter('assets.minifier.css_enable', $config->get('minifier.css_enable'));
        $this->container->setParameter('assets.minifier.css_dir', $config->get('minifier.css_dir'));
        $this->container->setParameter('assets.minifier.css_minifier.name', '@'. ltrim($config->get('minifier.css_minifier'), '@'));
        $this->container->setParameter('assets.minifier.css_minifier_worker.name', '@'. ltrim($config->get('minifier.css_minifier_worker'), '@'));
        
        $this->container->setParameter('assets.minifier.js_enable', $config->get('minifier.js_enable'));
        $this->container->setParameter('assets.minifier.js_dir', $config->get('minifier.js_dir'));
        $this->container->setParameter('assets.minifier.js_minifier.name', '@'. ltrim($config->get('minifier.js_minifier'), '@'));
        $this->container->setParameter('assets.minifier.js_minifier_worker.name', '@'. ltrim($config->get('minifier.js_minifier_worker'), '@'));

        $this->container->setParameter('assets.minifier.uglifyjs2_bin', $config->get('minifier.uglifyjs2.bin'));
        $this->container->setParameter('assets.minifier.uglifyjs2_node_bin', $config->get('minifier.uglifyjs2.node_bin'));
    }

}