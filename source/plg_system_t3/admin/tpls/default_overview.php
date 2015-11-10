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

jimport('joomla.updater.update');

$telem = CANVAS_TEMPLATE;
$felem = CANVAS_ADMIN;

$thasnew = false;
$ctversion = $ntversion = $xml->version;
$fhasnew = false;
$cfversion = $nfversion = $fxml->version;

$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query
  ->select('*')
  ->from('#__updates')
  ->where('(element = ' . $db->q($telem) . ') OR (element = ' . $db->q($felem) . ')');
$db->setQuery($query);
$results = $db->loadObjectList('element');

if(count($results)){
  if(isset($results[$telem]) && version_compare($results[$telem]->version, $ctversion, 'gt')){
    $thasnew = true;
    $ntversion = $results[$telem]->version;
  }
  
  if(isset($results[$felem]) && version_compare($results[$felem]->version, $cfversion, 'gt')){
    $fhasnew = true;
    $nfversion = $results[$felem]->version;
  }
}

$hasperm = JFactory::getUser()->authorise('core.manage', 'com_installer');

// Try to humanize the name
$xml->name = ucwords(str_replace('_', ' ', $xml->name));
$fxml->name = ucwords(str_replace('_', ' ', $fxml->name));

?>
<div class="canvas-admin-overview">

  <legend class="canvas-admin-form-legend"><?php echo JText::_('CANVAS_OVERVIEW_TPL_INFO')?></legend>
  <div id="canvas-admin-template-home" class="section">
  	<div class="row-fluid">
		
		<?php if (is_file (CANVAS_TEMPLATE_PATH.'/templateInfo.php')): ?>
			<?php include CANVAS_TEMPLATE_PATH.'/templateInfo.php' ?>
		<?php else: ?>
		
			<div class="span8">
				<div class="span4 col-md-4">
					<div class="tpl-preview">
						<img src="<?php echo CANVAS_TEMPLATE_URL ?>/template_preview.png" alt="Template Preview"/>
					</div>
				</div>
				<div class="span8 col-md-8">
					<div class="canvas-admin-overview-header">
						<h2>
							<?php echo JText::_('CANVAS_TPL_DESC_1') ?>
							<small><?php echo JText::_('CANVAS_TPL_DESC_2') ?></small>
						</h2>
						<p><?php echo JText::_('CANVAS_TPL_DESC_3') ?></p>
					</div>
					<div class="canvas-admin-overview-body">
						<h4><?php echo JText::_('CANVAS_TPL_DESC_4') ?></h4>
						<ul class="canvas-admin-overview-features">
							<li><?php echo JText::_('CANVAS_TPL_DESC_5') ?></li>
							<li><?php echo JText::_('CANVAS_TPL_DESC_6') ?></li>
							<li><?php echo JText::_('CANVAS_TPL_DESC_7') ?></li>
							<li><?php echo JText::_('CANVAS_TPL_DESC_8') ?></li>
						</ul>
					</div>
				</div>
			</div>
			
			<div class="span4">
				<div id="canvas-admin-tpl-info" class="canvas-admin-overview-block clearfix">
				  <h3><?php echo JText::_('CANVAS_OVERVIEW_TPL_INFO')?></h3>
				  <dl class="info">
					<dt><?php echo JText::_('CANVAS_OVERVIEW_NAME')?></dt>
					<dd><?php echo $xml->name ?></dd>
					<dt><?php echo JText::_('CANVAS_OVERVIEW_VERSION')?></dt>
					<dd><?php echo $xml->version ?></dd>
					<dt><?php echo JText::_('CANVAS_OVERVIEW_CREATE_DATE')?></dt>
					<dd><?php echo $xml->creationDate ?></dd>
					<dt><?php echo JText::_('CANVAS_OVERVIEW_AUTHOR')?></dt>
					<dd><a href="<?php echo $xml->authorUrl ?>" title="<?php echo $xml->author ?>"><?php echo $xml->author ?></a></dd>
				  </dl>
				</div>
				<div class="canvas-admin-overview-block updater<?php echo $thasnew ? ' outdated' : '' ?> clearfix">
				  <h3><?php echo empty($xml->updateservers) ? JText::sprintf('CANVAS_OVERVIEW_TPL_VERSION', $xml->name, $xml->version) : JText::sprintf($thasnew ? 'CANVAS_OVERVIEW_TPL_NEW' : 'CANVAS_OVERVIEW_TPL_SAME', $xml->name) ?></h3>
				  <p><?php echo empty($xml->updateservers) ? JText::_('CANVAS_OVERVIEW_TPL_VERSION_MSG') : ($thasnew ? JText::sprintf('CANVAS_OVERVIEW_TPL_NEW_MSG', $ctversion, $xml->name, $ntversion) : JText::sprintf('CANVAS_OVERVIEW_TPL_SAME_MSG', $ctversion)) ?></p>
				  <?php if($hasperm) :
					if(empty($xml->updateservers)): ?>
					<a class="btn" href="http://www.themezart.com/products/joomla/templates" class="canvascheck-framework" title="<?php echo JText::_('CANVAS_OVERVIEW_TPL_DL_CENTER') ?>"><?php echo JText::_('CANVAS_OVERVIEW_TPL_DL_CENTER') ?></a>&nbsp;
					<a class="btn" href="http://update.themezart.com" class="canvascheck-framework" title="<?php echo JText::_('CANVAS_OVERVIEW_TPL_UPDATE_CENTER') ?>"><?php echo JText::_('CANVAS_OVERVIEW_TPL_UPDATE_CENTER') ?></a>
					<?php else : ?> 
					<a class="btn" href="<?php JURI::base() ?>index.php?option=com_installer&view=update" class="canvascheck-framework" title="<?php echo JText::_( $thasnew ? 'CANVAS_OVERVIEW_GO_DOWNLOAD' : 'CANVAS_OVERVIEW_CHECK_UPDATE') ?>"><?php echo JText::_( $thasnew ? 'CANVAS_OVERVIEW_GO_DOWNLOAD' : 'CANVAS_OVERVIEW_CHECK_UPDATE') ?></a>
					<?php endif ?>
				  <?php endif; ?>
				</div>
			  </div>
		
		<?php endif ?>

    </div>
  </div>

  <legend class="canvas-admin-form-legend"><?php echo JText::_('CANVAS_OVERVIEW_FRMWRK_INFO')?></legend>
  <div id="canvas-admin-framework-home" class="section">

    <div class="row-fluid">

      <div class="span8">
        <?php if (is_file (CANVAS_ADMIN_PATH.'/admin/frameworkInfo.php')): ?>
        <div class="template-info row-fluid">
          <?php include CANVAS_ADMIN_PATH.'/admin/frameworkInfo.php' ?>
        </div>
        <?php endif ?>
      </div>

      <div class="span4">
        <div id="canvas-admin-frmk-info" class="canvas-admin-overview-block clearfix">
          <h3><?php echo JText::_('CANVAS_OVERVIEW_FRMWRK_INFO')?></h3>
          <dl class="info">
            <dt><?php echo JText::_('CANVAS_OVERVIEW_NAME')?></dt>
            <dd><?php echo $fxml->name ?></dd>
            <dt><?php echo JText::_('CANVAS_OVERVIEW_VERSION')?></dt>
            <dd><?php echo $fxml->version ?></dd>
            <dt><?php echo JText::_('CANVAS_OVERVIEW_CREATE_DATE')?></dt>
            <dd><?php echo $fxml->creationDate ?></dd>
            <dt><?php echo JText::_('CANVAS_OVERVIEW_AUTHOR')?></dt>
            <dd><a href="<?php echo $fxml->authorUrl ?>" title="<?php echo $fxml->author ?>"><?php echo $fxml->author ?></a></dd>
          </dl>
        </div>
        <div class="canvas-admin-overview-block updater<?php echo $fhasnew ? ' outdated' : '' ?> clearfix">
          <h3><?php echo JText::sprintf($fhasnew ? 'CANVAS_OVERVIEW_FRMWRK_NEW' : 'CANVAS_OVERVIEW_FRMWRK_SAME', $fxml->name)?></h3>
          <p><?php echo $fhasnew ? JText::sprintf('CANVAS_OVERVIEW_FRMWRK_NEW_MSG', $cfversion, $fxml->name, $nfversion) : JText::sprintf('CANVAS_OVERVIEW_FRMWRK_SAME_MSG', $cfversion) ?></p>
          <?php if($hasperm): ?>
          <a class="btn" href="<?php JURI::base() ?>index.php?option=com_installer&view=update" class="canvascheck-framework" title="<?php echo JText::_( $fhasnew ? 'CANVAS_OVERVIEW_GO_DOWNLOAD' : 'CANVAS_OVERVIEW_CHECK_UPDATE') ?>"><?php echo JText::_( $fhasnew ? 'CANVAS_OVERVIEW_GO_DOWNLOAD' : 'CANVAS_OVERVIEW_CHECK_UPDATE') ?></a>
          <?php endif; ?>
        </div>
      </div>

    </div>

	</div>

</div>