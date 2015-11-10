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

class CANVASTemplateWidgetLogo extends CANVASTemplateWidget{

    public $name = 'logo';
	public $params = null;
	
	public function __construct($params=null)
	{
		//parent::__construct();
		$this->params = $params;
	}
	
    public function renders()
    {
		
		// get params
		$sitename  = $this->params->get('sitename');
		$slogan    = $this->params->get('slogan', '');
		$logotype  = $this->params->get('logotype', 'text');
		$logoimage = $logotype == 'image' ? $this->params->get('logoimage', 'templates/' . CANVAS_TEMPLATE . '/images/logo.png') : '';
		$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', '') : false;

		if (!$sitename) {
			$sitename = JFactory::getConfig()->get('sitename');
		}

		ob_start();
		
		$path = CANVASPath::getPath('tpls/widgets/' . $this->name . '.php');
		if ($path) {
			include $path;
		} else {
			
			?>
				<!-- LOGO -->
				<div class="logo logo-<?php echo $logotype ?>">
					<div class="logo-<?php echo $logotype, ($logoimgsm ? ' logo-control' : '') ?>">
						<a href="<?php echo JURI::base(true) ?>" title="<?php echo strip_tags($sitename) ?>">
							<?php if($logotype == 'image'): ?>
								<img class="logo-img" src="<?php echo JURI::base(true) . '/' . $logoimage ?>" alt="<?php echo strip_tags($sitename) ?>" />
							<?php endif ?>

							<?php if($logoimgsm) : ?>
								<img class="logo-img-sm" src="<?php echo JURI::base(true) . '/' . $logoimgsm ?>" alt="<?php echo strip_tags($sitename) ?>" />
							<?php endif ?>
							<?php if($logotype == 'image') echo '<span class="hide-text">'; ?>
							
							<?php
							if (strpos($sitename,'::') !== false) {
								$sitenameTitle = explode('::',$sitename);
								$sitenameTitle = $sitenameTitle[0]."<span>".$sitenameTitle[1]."</span>";
							}else{
								$sitenameTitle = $sitename;
							}
							?>
							<?php echo $sitenameTitle ?>
							<?php if($logotype == 'image') echo '</span>';?>
						</a>
					</div>
				</div>
				<!-- //LOGO -->
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
