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
	$name      = $vars['name'];
	$splparams = $vars['splparams'];
	$datas     = $vars['datas'];
	$cols      = $vars['cols'];
	$rowcls    = isset($vars['row-fluid']) && $vars['row-fluid'] ? CANVAS_BASE_ROW_FLUID_PREFIX : 'row';
	$addcls    = isset($vars['class']) ? $vars['class'] : '';
	$style     = isset($vars['style']) && $vars['style'] ? $vars['style'] : 'CANVASXhtml';
	$tstyles   = explode(',', $style);

	if(count($tstyles) == 1){
		$styles = array_fill(0, $cols, $style);
	} else {

		$styles = array_fill(0, $cols, 'CANVASXhtml');
		foreach ($tstyles as $i => $stl) {
			if(trim($stl)){
				$styles[$i] = trim($stl);
			}
		}
	}
	?>
	<!-- SPOTLIGHT -->
	<div class="canvas-spotlight canvas-<?php echo $name, ' ', $addcls, ' ', $rowcls ?>">
		<?php
		foreach ($splparams as $i => $splparam):
			$param = (object)$splparam;
		?>
			<div class="<?php echo $datas[$i] ?> <?php //echo $splparam->default ?> <?php echo ($i == 0) ? 'item-first' : (($i == $cols - 1) ? 'item-last' : '') ?>">
				<?php
					if($this->checkWidget($param->position)){
						echo $this->renderWidget($param->position);
					}
				?>
				<?php if ($this->countModules($param->position)) : ?>
					<jdoc:include type="modules" name="<?php echo $param->position ?>" style="<?php echo $styles[$i] ?>"/>
				<?php else: ?>
					&nbsp;
				<?php endif ?>
			</div>
		<?php endforeach ?>
	</div>
<!-- SPOTLIGHT -->