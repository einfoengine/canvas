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
//CANVAS::import('core/widget');

class CANVASTemplateWidgetFontResizer extends CANVASTemplateWidget{

    public $name = 'fontresizer';
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
		$fontresizer_selection = $this->getParam('fontresizer_selection','#canvas-content');
		$path = CANVASPath::getPath('tpls/widgets/' . $this->name . '.php');
		$this->doc->addScript(CANVAS_URL . '/js/jquery.jfontsize.js');
		if ($path) {
			include $path;
		} else {
			
			?>
			<section class="canvas-fontresizer">	
				<!-- BACK TO TOP -->
				 <div id="font-resizer" class="canvas-hide">
					<a class="icon-search-minus" id="canvas-fr-m">A-</a>
					<a class="icon-search" id="canvas-fr-d">A</a>
					<a class="icon-search-plus" id="canvas-fr-p">A+</a>
				</div>
				<script type="text/javascript">
				  //<![CDATA[
				  //jQuery('$selector');
				  (function($){
					$(document).ready(function(){
					  $('<?php echo $fontresizer_selection; ?>').jfontsize({ btnMinusClasseId: '#canvas-fr-m', btnDefaultClasseId: '#canvas-fr-d', btnPlusClasseId: '#canvas-fr-p' });
					});
				  })(jQuery);
				  //]]>
				</script>
			</section>
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
