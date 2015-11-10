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
<div id="canvas-admin-toolbar" class="btn-toolbar">

	<?php if($input->getCmd('view') == 'style'): ?>
	<div id="canvas-admin-tb-save" class="btn-group">
		<button id="canvas-admin-tb-style-save-save" class="btn btn-success"><i class="icon-save"></i>  <?php echo JText::_('CANVAS_TOOLBAR_SAVE') ?></button>
		<button class="btn btn-success dropdown-toggle" data-toggle="dropdown">
			<span class="caret"></span>&nbsp;
		</button>
		<ul class="dropdown-menu">
			<li id="canvas-admin-tb-style-save-close"><a href="#"><?php echo JText::_('CANVAS_TOOLBAR_SAVECLOSE') ?></a></li>
			<li id="canvas-admin-tb-style-save-clone"><a href="#"><?php echo JText::_('CANVAS_TOOLBAR_SAVE_AS_CLONE') ?></a></li>
		</ul>
	</div>
	<?php endif; ?>

	<div id="canvas-admin-tb-recompile" class="btn-group">
		<button id="canvas-admin-tb-compile-all" class="btn hasTip" title="<?php echo JText::_('CANVAS_TOOLBAR_COMPILE_LESS_CSS') ?>::<?php echo JText::_('CANVAS_TOOLBAR_COMPILE_LESS_CSS_DESC') ?>"><i class="icon-code"></i>  <i class="icon-loading"></i>  <?php echo JText::_('CANVAS_TOOLBAR_COMPILE_LESS_CSS') ?></button>
		<?php if($input->getCmd('view') == 'style') : ?>
		<button class="btn dropdown-toggle" data-toggle="dropdown">
			<span class="caret"></span>&nbsp;
		</button>
		<ul class="dropdown-menu">
			<li id="canvas-admin-tb-compile-this" data-default="<?php echo JText::_('JDEFAULT') ?>" data-msg="<?php echo JText::_('CANVAS_TOOLBAR_COMPILE_THIS') ?>"><a href="#"><?php echo JText::sprintf('CANVAS_TOOLBAR_COMPILE_THIS', $params->get('theme', JText::_('JDEFAULT'))) ?></a></li>
		</ul>
		<?php endif ?>
	</div>

	<div id="canvas-admin-tb-themer" 
		class="btn-group">
		<button 
			data-title="<?php echo JText::_('CANVAS_TM_THEME_MAGIC') ?>"
			data-content="<?php echo JText::_('CANVAS_MSG_ENABLE_THEMEMAGIC') ?>"
			class="btn hasTip" 
			title="<?php echo JText::_('CANVAS_TOOLBAR_THEMER') ?>::<?php echo JText::_('CANVAS_TOOLBAR_THEMER_DESC') ?>">
			
			<i class="icon-magic"></i>  <?php echo JText::_('CANVAS_TOOLBAR_THEMER') ?>
		</button>
	</div>

	<div id="canvas-admin-tb-megamenu" 
		class="btn-group" >
		<button 
			data-title="<?php echo JText::_('CANVAS_NAVIGATION_MM_TITLE') ?>"
			data-content="<?php echo JText::_('CANVAS_MSG_MEGAMENU_NOT_USED') ?>"
			class="btn hasTip" 
			title="<?php echo JText::_('CANVAS_TOOLBAR_MEGAMENU') ?>::<?php echo JText::_('CANVAS_TOOLBAR_MEGAMENU_DESC') ?>">
				<i class="icon-sitemap"></i>  <?php echo JText::_('CANVAS_TOOLBAR_MEGAMENU') ?>
		</button>
	</div>
	
	<?php if(version_compare(JVERSION, '3.0', 'ge') && $input->getCmd('view') == 'template'): ?>
	<div id="canvas-admin-tb-copy" class="btn-group <?php echo $input->getCmd('view') ?>" data-toggle="modal" data-target="#collapseModal">
		<button class="btn"><i class="icon-copy"></i>  <?php echo JText::_('CANVAS_TOOLBAR_COPY') ?></button>
	</div>
	<?php endif; ?>

	<div id="canvas-admin-tb-close" class="btn-group <?php echo $input->getCmd('view') ?>">
		<button class="btn"><i class="icon-remove"></i>  <?php echo JText::_('CANVAS_TOOLBAR_CLOSE') ?></button>
	</div>
	<div id="canvas-admin-tb-help" class="btn-group <?php echo $input->getCmd('view') ?>">
		<button class="btn"><i class="icon-question-sign"></i>  <?php echo JText::_('CANVAS_TOOLBAR_HELP') ?></button>
	</div>

</div>