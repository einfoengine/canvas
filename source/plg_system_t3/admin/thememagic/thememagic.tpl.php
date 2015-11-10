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

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title><?php echo JText::_('CANVAS_TM_TITLE'); ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo CANVAS_ADMIN_URL; ?>/admin/bootstrap/css/bootstrap.css" />
		<link type="text/css" rel="stylesheet" href="<?php echo CANVAS_ADMIN_URL; ?>/admin/plugins/miniColors/jquery.miniColors.css" />
		<link type="text/css" rel="stylesheet" href="<?php echo CANVAS_ADMIN_URL; ?>/admin/thememagic/css/thememagic.css" />

		<script type="text/javascript" src="<?php echo CANVAS_ADMIN_URL; ?>/admin/js/jquery-1.8.3.min.js"></script>
		<script type="text/javascript" src="<?php echo CANVAS_ADMIN_URL; ?>/admin/bootstrap/js/bootstrap.js"></script>
	</head>

	<body<?php echo $tplparams->get('themermode', 1) == 0 ? ' class="no-magic"' : ''?>>
		<div id="wrapper">
			<?php if($tplparams->get('themermode', 1)): ?>
			<div id="canvas-admin-thememagic">
				<a href="<?php echo JURI::base(true); ?>" class="themer-minimize"><i class="icon-remove-sign"></i><i class="icon-magic"></i>  <span><?php echo JText::_('CANVAS_TM_MINIMIZE') ; ?></span></a>
				<a href="<?php echo $backurl; ?>" class="themer-close" title="<?php echo JText::_($isadmin ? 'CANVAS_TM_BACK_TO_ADMIN' : 'CANVAS_TM_EXIT'); ?>"><i class="icon-arrow-left"></i><?php echo JText::_($isadmin ? 'CANVAS_TM_BACK_TO_ADMIN' : 'CANVAS_TM_EXIT'); ?></a>
				
				<div class="canvas-admin-tm-header">
					<div id="canvas-admin-tm-recss" class="canvas-progress"></div>
				  <h2><strong><?php echo JText::_('CANVAS_TM_CUSTOMIZING'); ?></strong> <span><?php echo CANVAS_TEMPLATE ?></span></h2>
				  <form id="canvas-admin-tm-form" name="canvas-admin-tm-form" class="form-validate form-inline">
					<div class="controls controls-row">
						<label for="canvas-admin-theme-list"><?php echo JText::_('CANVAS_TM_THEME_LABEL'); ?></label>
					  <?php
						echo JHTML::_('select.genericlist', $themes, 'canvas-admin-theme-list', 'autocomplete="off"', 'id', 'title', $tplparams->get('theme', -1));
					  ?>
					  <div class="btn-group">
						<button id="canvas-admin-tm-pvbtn" class="btn btn-primary"><?php echo JText::_('CANVAS_TM_PREVIEW') ?></button>
						<?php if( $isadmin) : ?>
						<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
						<ul class="dropdown-menu">
						  <li><a id="canvas-admin-tm-save" href="" title="<?php echo JText::_('CANVAS_TM_SAVE') ?>"><?php echo JText::_('CANVAS_TM_SAVE') ?></a></li>
						  <li><a id="canvas-admin-tm-saveas" href="" title="<?php echo JText::_('CANVAS_TM_SAVEAS') ?>"><?php echo JText::_('CANVAS_TM_SAVEAS') ?></a></li>
						  <li><a id="canvas-admin-tm-delete" href="" title="<?php echo JText::_('CANVAS_TM_DELETE') ?>"><?php echo JText::_('CANVAS_TM_DELETE') ?></a></li>
						</ul>
					  	<?php endif; ?>
					  </div>
					</div>
				  </form>
				</div>
	
				<form id="canvas-admin-tm-variable-form" name="adminForm" class="form-validate">
					<div class="accordion" id="canvas-admin-tm-accord">
						<?php
						$i = 0;
						foreach ($fieldSets as $name => $fieldSet) :
							$label = !empty($fieldSet->label) ? $fieldSet->label : 'CANVAS_TM_'.$name.'_FIELDSET_LABEL';

							if(in_array($name, $disabledFieldSets)){
								continue;
							}
						?>
							
						<div class="accordion-group<?php echo $i == 0?' active':'' ?>">
							<div class="accordion-heading">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#canvas-admin-tm-accord" href="#<?php echo preg_replace( '/\s+/', ' ', $name);?>"><?php echo JText::_($label) ?></a>
							</div>
							<div id="<?php echo preg_replace( '/\s+/', ' ', $name);?>" class="accordion-body collapse<?php echo (($i == 0)? ' in' : ''); ?>">
								<div class="accordion-inner">
									<?php
									$fields = $form->getFieldset($name);
									$forders = array();
									foreach ($fields as $field) {
										$after = 0;
										$compare = $form->getFieldAttribute($field->fieldname, 'before', '', $field->group);
										if(empty($compare)){
											$compare = $form->getFieldAttribute($field->fieldname, 'after', '', $field->group);
											$after = 1;
										}
										if(!empty($compare)){
											$found = null;
											$i = 0;
											$compare = $field->formControl . '[' . $field->group . ']' . '[' . $compare . ']';
											
											foreach($forders as $ofield) {
												if ($compare == $ofield->name) {
													$found = $ofield;
													break;
												}
												$i++;
											}

											if($found && $i + $after < count($forders)){
												array_splice($forders, $i + $after, 0, array($field));
												continue;
											}
										}

										$forders[] = $field;
									}

									foreach ($forders as $field) :
										$hide = ($field->type === 'CANVASDepend' && $form->getFieldAttribute($field->fieldname, 'function', '', $field->group) == '@group');
										$textinput = $field->input;

										// add placeholder to Text input
										if ($field->type == 'Text' || $field->type == 'Color') {
											
											$textinput = str_replace ('/>', ' placeholder="' . $form->getFieldAttribute($field->fieldname, 'default', '', $field->group) .'"/>', $textinput);

											if($field->type == 'Color'){
												$textinput = str_replace(array('"#000000"', '"#rrggbb"'), '""', $textinput);
											}
										}
									?>
										<div class="control-group canvas-control-group<?php echo $hide ? ' hide' : ''?>">
										<?php if (!$field->hidden) : ?>
											<div class="control-label canvas-control-label">
												<?php echo preg_replace('/(\s*)for="(.*?)"(\s*)/i', ' ', $field->label); ?>
											</div>
										<?php endif; ?>
											<div class="controls canvas-controls">
												<?php echo $textinput ?>
											</div>
										</div>
									<?php
									endforeach;
									?>
								</div>
							</div>
						</div>

					<?php
					$i++;
						endforeach;
					?>
				</div>
			</form>
			</div>
			<?php else :?>
			
			<div id="canvas-admin-tm-warning" class="modal hide fade">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3><?php echo JText::_('CANVAS_TM_TITLE'); ?></h3>
				</div>
				<div class="modal-body">
					<p><?php echo JText::_('CANVAS_MSG_ENABLE_THEMEMAGIC'); ?></p>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-primary" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('CANVAS_LBL_OK') ?></a>
				</div>
			</div>

			<?php endif;?>
			<div id="canvas-admin-tm-preview">
				<iframe id="canvas-admin-tm-ifr-preview" frameborder="0" src="<?php echo $url ?>"></iframe>
			</div>

		</div>

		<?php if($tplparams->get('themermode', 1)): ?>
		<div id="canvas-admin-thememagic-dlg" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3><?php echo JText::_('CANVAS_TM_THEME_MAGIC') ?></h3>
			</div>
			<div class="modal-body">
				<div class="row-fluid">
					<form id="prompt-form" name="prompt-form" class="form-horizontal prompt-block">
						<span class="help-block"><?php echo JText::_('CANVAS_TM_ASK_ADD_THEME') ?></span>
						<p>
							<input type="text" id="theme-name" class="span12" placeholder="<?php echo JText::_('CANVAS_TM_THEME_NAME') ?>">
						</p>
					</form>
					<div class="message-block">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="" class="btn btn-default cancel" data-dismiss="modal" aria-hidden="true"></a>
				<a href="" class="btn btn-primary"></a>
			</div>
		</div>
		
		
		<script type="text/javascript" src="<?php echo CANVAS_ADMIN_URL; ?>/admin/js/json2.js"></script>
		<script type="text/javascript" src="<?php echo CANVAS_ADMIN_URL; ?>/admin/plugins/miniColors/jquery.miniColors.js"></script>
		<script type="text/javascript" src="<?php echo CANVAS_ADMIN_URL; ?>/includes/depend/js/depend.js"></script>
		<script type="text/javascript" src="<?php echo CANVAS_ADMIN_URL; ?>/admin/thememagic/js/thememagic.js"></script>
		<script type="text/javascript">
			// add class active for open 
			$('#canvas-admin-tm-accord .accordion-group').on('hide', function (e) {
				if($(e.target).hasClass('accordion-body')){
					$(this).removeClass('active');
				}
			}).on('show', function(e) {
				if($(e.target).hasClass('accordion-body')){
					$(this).addClass('active');
				}
			});
			
			var CANVASTheme = window.CANVASTheme || {};
			CANVASTheme.admin = <?php echo intval($isadmin); ?>;
			CANVASTheme.data = <?php echo json_encode($jsondata); ?>;
			CANVASTheme.themes = <?php echo json_encode($themes); ?>;
			CANVASTheme.template = '<?php echo CANVAS_TEMPLATE; ?>';
			CANVASTheme.templateid = '<?php echo JFactory::getApplication()->input->getInt('id'); ?>';
			CANVASTheme.url = '<?php echo JURI::root(true) . '/administrator/index.php'; ?>';
			CANVASTheme.langs = <?php echo json_encode($langs); ?>;
			CANVASTheme.active = '<?php echo $tplparams->get('theme', 'base')?>';
			CANVASTheme.variables = <?php echo ($tplparams->get('theme', -1) == -1 ? '{}' : 'CANVASTheme.data[CANVASTheme.active]') ?>;
			CANVASTheme.colorimgurl = '<?php echo CANVAS_ADMIN_URL; ?>/admin/plugins/colorpicker/images/ui-colorpicker.png';

			//Keepalive
			setInterval(function(){
				$.get('index.php');
			}, <?php echo $refreshTime; ?>);

			//tooltip
			$('.hasTooltip').tooltip({html: true, container: 'body'});

		</script>
		<?php else :?>
			<script type="text/javascript">
				$(document).ready(function(){
					$('#canvas-admin-tm-warning').modal('show')
				});
			</script>
		<?php endif;?>
	</body>
</html>