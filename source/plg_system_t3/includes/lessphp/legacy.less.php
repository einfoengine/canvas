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

CANVAS::import('lessphp/lessc.inc');

/**
 * CANVASLessCompiler class compile less
 *
 * @package CANVAS
 */
class CANVASLessCompiler
{
	public static function compile ($source, $path, $todir, $importdirs) {
		// call Less to compile
		$parser = new lessc();
		$parser->setImportDir(array_keys($importdirs));
		$parser->setPreserveComments(true);
		$output = $parser->compile($source);
		// update url
		$arr    = preg_split(CANVASLess::$rsplitbegin . CANVASLess::$kfilepath . CANVASLess::$rsplitend, $output, -1, PREG_SPLIT_DELIM_CAPTURE);
		$output = '';
		$file   = $relpath = '';
		$isfile = false;

		foreach ($arr as $s) {
			if ($isfile) {
				$isfile  = false;
				$file    = $s;
				$relpath = CANVASLess::relativePath($todir, dirname($file));
				$output .= "\n#".CANVASLess::$kfilepath."{content: \"{$file}\";}\n";
			} else {
				$output .= ($file ? CANVASPath::updateUrl($s, $relpath) : $s) . "\n\n";
				$isfile = true;
			}
		}

		return $output;
	}
}
