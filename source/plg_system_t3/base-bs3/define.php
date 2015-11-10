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


define('CANVAS', 'base-bs3');
define('CANVAS_URL',  CANVAS_ADMIN_URL  . '/' . CANVAS);
define('CANVAS_PATH', CANVAS_ADMIN_PATH . '/' . CANVAS);
define('CANVAS_REL',  CANVAS_ADMIN_REL  . '/' . CANVAS);

define('CANVAS_BASE_MAX_GRID',            12);
define('CANVAS_BASE_WIDTH_PREFIX',        'col-md-');
define('CANVAS_BASE_NONRSP_WIDTH_PREFIX', 'col-xs-');
define('CANVAS_BASE_WIDTH_PATTERN',       'col-{device}-{width}');
define('CANVAS_BASE_WIDTH_REGEX',         '/(\s*)col-(lg|md|sm|xs)-(\d+)(\s*)/');
define('CANVAS_BASE_HIDDEN_PATTERN',      'hidden');
define('CANVAS_BASE_FIRST_PATTERN',       '');
define('CANVAS_BASE_ROW_FLUID_PREFIX',    'row');
define('CANVAS_BASE_RSP_IN_CLASS',        true);
define('CANVAS_BASE_DEFAULT_DEVICE',      'md');
define('CANVAS_BASE_DEVICES',             json_encode(array('lg', 'md', 'sm', 'xs')));
define('CANVAS_BASE_DV_MAXCOL',           json_encode(array('lg' => 6, 'md' => 6, 'sm' => 4, 'xs' => 2)));
define('CANVAS_BASE_DV_MINWIDTH',         json_encode(array('lg' => 2, 'md' => 2, 'sm' => 3, 'xs' => 6)));
define('CANVAS_BASE_DV_UNITSPAN',         json_encode(array('lg' => 1, 'md' => 1, 'sm' => 1, 'xs' => 1)));
define('CANVAS_BASE_DV_PREFIX',           json_encode(array('col-md-', 'col-lg-', 'col-sm-', 'col-xs-')));	/* priority order */
define('CANVAS_BASE_LESS_COMPILER',      'less');
