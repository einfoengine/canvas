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
class JDocumentRendererMegamenuRender extends JDocumentRenderer
{
	/**
	 * Render megamenu block, then push the output into megamenu renderer to display
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
		CANVAS::import('menu/megamenu');

		$canvasapp = CANVAS::getApp();

		//we will check from params
		$menutype      = empty($params['menutype']) ? (empty($params['name']) ? $canvasapp->getParam('mm_type', 'mainmenu') : $params['name']) : $params['menutype'];
		$currentconfig = json_decode($canvasapp->getParam('mm_config', ''), true);

		//force to array
		if (!is_array($currentconfig)) {
			$currentconfig = (array)$currentconfig;
		}

		//get user access levels
		$viewLevels = JFactory::getUser()->getAuthorisedViewLevels();
		$mmkey = $menutype;
		$mmconfig = array();
		if (!empty($currentconfig)) {
			//find best fit configuration based on view level
			$vlevels = array_merge($viewLevels);
			if (is_array($vlevels) && in_array(3, $vlevels)) { //we assume, if a user is special, they should be registered also
				$vlevels[] = 2;
			}
			$vlevels = array_unique($vlevels);
			rsort($vlevels);
			if (!is_array($vlevels)) $vlevels = array();
			$vlevels[] = ''; // extend a blank, default key

			// check if available configuration for language override
			$langcode = JFactory::getDocument()->language;
			$shortlangcode = substr($langcode, 0, 2);
			$types = array($menutype . '-' . $langcode, $menutype . '-' . $shortlangcode, $menutype);

			foreach ($types as $type) {
				foreach ($vlevels as $vlevel) {
					$key  = $type . ($vlevel !== '' ? '-' . $vlevel : '');
					if(isset($currentconfig[$key])) {
						$mmkey    = $key;
						$menutype = $type;
						break 2;
					} else if (isset($currentconfig[$type])){
						$mmkey    = $menutype = $type;
						break 2;
					}
				}
			}
			if (isset($currentconfig[$mmkey])) {
				$mmconfig = $currentconfig[$mmkey];
				if(!is_array($mmconfig)){
					$mmconfig = array();
				}
			}
		}

		JDispatcher::getInstance()->trigger('onCANVASMegamenu', array(&$menutype, &$mmconfig, &$viewLevels));

		$mmconfig['access'] = $viewLevels;
		$menu = new CANVASMenuMegamenu ($menutype, $mmconfig, $canvasapp->_tpl->params);
		
		$canvasapp->setBuffer($menu->render(true), 'megamenu', empty($params['name']) ? null : $params['name'], null);
		return '';
	}
}
