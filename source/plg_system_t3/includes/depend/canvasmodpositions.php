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

JFormHelper::loadFieldClass('list');

/**
* get all module positions
*
* @package		Joomla.Administrator
* @subpackage	CANVAS.Core.Element
*/
class JFormFieldCANVASModPositions extends JFormFieldList
{
	/**
	* The form field type.
	*
	* @var		string
	* @since	1.6
	*/
	protected $type = 'CANVASModPositions';

	/**
	* Method to get the field input markup.
	*
	* @return	string	The field input markup.
	* @since	1.6
	*/
	/*
	protected function getInput()
	{

		echo $this->value;die;

		$db = JFactory::getDBO();
		$query = 'SELECT `position` FROM `#__modules` WHERE  `client_id`=0 AND ( `published` !=-2 AND `published` !=0 ) GROUP BY `position` ORDER BY `position` ASC';

		$db->setQuery($query);
		$dbpositions = (array) $db->loadAssocList();


		$template = $this->form->getValue('template');
		$templateXML = JPATH_SITE.'/templates/'.$template.'/templateDetails.xml';
		$template = simplexml_load_file( $templateXML );
		$options = array();

		foreach($dbpositions as $positions) $options[] = $positions['position'];

		foreach($template->positions[0] as $position)  $options[] =  (string) $position;

		$options = array_unique($options);

		$selectOption = array();
		sort($selectOption);

		foreach($options as $option) $selectOption[] = JHTML::_( 'select.option',$option,$option );

		return JHTML::_('select.genericlist', $selectOption, 'jform[params]['.$this->element['name'].']', 'class="'.$this->element['class'].'"' . ($this->element['multiple'] == 1 ? ' multiple="multiple" size="10" ' : '') . ($this->element['disabled'] ? ' disabled="disabled"' : ''), 'value', 'text', $this->value, 'jform_params_canvas_'.$this->element['name']);
	}
	*/
	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Get the field options.
		$db = JFactory::getDBO();
		$query = 'SELECT `position` FROM `#__modules` WHERE  `client_id`=0 AND ( `published` !=-2 AND `published` !=0 ) GROUP BY `position` ORDER BY `position` ASC';

		$db->setQuery($query);
		$dbpositions = (array) $db->loadAssocList();


		$jtemplate = $this->form->getValue('template');
		if(empty($jtemplate)){
			$app = JFactory::getApplication();
			$jtemplate = $app->getTemplate();
		}
		$templateXML = JPATH_SITE.'/templates/'.$jtemplate.'/templateDetails.xml';
		$template = simplexml_load_file( $templateXML );
		$options = array();

		foreach($dbpositions as $positions) $options[] = $positions['position'];

		foreach($template->positions[0] as $position)  $options[] =  (string) $position;

		$options = array_unique($options);
		
		$finaloptions = array();
		foreach ($options as $key=>$name){
			//the data to use MUST be in an array form for the extra html attributes...
			$finaloptions[$name] = $name; 
		}
		
		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true')
		{
			$html[] = JHtml::_('select.genericlist', $finaloptions, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
		}
		else
		// Create a regular list.
		{
			$html[] = JHtml::_('select.genericlist', $finaloptions, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}
}
