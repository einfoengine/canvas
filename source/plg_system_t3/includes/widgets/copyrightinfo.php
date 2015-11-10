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


class CANVASTemplateWidgetCopyrightinfo extends CANVASTemplateWidget{

    public $name = 'copyrightinfo';
	public $params = null;
	public $doc = null;
	
	public function __construct($params=null)
	{
		//parent::__construct();
		$this->params = $params;
		$this->doc = JFactory::getDocument();
	}
	
    public function renders()
    {
        $copyrightinfo    = (string) $this->getParam('copyrightinfo');
		ob_start();
		
		$path = CANVASPath::getPath('tpls/widgets/' . $this->name . '.php');
		if ($path) {
			include $path;
		} else {
		
            ?>
		<div class="canvas-copyrightinfo">
            <span class="copyright">
                <?php echo ($copyrightinfo) ? $copyrightinfo :  JText::_('CANVAS_DEFAULT_COPYRIGHT'); ?>
            </span>
		</div>
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
?>
