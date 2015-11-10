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
?>
<?php
	$style = 'CANVASXhtml';
	$name = $vars['name'];
	$poss = $vars['poss'];
	$spldata = $vars['spldata'];
	$default = $vars['default'];
	$rowcls = isset($vars['row-fluid']) && $vars['row-fluid'] ? CANVAS_BASE_ROW_FLUID_PREFIX : 'row';
?>
	<!-- SPOTLIGHT -->
	<div class="<?php echo $rowcls ?> canvas-spotlight canvas-<?php echo $name ?>" <?php echo $spldata ?>>
		<?php foreach ($poss as $i => $pos): ?>
		<div class="<?php echo CANVAS_BASE_WIDTH_PREFIX, $default[$i] ?>">
			<?php if ($this->countModules($pos)) : ?>
				<jdoc:include type="modules" name="<?php echo $pos ?>" data-original="" style="<?php echo $style ?>" />
				<?php else: ?>
				&nbsp;
			<?php endif ?>
		</div>
		<?php endforeach ?>
	</div>
	<!-- SPOTLIGHT -->