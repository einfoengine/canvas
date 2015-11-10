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

// No direct access
defined('_JEXEC') or die;

/**
 * Radio List Element
 *
 * @package  CANVAS.Core.Element
 */
class JFormFieldCANVASPositions extends JFormField
{
	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	protected $type = 'CANVASPositions';

	/**
	 * Check and load assets file if needed
	 */
	function loadAsset(){
		if (!defined ('_CANVAS_DEPEND_ASSET_')) {
			define ('_CANVAS_DEPEND_ASSET_', 1);
			
			if(!defined('CANVAS')){
				$canvasurl = str_replace(DIRECTORY_SEPARATOR, '/', JURI::base(true) . '/' . substr(dirname(__FILE__), strlen(JPATH_SITE)));
				$canvasurl = str_replace('/administrator/', '/', $uri);
				$canvasurl = str_replace('//', '/', $uri);
			} else {
				$canvasurl = CANVAS_ADMIN_URL;
			}

			$jdoc = JFactory::getDocument();

			if(!defined('CANVAS_TEMPLATE')){
				JFactory::getLanguage()->load(CANVAS_PLUGIN, JPATH_ADMINISTRATOR);

				if(version_compare(JVERSION, '3.0', 'ge')){
					JHtml::_('jquery.framework');
				} else {
					$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/js/jquery-1.8.3.min.js');
					$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/js/jquery.noconflict.js');
				}

				$jdoc->addStyleSheet(CANVAS_ADMIN_URL . '/includes/depend/css/depend.css');
				$jdoc->addScript(CANVAS_ADMIN_URL . '/includes/depend/js/depend.js');
			}

			JFactory::getDocument()->addScriptDeclaration ( '
				jQuery.extend(CANVASDepend, {
					adminurl: \'' . JFactory::getURI()->toString() . '\',
					rooturl: \'' . JURI::root() . '\'
				});
			');
		}
	}
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	function getInput()
	{
		$this->loadAsset();

		CANVAS::import('admin/layout');
		
		return $this->getPositions();
	}
	
	function getPositions()
	{
		$path     = JPATH_SITE;
		$lang     = JFactory::getLanguage();
		$clientId = 0;
		$state    = 1;
		
		$templates      = array_keys(CANVASAdminLayout::getTemplates($clientId, $state));
		$templateGroups = array();
		
		// Add positions from templates
		foreach ($templates as $template) {
			$options = array();
			
			$positions = CANVASAdminLayout::getTplPositions($clientId, $template);
			if (is_array($positions))
				foreach ($positions as $position) {
					$text      = CANVASAdminLayout::getTranslatedModulePosition($clientId, $template, $position) . ' [' . $position . ']';
					$options[] = CANVASAdminLayout::createOption($position, $text);
				}
			
			$templateGroups[$template] = CANVASAdminLayout::createOptionGroup(ucfirst($template), $options);
		}
		
		// Add custom position to options
		$customGroupText                  = JText::_('CANVAS_LAYOUT_CUSTOM_POSITION');
		$customPositions                  = CANVASAdminLayout::getDbPositions($clientId);
		$templateGroups[$customGroupText] = CANVASAdminLayout::createOptionGroup($customGroupText, $customPositions);


		$multiple = $this->toBoolean((string) $this->element['multiple']);
		$disabled = $this->toBoolean((string) $this->element['disabled']);
		
		
		return JHtml::_('select.groupedlist', $templateGroups, $this->name, array(
			'list.attr' => ($multiple ? ' multiple="multiple" size="10"' : '') . ($disabled ? 'disabled="disabled"' : '')
		));
	}


	/**
	 * Helper function, check the field attribute and return boolean value
	 *
	 * @return  boolean the check result
	 */
	function toBoolean($attr){
		return !in_array($attr, array('false', '', '0', 'no', 'off'));
	}
}