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

// Initiasile related data.
require_once JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php';
$menuTypes = MenusHelper::getMenuLinks();
$user = JFactory::getUser();
?>

<div class="canvas-admin-assignment clearfix">

  <div class="canvas-admin-fieldset-desc">
    <?php echo JText::_('CANVAS_MENUS_ASSIGNMENT_DESC'); ?>
  </div>

  <div class="control-group canvas-control-group">

    <div class="control-label canvas-control-label">
      <label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>
    </div>

    <div class="controls canvas-controls">
      <div class="btn-toolbar">
        <button type="button" class="btn" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
          <i class="icon-checkbox-partial"></i>  <?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
        </button>
      </div>
      <div id="menu-assignment">
        <ul class="menu-links thumbnails">
          <?php foreach ($menuTypes as &$type) : ?>
              <li class="span3">
                <div class="thumbnail">
                <h5><?php echo $type->title ? $type->title : $type->menutype; ?>
								<a href="javascript://" class="menu-assignment-toggle" title="<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>">
									<i class="icon-checkbox-partial"></i>
								</a>
								</h5>
									<?php // foreach ($type->links as $link) :?>
									<?php for ($i=0; $i<count ($type->links) ; $i++) :
									$link = $type->links[$i];
									$next = $i < count ($type->links) - 1 ? $type->links[$i+1] : null;
									?>
                    <label class="checkbox small level<?php echo $link->level ?>" data-level="<?php echo $link->level ?>" for="link<?php echo (int) $link->value;?>" >
                    <input type="checkbox" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php if ($link->template_style_id == $form->getValue('id')):?> checked="checked"<?php endif;?><?php if ($link->checked_out && $link->checked_out != $user->id):?> disabled="disabled"<?php else:?> class="chk-menulink "<?php endif;?> />
										<?php echo $link->text; ?>
										<?php if ($next && $next->level > $link->level) : ?>
											<a href="javascript://" class="menu-assignment-toggle" title="<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>">
												<i class="icon-checkbox-partial"></i>
											</a>
											<a href="javascript://" title="<?php echo JText::_('CANVAS_GLOBAL_TOGGLE_FOLDING'); ?>">
												<i class="menu-tree-toggle icon-minus"></i>
											</a>
										<?php endif ?>
                    </label>
                  <?php endfor; ?>
                  <?php // endforeach; ?>
                </div>
              </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

