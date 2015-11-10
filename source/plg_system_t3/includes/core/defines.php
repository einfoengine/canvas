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

define ('CANVAS_PLUGIN', 'plg_system_canvas');

//CANVAS base folder
define ('CANVAS_ADMIN', 'canvas');
define ('CANVAS_ADMIN_PATH', JPATH_ROOT . '/plugins/system/' . CANVAS_ADMIN);
define ('CANVAS_ADMIN_URL', JURI::root(true) . '/plugins/system/' . CANVAS_ADMIN);
define ('CANVAS_ADMIN_REL', 'plugins/system/' . CANVAS_ADMIN);

//CANVAS secondary base theme folder
define ('CANVAS_EX_BASE_PATH', JPATH_ROOT . '/media/canvas/themes');
define ('CANVAS_EX_BASE_URL', JURI::root(true) . '/media/canvas/themes');
define ('CANVAS_EX_BASE_REL', 'media/canvas/themes');

//CANVAS core base theme
define ('CANVAS_CORE_BASE', 'base');
define ('CANVAS_CORE_BASE_PATH', CANVAS_ADMIN_PATH . '/' . CANVAS_CORE_BASE);
define ('CANVAS_CORE_BASE_URL', CANVAS_ADMIN_URL . '/' . CANVAS_CORE_BASE);
define ('CANVAS_CORE_BASE_REL', CANVAS_ADMIN_REL . '/' . CANVAS_CORE_BASE);

// CANVAS User dir
define ('CANVAS_LOCAL_DIR', 'local');