<?php
/** 
 *------------------------------------------------------------------------------
 * @package       CANVAS Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 ThemezArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       ThemezArt
 *                & t3-framework.org as base version
 * @Google group: https://groups.google.com/forum/#!forum/canvasfw
 * @Link:         http://themezart.com/canvas-framework 
 *------------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * CANVASJ class
 * Make CANVAS compatible with both Joomla 3.x & 2.5
 * @package		CANVAS
 */

class CANVASJ {

	/**
	 * The method is available from Joomla 3.1.2 in class JHtml. Changing call JHtml::tooltipText to CANVASJ::tooltipText to make it work on Joomla 2.5
   *
   * Converts a double colon seperated string or 2 separate strings to a string ready for bootstrap tooltips
	 *
	 * @param   string  $title      The title of the tooltip (or combined '::' separated string).
	 * @param   string  $content    The content to tooltip.
	 * @param   int     $translate  If true will pass texts through JText.
	 * @param   int     $escape     If true will pass texts through htmlspecialchars.
	 *
	 * @return  string  The tooltip string
	 *
	 * @since   3.1.2
	 */
	public static function tooltipText($title = '', $content = '', $translate = 1, $escape = 1)
	{
		// Return empty in no title or content is given.
		if ($title == '' && $content == '')
		{
			return '';
		}

		// Split title into title and content if the title contains '::' (old Mootools format).
		if ($content == '' && !(strpos($title, '::') === false))
		{
			list($title, $content) = explode('::', $title, 2);
		}

		// Pass texts through the JText.
		if ($translate)
		{
			$title = JText::_($title);
			$content = JText::_($content);
		}

		// Escape the texts.
		if ($escape)
		{
			$title = str_replace('"', '&quot;', $title);
			$content = str_replace('"', '&quot;', $content);
		}

		// Return only the content if no title is given.
		if ($title == '')
		{
			return $content;
		}

		// Return only the title if title and text are the same.
		if ($title == $content)
		{
			return '<strong>' . $title . '</strong>';
		}

		// Return the formated sting combining the title and  content.
		if ($content != '')
		{
			return '<strong>' . $title . '</strong><br />' . $content;
		}

		// Return only the title.
		return $title;
	}  
}
