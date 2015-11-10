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

class CANVASTemplateWidgetCanvas_Logo extends CANVASTemplateWidget{

    public $name = 'canvas_logo';
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
			
			?>				<div class="poweredby text-hide">					<a class="canvas-logo canvas-logo-color" href="http://themezart.com/canvas-framework" title="<?php echo JText::_('CANVAS_POWER_BY_TEXT') ?>"					   target="_blank" <?php echo method_exists('CANVAS', 'isHome') && CANVAS::isHome() ? '' : 'rel="nofollow"' ?>><?php echo JText::_('CANVAS_POWER_BY_HTML') ?></a>				</div>
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
