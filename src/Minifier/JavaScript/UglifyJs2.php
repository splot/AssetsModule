<?php
/**
 * JavaScript minifier that uses UglifyJs2 node.js package for minification.
 * 
 * @package SplotAssetsModule
 * @subpackage Minifier
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Minifier\JavaScript;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Filter\UglifyJs2Filter;

use Splot\AssetsModule\Assets\Asset;
use Splot\AssetsModule\Assets\MinifierInterface;

class UglifyJs2 implements MinifierInterface
{

    /**
     * UglifyJs2 binary executable path.
     * 
     * @var string
     */
    protected $bin = '/usr/local/bin/uglifyjs';

    /**
     * Node.js binary executable path.
     * 
     * @var string
     */
    protected $nodeBin = null;

    /**
     * Constructor.
     * 
     * @param string $bin [optional] UglifyJs2 binary executable path. Default: '/usr/local/bin/uglifyjs'.
     * @param string $nodeBin [optional] Node.js binary executable path. Default: null.
     */
    public function __construct($bin = '/usr/local/bin/uglifyjs', $nodeBin = null) {
        $this->bin = $bin;
        $this->nodeBin = $nodeBin;
    }

    /**
     * Minifies the given JavaScript asset.
     * 
     * @param  Asset  $asset JavaScript asset to be minified.
     */
    public function minifyAsset(Asset $asset) {
        $asseticCollection = new AssetCollection(array(
            new FileAsset($asset->getPath())
        ), array(
            new UglifyJs2Filter($this->bin, $this->nodeBin)
        ));

        file_put_contents($asset->getPath(), $asseticCollection->dump());
    }

}