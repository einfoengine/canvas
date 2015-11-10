<?php
/** 
 *------------------------------------------------------------------------------
 * @package       CANVAS Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 ThemezArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       ThemezArt
 *                & t3-framework.org as base version
 * @Link:         http://themezart.com/canvas-framework 
 *------------------------------------------------------------------------------
 */

/**
 *
 * Admin helper module class
 * @author ThemezArt
 *
 */
class CANVASAjax {

	protected static $signature;
	protected static $modesef;

	public static function render() {
		// excute action by CANVAS
		$input = JFactory::getApplication()->input;

		if ($input->getCmd ('canvasajax')) {
			JFactory::getDocument()->getBuffer('canvasajax');
		}
	}

	public static function processAjaxRule () {
		$app = JFactory::getApplication();
		$router = $app->getRouter();
		
		if ($app->isSite()) {
			//self::$signature = 'canvasajax';
			//self::$modesef = ($router->getMode() == JROUTER_MODE_SEF) ? true : false;
			
			$router->attachBuildRule(array('CANVASAjax', 'buildRule'));
			//$router->attachParseRule(array('CANVASAjax', 'parseRule'));
		}
	}

	public static function buildRule (&$router, &$uri) {
		$uri->delVar('canvasajax');
	}

	public static function parseRule (&$router, &$uri) {

	}
}