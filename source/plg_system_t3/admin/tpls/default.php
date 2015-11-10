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

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
$user = JFactory::getUser();
$canDo = method_exists('TemplatesHelper', 'getActions') ? TemplatesHelper::getActions() : JHelperContent::getActions('com_templates');
$iswritable = is_writable('canvastest.txt');
?>
<?php if($iswritable): ?>
<div id="canvas-admin-writable-message" class="alert warning">
	<button type="button" class="close" data-dismiss="alert">×</button>
	<strong><?php echo JText::_('CANVAS_MSG_WARNING'); ?></strong> <?php echo JText::_('CANVAS_MSG_FILE_NOT_WRITABLE'); ?>
</div>
<?php endif;?>
<div class="canvas-admin-form clearfix">
<form action="<?php echo JRoute::_('index.php?option=com_templates&layout=edit&id='.$input->getInt('id')); ?>" method="post" name="adminForm" id="style-form" class="form-validate form-horizontal">
	<div class="canvas-admin-header clearfix">
		<div class="controls-row">
			<div class="control-group canvas-control-group">
				<div class="control-label canvas-control-label">
					<label id="canvas-styles-list-lbl" for="canvas-styles-list" class="hasTooltip" title="<?php echo JText::_('CANVAS_SELECT_STYLE_DESC'); ?>"><?php echo JText::_('CANVAS_SELECT_STYLE_LABEL'); ?></label>
				</div>
				<div class="controls canvas-controls">
					<?php echo JHTML::_('select.genericlist', $styles, 'canvas-styles-list', 'autocomplete="off"', 'id', 'title', $input->get('id')); ?>
				</div>
			</div>
			<div class="control-group canvas-control-group">
				<div class="control-label canvas-control-label">
					<?php echo $form->getLabel('title'); ?>
				</div>
				<div class="controls canvas-controls">
					<?php echo $form->getInput('title'); ?>
				</div>
			</div>
			<div class="control-group canvas-control-group hide">
				<div class="control-label canvas-control-label">
					<?php echo $form->getLabel('template'); ?>
				</div>
				<div class="controls canvas-controls">
					<?php echo $form->getInput('template'); ?>
				</div>
			</div>
			<div class="control-group canvas-control-group hide">
				<div class="control-label canvas-control-label">
					<?php echo $form->getLabel('client_id'); ?>
				</div>
				<div class="controls canvas-controls">
					<?php echo $form->getInput('client_id'); ?>
					<input type="text" size="35" value="<?php echo $form->getValue('client_id') == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>	" class="input readonly" readonly="readonly" />
				</div>
			</div>
			<div class="control-group canvas-control-group">
				<div class="control-label canvas-control-label">
					<?php echo str_replace('<label', '<label data-placement="bottom" ', $form->getLabel('home')); ?>
				</div>
				<div class="controls canvas-controls">
					<?php echo $form->getInput('home'); ?>
				</div>
			</div>
		</div>
	</div>
	<fieldset>
		<div class="canvas-admin clearfix">
			<div class="canvas-admin-nav">
				<ul class="nav nav-tabs">
					<li<?php echo $canvaslock == 'overview_params' ? ' class="active"' : ''?>><a href="#overview_params" data-toggle="tab"><?php echo JText::_('CANVAS_OVERVIEW_LABEL');?></a></li>
					<?php
					$fieldSets = $form->getFieldsets('params');
					foreach ($fieldSets as $name => $fieldSet) :
						$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_TEMPLATES_'.$name.'_FIELDSET_LABEL';
					?>
						<li<?php echo $canvaslock == preg_replace( '/\s+/', ' ', $name) ? ' class="active"' : ''?>><a href="#<?php echo preg_replace( '/\s+/', ' ', $name);?>" data-toggle="tab"><?php echo JText::_($label) ?></a></li>
					<?php
					endforeach;
					?>
					<?php if ($user->authorise('core.edit', 'com_menu') && ($form->getValue('client_id') == 0)):?>
						<?php if ($canDo->get('core.edit.state')) : ?>
							<li<?php echo $canvaslock == 'assignment' ? ' class="active"' : ''?>><a href="#assignment_params" data-toggle="tab"><?php echo JText::_('CANVAS_MENUS_ASSIGNMENT_LABEL');?></a></li>
						<?php endif; ?>
					<?php endif;?>
				</ul>
			</div>
			<div class="canvas-admin-tabcontent tab-content clearfix">
				<div class="tab-pane tab-overview clearfix<?php echo $canvaslock == 'overview_params' ? ' active' : ''?>" id="overview_params">
					<?php
						$default_overview_override = CANVAS_TEMPLATE_PATH . '/admin/default_overview.php';
						if(file_exists($default_overview_override)) {
							include $default_overview_override;
						} else {
							include CANVAS_ADMIN_PATH . '/admin/tpls/default_overview.php';
						}
					?>
				</div>
			<?php
			foreach ($fieldSets as $name => $fieldSet) : ?>
				<div class="tab-pane<?php echo $canvaslock == preg_replace( '/\s+/', ' ', $name) ? ' active' : ''?>" id="<?php echo preg_replace( '/\s+/', ' ', $name); ?>">
					<?php

					if (isset($fieldSet->description) && trim($fieldSet->description)) : 
						echo '<div class="canvas-admin-fieldset-desc">'.(JText::_($fieldSet->description)).'</div>';
					endif;

					foreach ($form->getFieldset($name) as $field) :
						$hide = ($field->type === 'CANVASDepend' && $form->getFieldAttribute($field->fieldname, 'function', '', $field->group) == '@group');
						$fieldinput = $field->input;

						// add placeholder to Text input
						if ($field->type == 'Text') {
							$placeholder = $form->getFieldAttribute($field->fieldname, 'placeholder', '', $field->group);
							if(empty($placeholder)){
								$placeholder = $form->getFieldAttribute($field->fieldname, 'default', '', $field->group);
							} else {
								$placeholder = JText::_($placeholder);
							}

							if(!empty($placeholder)){
								$fieldinput = str_replace ('/>', ' placeholder="' . $placeholder . '"/>', $fieldinput);
							}
						}

						$global = $form->getFieldAttribute($field->fieldname, 'global', 0, $field->group);
					?>
					<?php if ($field->hidden || ($field->type == 'CANVASDepend' && !$field->label)) : ?>
						<?php echo $fieldinput; ?>
					<?php else : ?>
					<div class="control-group canvas-control-group<?php echo $hide ? ' hide' : '' ?>">
						<div class="control-label canvas-control-label<?php echo $global ? ' canvas-admin-global' : '' ?>">
							<?php echo $field->label; ?>
						</div>
						<div class="controls canvas-controls">
							<?php echo $fieldinput ?>
						</div>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
				</div>
			<?php endforeach;  ?>

			<?php if ($user->authorise('core.edit', 'com_menu') && $form->getValue('client_id') == 0):?>
				<?php if ($canDo->get('core.edit.state')) : ?>
					<div class="tab-pane clearfix<?php echo $canvaslock == 'assignment' ? ' active' : ''?>" id="assignment_params">
						<?php include CANVAS_ADMIN_PATH . '/admin/tpls/default_assignment.php'; ?>
					</div>
				<?php endif; ?>
			<?php endif;?>
		</div>
		</div>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>

<?php
	if (is_file(CANVAS_ADMIN_PATH . '/admin/tour/tour.tpl.php')){
		include_once CANVAS_ADMIN_PATH . '/admin/tour/tour.tpl.php';
	}

	//if (is_file(CANVAS_ADMIN_PATH . '/admin/megamenu/megamenu.tpl.php')){
	//	include_once CANVAS_ADMIN_PATH . '/admin/megamenu/megamenu.tpl.php';
	//}

	if (is_file(CANVAS_ADMIN_PATH . '/admin/layout/layout.tpl.php')){
		include_once CANVAS_ADMIN_PATH . '/admin/layout/layout.tpl.php';
	}
?>
