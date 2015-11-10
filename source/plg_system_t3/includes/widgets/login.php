<?php
/** 
 *------------------------------------------------------------------------------
 * @package       Canvas Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 ThemezArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       ThemezArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/canvasfw
 * @Link:         http://canvas-framework.org 
 *------------------------------------------------------------------------------
 */

//prevent direct access
defined('_JEXEC') or die;

class CANVASTemplateWidgetLogin extends CANVASTemplateWidget{

    public $name = 'login';
	public $params = null;
	
	public function __construct($params=null)
	{
		//parent::__construct();
		$this->params = $params;
	}
	
    public function renders()
    {
		
		
		ob_start();
		
		$path = CANVASPath::getPath('tpls/widgets/' . $this->name . '.php');
		if ($path) {
			include $path;
		} else {
			
			?>
				<!-- LOGIN -->
					 <!-- login trigger modal -->
							<?php 
							$user = JFactory::getUser(); 
							$type = (!$user->get('guest')) ? 'logout' : 'login';
							?>
						<div class="canvas-widget login-widget">	
							<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#canvasWidget<?php echo $type; ?>">
							  <i class="fa fa-sign-<?php echo ($type=='login') ? 'in' : 'out' ; ?>"></i>
							</button>
							<div class="modal fade" id="canvasWidgetlogin" tabindex="-1" role="dialog" aria-labelledby="canvasWidget<?php echo $type; ?>Label" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
											<h4 class="modal-title" id="canvasWidget<?php echo $type; ?>Label"><?php echo JText::_('JLOGIN'); ?></h4>
										</div>
										<div class="modal-body">
											<form class="form-horizontal" role="form" action="<?php echo JRoute::_('index.php', true); ?>" method="post">
											  <div class="form-group">
												<label for="inputUsername" class="col-sm-4 control-label">
													<?php echo JText::_('USER_NAME') ?>
													 <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>" title="<?php echo JText::_('FORGOT_YOUR_USERNAME'); ?>">
													<i class="fa fa-question-circle"></i>
													</a>
												</label>
												
												<div class="col-sm-8">
												  <input type="text" name="username" class="form-control" id="inputUsername" placeholder="Username">
												 
												</div>
											  </div>
											  <div class="form-group">
												<label for="inputUserPassword" class="col-sm-4 control-label"><?php echo JText::_('PASSWORD') ?>
													 <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" title="<?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?>">
													<i class="fa fa-question-circle"></i>
													</a>
												</label>
												<div class="col-sm-8">
												  <input type="password" class="form-control" id="inputUserPassword" name="password" placeholder="Password">
												</div>
											  </div>
											   <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
												  <div class="form-group">
													<div class="col-sm-offset-2 col-sm-10">
														<div class="checkbox">
															<label>
															  <input type="checkbox" name="remember" > <?php echo JText::_('REMEMBER_ME') ?>
															</label>
														</div>
													</div>
												</div>
												<?php endif; ?>
												
												 <div class="form-group">
												<div class="col-sm-offset-2 col-sm-10">
												  <input type="hidden" name="option" value="com_users" />
													<input type="hidden" name="task" value="user.login" />
													<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_('index.php', true)); ?>" />
													<?php echo JHtml::_('form.token'); ?>
													<button type="submit" class="btn btn-default"><?php echo JText::_('JLOGIN') ?></button>
													
													  <?php 
												require_once JPATH_SITE . '/components/com_users/helpers/route.php';
											  $usersConfig = JComponentHelper::getParams('com_users');
											  if ($usersConfig->get('allowUserRegistration')) : ?>
														<a class="btn btn-default" href="<?php echo JRoute::_('index.php?option=com_users&view=registration&Itemid=' . UsersHelperRoute::getRegistrationRoute()); ?>">
															<?php echo JText::_('JREGISTER'); ?> <span class="fa fa-arrow-right"></span></a>
												<?php endif; ?>
											
													
													
												</div>
											  </div>
											</form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('JCLOSE'); ?></button>
										</div>
									</div>
								</div>
							</div>
							
							<div class="modal fade" id="canvasWidgetlogout" tabindex="-1" role="dialog" aria-labelledby="canvasWidget<?php echo $type; ?>Label" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
											<h4 class="modal-title" id="canvasWidget<?php echo $type; ?>Label"><?php echo JText::_('JLOGOUT'); ?></h4>
										</div>
										<div class="modal-body">
											<form class="form-horizontal" role="form" action="<?php echo JRoute::_('index.php?option=com_users', true); ?>" method="post">
												<div class="form-group">
													
													<div class="col-md-12 logout-button">
														
														<button type="submit" class="btn btn-default"><?php echo JText::_('JLOGOUT') ?></button>
														
														<input type="hidden" name="option" value="com_users">
														<input type="hidden" name="task" value="user.logout">
														<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_('index.php', true)); ?>" />
														<?php echo JHtml::_('form.token'); ?>
													</div>
												</div>
											</form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('JCLOSE'); ?></button>
										</div>
									</div>
								</div>
							</div>
						</div>
				<!-- //LOGIN -->
			<?php
			
		}
	return ob_get_clean();

    }
	
	
	
	function getParam($name, $default = null) {
		if (!$this->params)
			return $default;
		return $this->params->get($name, $default);
	}
}
