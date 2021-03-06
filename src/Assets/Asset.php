<?php
/**
 * Asset reference class.
 * 
 * @package SplotAssetsModule
 * @subpackage Assets
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\Assets;

use Splot\AssetsModule\Assets\AssetsContainer;

class Asset
{

    /**
     * Asset resource name.
     * 
     * @var string
     */
    protected $resource;

    /**
     * Resolved path to the asset.
     * 
     * @var string
     */
    protected $path;

    /**
     * Resolved URL to the asset.
     * 
     * @var string
     */
    protected $url;

    /**
     * Container package name in which the asset is registered.
     * 
     * @var string
     */
    protected $package = AssetsContainer::DEFAULT_PACKAGE;

    /**
     * Asset priority.
     * 
     * @var integer
     */
    protected $priority = 0;

    /**
     * Should this asset be minified?
     * 
     * @var boolean
     */
    protected $minify = false;

    /**
     * Is this a remote asset (not stored in the local filesystem).
     *
     * This is checked automatically based on the resource name.
     * 
     * @var boolean
     */
    protected $remote = false;

    /**
     * Constructor.
     * 
     * @param string  $resource Asset resource name.
     * @param string  $path     Resolved path to the asset.
     * @param string  $url      Resolved URL to the asset.
     * @param integer  $package [optional] Container package name in which the asset is registered.
     *                          Default: AssetsContainer::DEFAULT_PACKAGE.
     * @param integer $priority [optional] Asset priority. Default: 0.
     */
    public function __construct($resource, $path, $url, $package = AssetsContainer::DEFAULT_PACKAGE, $priority = 0) {
        $this->resource = $resource;
        $this->path = $path;
        $this->url = $url;
        $this->package = $package;
        $this->priority = $priority;

        // determine if this is a remote asset or not
        $this->remote = stripos($resource, 'http://') === 0 
            || stripos($resource, 'https://') === 0
            || stripos($resource, '//') === 0;
    }

    /**
     * Returns the asset resource name.
     * 
     * @return string
     */
    public function getResource() {
        return $this->resource;
    }

    /**
     * Returns the resolved path to the asset.
     * 
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Returns the resolved URL to the asset.
     * 
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set a new URL for this asset.
     * 
     * @param string $url New URL for this asset.
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * Returns the container package in which the asset is registered.
     * 
     * @return string
     */
    public function getPackage() {
        return $this->package;
    }

    /**
     * Sets the container package name in which the asset is registered.
     * 
     * @param string $package Package name.
     */
    public function setPackage($package) {
        $this->package = $package;
    }

    /**
     * Returns the asset priority.
     * 
     * @return integer
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * Sets the asset priority.
     * 
     * @param integer $priority Asset priority.
     */
    public function setPriority($priority) {
        $this->priority = $priority;
    }

    /**
     * Returns should this asset be minified.
     * 
     * @return boolean
     */
    public function getMinify() {
        return $this->minify;
    }

    /**
     * Sets whether or not this asset should be minified.
     * 
     * @param boolean $minify Whether or not this asset should be minified.
     */
    public function setMinify($minify) {
        $this->minify = $minify;
    }

    /**
     * Returns whether this is a remote asset (not stored in the local filesystem).
     * 
     * @return boolean
     */
    public function getRemote() {
        return $this->remote;
    }

    /**
     * Returns whether this is a remote asset (not stored in the local filesystem).
     * 
     * @return boolean
     */
    public function isRemote() {
        return $this->getRemote();
    }

    /**
     * Converts the asset to string by returning it's URL.
     * 
     * @return string
     */
    public function __toString() {
        return $this->getUrl();
    }

}