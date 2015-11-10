<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocument Megamenu renderer
 */
class JDocumentRendererCANVASBootstrap extends JDocumentRenderer
{
	/**
	 * Render megamenu block
	 *
	 * @param   string  $position  The position of the modules to render
	 * @param   array   $params    Associative array of values
	 * @param   string  $content   Module content
	 *
	 * @return  string  The output of the script
	 *
	 * @since   11.1
	 */
	public function render($info = null, $params = array(), $content = null)
	{
		CANVAS::import('menu/canvasbootstrap');

		// import the renderer
		$canvasapp    = CANVAS::getApp();
		$menutype = empty($params['menutype']) ? $canvasapp->getParam('mm_type', 'mainmenu') : $params['menutype'];

		JDispatcher::getInstance()->trigger('onCANVASBSMenu', array(&$menutype));
		$menu = new CANVASBootstrap($menutype);
		
		return $menu->render(true);
	}
}
