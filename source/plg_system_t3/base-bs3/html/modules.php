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

defined('_JEXEC') or die('Restricted access');

/**
 * This is a file to add template specific chrome to module rendering.  To use it you would
 * set the style attribute for the given module(s) include in your template to use the style
 * for each given modChrome function.
 *
 * eg.  To render a module mod_test in the sliders style, you would use the following include:
 * <jdoc:include type="module" name="test" style="slider" />
 *
 * This gives template designers ultimate control over how modules are rendered.
 *
 * NOTICE: All chrome wrapping methods should be named: modChrome_{STYLE} and take the same
 * three arguments.
 */


/*
 * Default Module Chrome that has sematic markup and has best SEO support
 */
function modChrome_CANVASXhtml($module, &$params, &$attribs)
{ 
	if (strpos($params->get('moduleclass_sfx'),'row-feature') !== false) {
		$badge          = preg_match ('/badge/', $params->get('moduleclass_sfx'))? '<span class="badge">&nbsp;</span>' : '';
		$moduleTag      = htmlspecialchars($params->get('module_tag', 'div'));
		$headerTag      = 'header';
		$headerClass    = $params->get('header_class');
		$bootstrapSize  = $params->get('bootstrap_size');
		$moduleClass    = !empty($bootstrapSize) ? ' col-sm-' . (int) $bootstrapSize . '' : '';
		$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));
		$moduleClassSfx = str_replace('row-feature','',$moduleClassSfx);
		
		//prepare the module title
		if (strpos($module->title,'::') !== false) {
			$moduleTitle = explode('::',$module->title);
			$moduleTitle = "<h2>".$moduleTitle[0]."</h2><h3>".$moduleTitle[1]."</h3>";
		}else{
			$moduleTitle = "<h2>".$module->title."</h2>";
		}
		
		if (!empty ($module->content)) {
			$html = "<{$moduleTag} class=\"row-feature {$moduleClassSfx} {$moduleClass} canvas-content-set\" id=\"Mod{$module->id}\">" .
						"<div class=\"container-fluid\">" . $badge;

			if ($module->showtitle != 0) {
				$html .= "<{$headerTag} class=\"row row-feature-title {$headerClass}\">{$moduleTitle}</{$headerTag}>";
			}

			$html .= "<div class=\"row row-feature-ct\">{$module->content}</div></div></{$moduleTag}>";
			echo $html;
		}
	}else{
	
		$badge          = preg_match ('/badge/', $params->get('moduleclass_sfx'))? '<span class="badge">&nbsp;</span>' : '';
		$moduleTag      = htmlspecialchars($params->get('module_tag', 'div'));
		$headerTag      = htmlspecialchars($params->get('header_tag', 'h3'));
		$headerClass    = $params->get('header_class');
		$bootstrapSize  = $params->get('bootstrap_size');
		$moduleClass    = !empty($bootstrapSize) ? ' col-sm-' . (int) $bootstrapSize . '' : '';
		$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));
		//prepare the module title
		if (strpos($module->title,'::') !== false) {
			$moduleTitle = explode('::',$module->title);
			$moduleTitle = "<span>".$moduleTitle[0]."</span>".$moduleTitle[1];
		}else{
			$moduleTitle = $module->title;
		}
		
		if (!empty ($module->content)) {
			$html = "<{$moduleTag} class=\"canvas-module module{$moduleClassSfx} {$moduleClass} canvas-content-set\" id=\"Mod{$module->id}\">" .
						"<div class=\"module-inner\">" . $badge;

			if ($module->showtitle != 0) {
				$html .= "<{$headerTag} class=\"module-title {$headerClass}\">{$moduleTitle}</{$headerTag}>";
			}

			$html .= "<div class=\"module-ct\">{$module->content}</div></div></{$moduleTag}>";

			echo $html;
		}
	}
}


function modChrome_canvastabs($module, $params, $attribs)
{
	$area = isset($attribs['id']) ? (int) $attribs['id'] :'1';
	$area = 'area-'.$area;

	static $modulecount;
	static $modules;

	if ($modulecount < 1) {
		$modulecount = count(JModuleHelper::getModules($attribs['name']));
		$modules = array();
	}

	if ($modulecount == 1) {
		$temp = new stdClass;
		$temp->content = $module->content;
		$temp->title = $module->title;
		$temp->params = $module->params;
		$temp->id = $module->id;
		$modules[] = $temp;

		// list of moduletitles
		echo '<ul class="nav nav-tabs" id="tab'.$temp->id .'">';

		foreach($modules as $rendermodule) {
			//prepare the module title
			if (strpos($rendermodule->title,'::') !== false) {
				$moduleTitle = explode('::',$rendermodule->title);
				$moduleTitle = "<span>".$moduleTitle[0]."</span>".$moduleTitle[1];
			}else{
				$moduleTitle = $rendermodule->title;
			}
			echo '<li><a data-toggle="tab" href="#module-'.$rendermodule->id.'" >'.$moduleTitle.'</a></li>';
		}
		echo '</ul>';
		echo '<div class="tab-content">';
		$counter = 0;
		// modulecontent
		foreach($modules as $rendermodule) {
			$counter ++;

			echo '<div class="tab-pane  fade in" id="module-'.$rendermodule->id.'">';
			echo $rendermodule->content;
			
			echo '</div>';
		}
		echo '</div>';
		echo '<script type="text/javascript">';
		echo 'jQuery(document).ready(function(){';
			echo 'jQuery("#tab'.$temp->id.' a:first").tab("show")';
			echo '});';
		echo '</script>';
		$modulecount--;

	} else {
		$temp = new stdClass;
		$temp->content = $module->content;
		$temp->params = $module->params;
		$temp->title = $module->title;
		$temp->id = $module->id;
		$modules[] = $temp;
		$modulecount--;
	}
}


function modChrome_canvasslider($module, &$params, &$attribs)
{
	$badge = preg_match ('/badge/', $params->get('moduleclass_sfx'))?"<span class=\"badge\">&nbsp;</span>\n":"";
	$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;
	//prepare the module title
	if (strpos($module->title,'::') !== false) {
		$moduleTitle = explode('::',$module->title);
		$moduleTitle = "<span>".$moduleTitle[0]."</span>".$moduleTitle[1];
	}else{
		$moduleTitle = $module->title;
	}
	?>

	<div class="moduleslide-<?php echo $module->id ?> collapse-trigger collapsed" data-toggle="collapse" data-target="#slidecontent-<?php echo $module->id ?>">
		<h<?php echo $headerLevel; ?>><?php echo $moduleTitle; ?></h<?php echo $headerLevel; ?>>
	</div>

	<div id="slidecontent-<?php echo $module->id ?>" class="collapse-<?php echo $module->id ?> in"><?php echo $module->content; ?></div>

	<script type="text/javascript">;
	jQuery(document).ready(function(){;
		jQuery(".collapse-<?php echo $module->id ?>").collapse({toggle: 1});
	});
	</script>

	<?php 
} 


function modChrome_canvasmodal($module, &$params, &$attribs)
{

	$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;

	if (!empty ($module->content)) : 
		//prepare the module title
		if (strpos($module->title,'::') !== false) {
			$moduleTitle = explode('::',$module->title);
			$moduleTitle = "<span>".$moduleTitle[0]."</span>".$moduleTitle[1];
		}else{
			$moduleTitle = $module->title;
		}
	?>

	<div class="moduletable <?php echo $params->get('moduleclass_sfx'); ?> modalmodule">
		<div class="canvas-module-title">
			<a href="#module<?php echo $module->id ?>" role="button" class="btn" data-toggle="modal">
				<h<?php echo $headerLevel; ?>><?php echo $moduleTitle; ?></h<?php echo $headerLevel; ?>>
			</a>
		</div>
		<div id="module<?php echo $module->id ?>" class="modal hide fade" aria-hidden="true">
			<div class="modal-header">
				<h<?php echo $headerLevel; ?>><?php echo $moduleTitle; ?></h<?php echo $headerLevel; ?>>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

			</div>
			<div class="canvas-module-body">
				<?php echo $module->content; ?>
			</div>
		</div>
	</div>
	
	<?php endif;  
}


function modChrome_popover($module, &$params, &$attribs)
{
	$position = preg_match ('/left/', $params->get('moduleclass_sfx'))?"":"";
	$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;

	if (!empty ($module->content)) : 
		//prepare the module title
		if (strpos($module->title,'::') !== false) {
			$moduleTitle = explode('::',$module->title);
			$moduleTitle = "<span>".$moduleTitle[0]."</span>".$moduleTitle[1];
		}else{
			$moduleTitle = $module->title;
		}
	?>
	<div class="moduletable <?php echo $params->get('moduleclass_sfx'); ?> popovermodule">
		<a id="popover<?php echo $module->id ?>" href="#" rel="popover" data-placement="right" class="btn">
			<h<?php echo $headerLevel; ?>><?php echo $moduleTitle; ?></h<?php echo $headerLevel; ?>>
		</a>
		<div id="popover_content_wrapper-<?php echo $module->id ?>" style="display: none">
			<div><?php echo $module->content; ?></div>
		</div>
		
		<script type="text/javascript">;
		jQuery(document).ready(function(){

			jQuery("#popover<?php echo $module->id ?>").popover({
				html: true,
				content: function() {
					return jQuery('#popover_content_wrapper-<?php echo $module->id ?>').html();
				}
			}).click(function(e) {
				e.preventDefault();
			});
		});
		</script>
	</div>
	<?php endif;  
}

function modChrome_FeatureRow($module, &$params, &$attribs)
{ 
	$badge          = preg_match ('/badge/', $params->get('moduleclass_sfx'))? '<span class="badge">&nbsp;</span>' : '';
	$moduleTag      = htmlspecialchars($params->get('module_tag', 'div'));
	$headerTag      = 'header';
	$headerClass    = $params->get('header_class');
	$bootstrapSize  = $params->get('bootstrap_size');
	$moduleClass    = !empty($bootstrapSize) ? ' col-sm-' . (int) $bootstrapSize . '' : '';
	$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));
	
	//prepare the module title
	if (strpos($module->title,'::') !== false) {
		$moduleTitle = explode('::',$module->title);
		$moduleTitle = "<h2>".$moduleTitle[0]."</h2><h3>".$moduleTitle[1]."</h3>";
	}else{
		$moduleTitle = "<h2>".$module->title."</h2>";
	}
	
	if (!empty ($module->content)) {
		$html = "<{$moduleTag} class=\"row-feature {$moduleClassSfx} {$moduleClass} canvas-content-set\" id=\"Mod{$module->id}\">" .
					"<div class=\"container-fluid\">" . $badge;

		if ($module->showtitle != 0) {
			$html .= "<{$headerTag} class=\"row row-feature-title {$headerClass}\">{$moduleTitle}</{$headerTag}>";
		}

		$html .= "<div class=\"row row-feature-ct\">{$module->content}</div></div></{$moduleTag}>";
		echo $html;
	}
}