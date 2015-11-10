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

class CANVASTemplateWidgetToTop extends CANVASTemplateWidget{

    public $name = 'totop';
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
			<div class="canvas-back-to-top">	
				<!-- BACK TOP TOP BUTTON -->				<div id="back-to-top" data-spy="affix" data-offset-top="300" class="back-to-top hidden-xs hidden-sm affix-top">				  <button class="btn btn-primary" title="Back to Top"><i class="fa fa-caret-up"></i></button>				</div>				<script type="text/javascript">				(function($) {					// Back to top					$('#back-to-top').on('click', function(){						$("html, body").animate({scrollTop: 0}, 500);						return false;					});				})(jQuery);				</script>				<!-- BACK TO TOP BUTTON -->
			</div>
			<?php
		}
		
        return ob_get_clean();
		
    }
}
