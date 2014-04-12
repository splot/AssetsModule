<?php
/**
 * Proxy minifier that delegates the minification process to a background worker.
 * 
 * @package SplotAssetsModule
 * @subpackage Minifier
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Minifier;

use Splot\WorkQueueModule\WorkQueue\AbstractJob;

use Splot\AssetsModule\Assets\Asset;
use Splot\AssetsModule\Assets\MinifierInterface;

class AssetMinifyJob extends AbstractJob
{

    public function execute(Asset $asset, MinifierInterface $minifier) {
        $minifier->minifyAsset($asset);
    }

}