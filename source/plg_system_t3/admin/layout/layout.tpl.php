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

	CANVAS::import('admin/layout');
?>

<!-- LAYOUT CONFIGURATION PANEL -->
<div id="canvas-admin-layout" class="canvas-admin-layout hide">
	<div class="canvas-admin-inline-nav clearfix">
		<div class="canvas-admin-layout-row-mode clearfix">
			<ul class="canvas-admin-layout-modes nav nav-tabs">
				<li class="canvas-admin-layout-mode-structure active"><a href="" title="<?php echo JText::_('CANVAS_LAYOUT_MODE_STRUCTURE') ?>"><?php echo JText::_('CANVAS_LAYOUT_MODE_STRUCTURE') ?></a></li>
				<li class="canvas-admin-layout-mode-layout"><a href="" title="<?php echo JText::_('CANVAS_LAYOUT_MODE_LAYOUT') ?>"><?php echo JText::_('CANVAS_LAYOUT_MODE_LAYOUT') ?></a></li>
			</ul>
			<button class="canvas-admin-layout-reset-all btn pull-right"><i class="icon-undo"></i>  <?php echo JText::_('CANVAS_LAYOUT_RESET_ALL') ?></button>
		</div>
		<div class="canvas-admin-layout-row-device clearfix">
			<div class="canvas-admin-layout-devices btn-group hide">
				<?php $canvasdevices = json_decode(CANVAS_BASE_DEVICES, true); ?>
				<?php foreach($canvasdevices as $device) : ?>
					<?php if((bool)CANVAS_BASE_RSP_IN_CLASS || $device != CANVAS_BASE_DEFAULT_DEVICE) : ?>
						<button class="btn canvas-admin-dv-<?php echo $device ?>" data-device="<?php echo $device ?>" title="<?php echo JText::_('CANVAS_LAYOUT_DVI_' . strtoupper($device)) ?>"><i class="icon-device"></i>  <?php echo JText::_('CANVAS_LAYOUT_DVI_' . strtoupper($device)) ?></button>
					<?php endif ?>				
				<?php endforeach; ?>
			</div>
			<button class="btn canvas-admin-layout-reset-device pull-right hide"><?php echo JText::_('CANVAS_LAYOUT_RESET_PER_DEVICE') ?></button>
			<button class="btn canvas-admin-layout-reset-position pull-right"><?php echo JText::_('CANVAS_LAYOUT_RESET_POSITION') ?></button>
			<button class="canvas-admin-tog-fullscreen" title="<?php echo JText::_('CANVAS_LAYOUT_TOGG_FULLSCREEN') ?>"><i class="icon-resize-full"></i></button>
		</div>
	</div>
	<div id="canvas-admin-layout-container" class="canvas-admin-layout-container canvas-admin-layout-preview canvas-admin-layout-mode-m"></div>
</div>

<!-- POPOVER POSITIONS -->
<div id="canvas-admin-layout-tpl-positions" class="popover right hide">
	<div class="arrow"></div>
	<h3 class="popover-title"><?php echo JText::_('CANVAS_LAYOUT_POPOVER_TITLE') ?></h3>
	<div class="popover-content">
		<?php echo CANVASAdminLayout::getPositions() ?>
		<button class="canvas-admin-layout-rmvbtn btn btn-small"><i class="icon-remove"></i>  <?php echo JText::_('CANVAS_LAYOUT_EMPTY_POSITION') ?></button>
		<button class="canvas-admin-layout-defbtn btn btn-small btn-success"><i class="icon-ok-circle"></i>  <?php echo JText::_('JDEFAULT') ?></button>
		<button class="canvas-admin-layout-combtn btn btn-small btn-primary"><i class="icon-align-justify"></i>  <?php echo JText::_('CANVAS_JCOMPONENT') ?></button>
	</div>
</div>

<!-- CLONE BUTTONS -->
<div id="canvas-admin-layout-clone-btns">
	<button id="canvas-admin-layout-clone-copy" class="btn btn-success"><i class="icon-save"></i>  <?php echo JText::_('CANVAS_LAYOUT_LABEL_SAVE_AS_COPY') ?></button>
	<button id="canvas-admin-layout-clone-delete" class="btn hasTooltip" title="<?php echo JText::_('CANVAS_LAYOUT_DESC_DELETE') ?>"><i class="icon-trash"></i>  <?php echo JText::_('CANVAS_LAYOUT_LABEL_DELETE') ?></button>
	<button id="canvas-admin-layout-clone-purge" class="btn hasTooltip" title="<?php echo JText::_('CANVAS_LAYOUT_DESC_PURGE') ?>"><i class="icon-remove"></i>  <?php echo JText::_('CANVAS_LAYOUT_LABEL_PURGE') ?></button>
</div>

<!-- MODAL CLONE LAYOUT -->
<div id="canvas-admin-layout-clone-dlg" class="layout-modal modal fade hide">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><?php echo JText::_('CANVAS_LAYOUT_ASK_ADD_LAYOUT') ?></h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal prompt-block">
			<p><?php echo JText::_('CANVAS_LAYOUT_ASK_ADD_LAYOUT_DESC') ?></p>
      <div class="input-prepend">
        <span class="add-on"><i class="icon-info-sign"></i></span>
        <input type="text" class="input-xlarge" id="canvas-admin-layout-cloned-name" />
      </div>
		</form>
		<div class="message-block">
			<p class="msg"><?php echo JText::_('CANVAS_LAYOUT_ASK_DEL_LAYOUT_DESC') ?></p>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn cancel" data-dismiss="modal"><?php echo JText::_('JCANCEL') ?></button>
		<button class="btn btn-danger yes hide"><?php echo JText::_('CANVAS_LAYOUT_LABEL_DELETEIT') ?></button>
		<button class="btn btn-success yes"><?php echo JText::_('CANVAS_LAYOUT_LABEL_CLONEIT') ?></button>
	</div>
</div>
<script type="text/javascript">
	CANVASAdminLayout = window.CANVASAdminLayout || {};
	CANVASAdminLayout.layout = CANVASAdminLayout.layout || {};
	CANVASAdminLayout.layout.devices     = <?php echo CANVAS_BASE_DEVICES ?>;
	CANVASAdminLayout.layout.maxcol      = <?php echo CANVAS_BASE_DV_MAXCOL ?>;
	CANVASAdminLayout.layout.minspan     = <?php echo CANVAS_BASE_DV_MINWIDTH ?>;
	CANVASAdminLayout.layout.unitspan    = <?php echo CANVAS_BASE_DV_UNITSPAN ?>;
	CANVASAdminLayout.layout.dlayout     = '<?php echo CANVAS_BASE_DEFAULT_DEVICE ?>';
	CANVASAdminLayout.layout.clayout     = '<?php echo CANVAS_BASE_DEFAULT_DEVICE ?>';
	CANVASAdminLayout.layout.nlayout     = '<?php echo CANVAS_BASE_DEFAULT_DEVICE ?>';
	CANVASAdminLayout.layout.maxgrid     = <?php echo CANVAS_BASE_MAX_GRID ?>;
	CANVASAdminLayout.layout.maxcols     = <?php echo CANVAS_BASE_MAX_GRID ?>;
	CANVASAdminLayout.layout.widthprefix = '<?php echo CANVAS_BASE_WIDTH_PREFIX ?>';
	CANVASAdminLayout.layout.spanptrn    = '<?php echo CANVAS_BASE_WIDTH_PATTERN ?>';
	CANVASAdminLayout.layout.hiddenptrn  = '<?php echo CANVAS_BASE_HIDDEN_PATTERN ?>';
	CANVASAdminLayout.layout.firstptrn   = '<?php echo CANVAS_BASE_FIRST_PATTERN ?>';
	CANVASAdminLayout.layout.spancls     = new RegExp('<?php echo trim(preg_quote(CANVAS_BASE_WIDTH_REGEX), '/') ?>', 'g');
	CANVASAdminLayout.layout.responcls   = <?php echo (bool)CANVAS_BASE_RSP_IN_CLASS ? 'true' : 'false' ?>;
</script>