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
 * @credits       Mary Lou - http://tympanus.net/codrops/2013/08/28/transitions-for-off-canvas-navigations/
 *------------------------------------------------------------------------------
 */

defined('_JEXEC') or die;
?>

<?php
  if (!$this->getParam('addon_offcanvas_enable')) return ;
?>

<button class="btn btn-default off-canvas-toggle" type="button" data-pos="left" data-nav="#canvas-off-canvas" data-effect="<?php echo $this->getParam('addon_offcanvas_effect', 'off-canvas-effect-4') ?>">
  <i class="fa fa-bars"></i>
</button>

<!-- OFF-CANVAS SIDEBAR -->
<div id="canvas-off-canvas" class="canvas-off-canvas">

  <div class="canvas-off-canvas-header">
    <h2 class="canvas-off-canvas-header-title">Sidebar</h2>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  </div>

  <div class="canvas-off-canvas-body">
    <jdoc:include type="modules" name="<?php $this->_p('off-canvas') ?>" style="CANVASXhtml" />
  </div>

</div>
<!-- //OFF-CANVAS SIDEBAR -->
