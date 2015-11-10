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
class CANVASTemplateWidgetComponent extends CANVASTemplateWidget{

    public $name = 'component';
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
		ob_start();
		$path = CANVASPath::getPath('tpls/widgets/' . $this->name . '.php');
		if ($path) {
			include $path;
		} else {
			?>
			<!-- MAIN CONTENT -->			<div id="canvas-content" class="canvas-content">				<?php if($this->hasMessage()) : ?>				<jdoc:include type="message" />				<?php endif ?>				<jdoc:include type="component" />			</div>			<!-- //MAIN CONTENT -->
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
