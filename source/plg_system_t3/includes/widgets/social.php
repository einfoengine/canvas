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

class CANVASTemplateWidgetSocial extends CANVASTemplateWidget{

    public $name = 'social';
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

        $twitter    = (string) $this->getParam('twitter');
        $facebook   = (string) $this->getParam('facebook');
        $gplus  	= (string) $this->getParam('gplus');
        $rss        = (string) $this->getParam('rss');

        ob_start();
		
		$path = CANVASPath::getPath('tpls/widgets/' . $this->name . '.php');
		if ($path) {
			include $path;
		} else {
		
        ?>
		<?php if( $twitter OR $facebook OR $gplus OR $rss ): ?>
		<div class="canvas-social-icons">	
			<ul class="social-icons list-inline clearfix">
				<?php if( !empty($twitter) ):?>
					<li class="twitter">
						<a href="https://twitter.com/<?php echo $twitter ;?>" rel="author" target="_blank">
							<i class="icon-twitter"></i>
						</a>
					</li>
				<?php endif;?>
				<?php if( !empty($facebook) ):?>
					<li class="facebook">
						<a href="https://facebook.com/<?php echo $facebook ;?>" rel="author" target="_blank">
							<i class="icon-facebook"></i>
						</a>
					</li>
				<?php endif; ?>
				<?php if( !empty($gplus) ):?>
					<li class="gplus">
						<a href="https://plus.google.com/<?php echo $gplus ;?>" rel="author" target="_blank">
							<i class="icon-google-plus" ></i>
						</a>
					</li>
				<?php endif; ?>
				<?php if( !empty($rss) ):?>
					<li class="rss">
						<a href="<?php echo $rss; ?>" target="_blank">
							<i class="icon-rss" ></i>
						</a>
					</li>
				<?php endif;?>
			</ul>
		</div>
		<?php endif;
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
