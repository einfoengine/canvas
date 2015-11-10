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
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" class="<?php $this->bodyClass(); ?>">

  <head>
    <jdoc:include type="head" />
    
    <?php $this->loadBlock ('head') ?>
  </head>

  <body>
    <section id="canvas-mainbody" class="container canvas-mainbody">
      <div class="row">
        <div id="canvas-content" class="canvas-content span12">
          <jdoc:include type="canvasajax" />
        </div>
      </div>
    </section>
  </body>

</html>