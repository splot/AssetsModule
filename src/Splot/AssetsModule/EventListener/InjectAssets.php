<?php
/**
 * Assets injector to the final body output.
 * 
 * @package SplotAssetsModule
 * @subpackage EventListener
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\AssetsModule\EventListener;

use Splot\Framework\EventManager\AbstractEvent;
use Splot\Framework\Events\WillSendResponse;

use Splot\AssetsModule\Assets\AssetsContainer\JavaScriptContainer;
use Splot\AssetsModule\Assets\AssetsContainer\StylesheetContainer;

class InjectAssets extends AbstractEvent
{

	/**
	 * JavaScript container service.
	 * 
	 * @var JavaScriptContainer
	 */
	protected $_javascripts;

	/**
	 * Stylesheets container service.
	 * 
	 * @var StylesheetContainer
	 */
	protected $_stylesheets;

	/**
	 * Constructor.
	 * 
	 * @param JavaScriptContainer $javascripts JavaScript container service.
	 * @param StylesheetContainer $stylesheets Stylesheets container service.
	 */
	public function __construct(JavaScriptContainer $javascripts, StylesheetContainer $stylesheets) {
		$this->_javascripts = $javascripts;
		$this->_stylesheets = $stylesheets;
	}

	/**
	 * Injects assets on WillSendResponse event.
	 * 
	 * Injects the javascripts and stylesheets in place of their placeholders defined in templates.
	 * 
	 * @param WillSendResponse $event Event that triggers this method.
	 */
	public function injectAssetsOnResponse(WillSendResponse $event) {
		$response = $event->getResponse();

		$response->alterPart($this->_javascripts->getPlaceholder(), $this->_javascripts->printAssets());
		$response->alterPart($this->_stylesheets->getPlaceholder(), $this->_stylesheets->printAssets());
	}

}