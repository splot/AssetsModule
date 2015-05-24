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

use Splot\AssetsModule\Assets\Asset;
use Splot\AssetsModule\Assets\MinifierInterface;
use Splot\AssetsModule\Minifier\AssetMinifyJob;

use Splot\WorkQueueModule\WorkQueue\WorkQueue;

class WorkerMinifier implements MinifierInterface
{

    /**
     * Splot Work Queue.
     * 
     * @var WorkQueue
     */
    protected $workQueue;

    /**
     * The actual minifier.
     * 
     * @var MinifierInterface
     */
    protected $minifier;

    /**
     * Constructor.
     * 
     * @param WorkQueue         $workQueue Splot Work Queue.
     * @param MinifierInterface $minifier  The actual minifier.
     */
    public function __construct(WorkQueue $workQueue, MinifierInterface $minifier) {
        $this->workQueue = $workQueue;
        $this->minifier = $minifier;
    }

    /**
     * Triggers a job to minify the given asset.
     * 
     * @param  Asset  $asset Asset to be minified.
     */
    public function minifyAsset(Asset $asset) {
        $this->workQueue->addJob(AssetMinifyJob::getName(), array(
            'minifier' => $this->minifier,
            'asset' => $asset
        ));
    }

}