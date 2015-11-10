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
 
$cls = array('canvas-admin-layout-pos', 'block-' . $vars['name']);
$attr = '';

if(isset($vars['data-original'])){
	$attr = ' data-original="'. $vars['data-original'] . '"';
} else {
	$cls[] = 'canvas-admin-layout-uneditable'; 
}
?>
<div class="<?php echo implode(' ', $cls) ?>"<?php echo $attr ?>>
	<h3><?php echo $vars['name'] ?></h3>
</div>