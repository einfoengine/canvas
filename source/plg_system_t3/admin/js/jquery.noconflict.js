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
 
if(typeof jQuery != 'undefined'){
	window._jQuery = jQuery.noConflict(true);
	if(!window.jQuery){
		window.jQuery = window._jQuery;
		window._jQuery = null;
	}
}