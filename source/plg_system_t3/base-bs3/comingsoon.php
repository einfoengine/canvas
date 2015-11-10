<?php
/** 
 *------------------------------------------------------------------------------
 * @package       CANVAS Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 ThemezArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       ThemezArt
 *                & Purity III as base theme
 *                & comingsoon page inspired from Helix framework
 * @Link:         http://themezart.com/canvas-framework 
 *------------------------------------------------------------------------------
 */

// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );
$app = JFactory::getApplication();
$doc = JFactory::getDocument();

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

//get language and direction
$this->language = $doc->language;

$doc->setTitle('Coming Soon');

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', true);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/comingsoon.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
	<script type="text/javascript" src="<?php echo CANVAS_URL ?>/js/jquery.countdown.min.js"></script>
</head>
<body>
<jdoc:include type="message" />
	<div class="container">
		<div class="comingsoon_container span4">
			<!-- Container Goes here -->
			
			<div class="comingsoon-rocket">
				<img src="templates/<?php echo $this->template; ?>/images/comingsoon/comingsoon-rocket.png">
			</div>

			
		</div> <!-- Logo END -->

		<div id="comingsoon" class="span8 clearfix">
			<a id="logo" class="logo" href="<?php echo $this->baseurl; ?>"><?php echo htmlspecialchars($app->getCfg('sitename')); ?></a>

			<h2 class="comingsoon-title"><?php echo $this->params->get('comingsoon_title', 'Comming Soon'); ?></h2>
			<div class="comingsoon-watch">
				<img src="templates/<?php echo $this->template; ?>/images/comingsoon/cowndown_watch.png">
			</div>
			<div id="comingsoon-countdown" class="comingsoon-countdown"></div> <!-- Comingsoon coundown -->

			<div class="comingsoon-content">
				<?php echo $this->params->get('comingsoon_content', 'We are Coming Soon'); ?>
			</div>

			<?php
			
				if(	$this->params->get('facebook') ||
					$this->params->get('twitter') ||
					$this->params->get('gplus')
					){
						$output = '<div class="row-fluid"><div class="social-media span7">';
						$output .= '<ul class="social-icons">';

						if($this->params->get('facebook')){
							$output .= '<li><a target="_blank" href="' . $this->params->get('facebook') . '"><i class="icon-facebook"></i></a></li>';
						}

						if($this->params->get('twitter')){
							$output .= '<li><a target="_blank" href="' . $this->params->get('twitter') . '"><i class="icon-twitter"></i></a></li>';
						}

						if($this->params->get('gplus')){
							$output .= '<li><a target="_blank" href="' . $this->params->get('gplus') . '"><i class="icon-google-plus"></i></a></li>';
						}

						$output .='</ul>';
						$output .='</div>
									<div class="span5">
										<div class="home">
											<a href="#"><i class="icon-home"></i> <p>go to home</p></a>
										</div>
									</div>
						</div>';

					echo $output;
				}
			
			?>
		</div> <!-- END Coming Soon Container -->

	</div>

	<script type="text/javascript">

		jQuery(function($) {
			$('#comingsoon-countdown').countdown('<?php echo $this->params->get('comingsoon_year', '2015'); ?>/<?php echo $this->params->get('comingsoon_month', '12'); ?>/<?php echo $this->params->get('comingsoon_day', '31'); ?>', function(event) {
			    $(this).html(event.strftime('<div class="days"><span class="number">%-D</span><span class="string">%!D:<?php echo JText::_("DAY"); ?>,<?php echo JText::_("DAYS"); ?>;</span></div><div class="hours"><span class="number">%H</span><span class="string">%!H:<?php echo JText::_("HOUR"); ?>,<?php echo JText::_("HOURS"); ?>;</span></div><div class="minutes"><span class="number">%M</span><span class="string">%!M:<?php echo JText::_("MINUTE"); ?>,<?php echo JText::_("MINUTES"); ?>;</span></div><div class="seconds"><span class="number comming_soon_second">%S</span><span class="string">%!M:<?php echo JText::_("SECOND"); ?>,<?php echo JText::_("SECONDS"); ?>;</span></div>'));
			});
		});

	</script>

</body>
</html>