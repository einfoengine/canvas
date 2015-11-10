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

// No direct access
defined('_JEXEC') or die();

CANVAS::import('lessphp/less/less');

/**
 * CANVASLessCompiler class compile less
 *
 * @package CANVAS
 */
class CANVASLessCompiler
{
	public static function compile ($source, $path, $todir, $importdirs) {
		$parser = new Less_Parser();
		$parser->SetImportDirs($importdirs);
		$parser->parse($source, CANVASLess::relativePath($todir, dirname($path)) . basename($path));
		$output = $parser->getCss();
		return $output;
	}
}
