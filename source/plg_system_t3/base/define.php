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

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package      CANVAS
 * @description  This file should contains information of itself
 */


define('CANVAS', CANVAS_CORE_BASE);
define('CANVAS_URL', CANVAS_CORE_BASE_URL);
define('CANVAS_PATH', CANVAS_CORE_BASE_PATH);
define('CANVAS_REL', CANVAS_CORE_BASE_REL);

define('CANVAS_BASE_MAX_GRID',            12);
define('CANVAS_BASE_WIDTH_PREFIX',        'span');
define('CANVAS_BASE_NONRSP_WIDTH_PREFIX', 'span');
define('CANVAS_BASE_WIDTH_PATTERN',       'span{width}');
define('CANVAS_BASE_WIDTH_REGEX',         '/(\s*)span(\d+)(\s*)/');
define('CANVAS_BASE_HIDDEN_PATTERN',      'hidden');
define('CANVAS_BASE_FIRST_PATTERN',       'spanfirst');
define('CANVAS_BASE_RSP_IN_CLASS',        false);
define('CANVAS_BASE_ROW_FLUID_PREFIX',    'row-fluid');
define('CANVAS_BASE_DEFAULT_DEVICE',      'default');
define('CANVAS_BASE_DEVICES',             json_encode(array('default', 'wide', 'normal', 'xtablet', 'tablet', 'mobile')));
define('CANVAS_BASE_DV_MAXCOL',           json_encode(array('default' => 6, 'wide' => 6, 'normal' => 6, 'xtablet' => 4, 'tablet' => 3, 'mobile' => 2)));
define('CANVAS_BASE_DV_MINWIDTH',         json_encode(array('default' => 2, 'wide' => 2, 'normal' => 2, 'xtablet' => 3, 'tablet' => 4, 'mobile' => 6)));
define('CANVAS_BASE_DV_UNITSPAN',         json_encode(array('default' => 1, 'wide' => 1, 'normal' => 1, 'xtablet' => 1, 'tablet' => 1, 'mobile' => 6)));
define('CANVAS_BASE_DV_PREFIX',           json_encode(array('span')));
define('CANVAS_BASE_LESS_COMPILER',       'legacy.less');