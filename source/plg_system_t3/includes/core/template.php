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

// No direct access
defined('_JEXEC') or die();

CANVAS::import('extendable/extendable');

/**
 * CANVASTemplate class provides extended template tools used for CANVAS framework
 *
 * @package CANVAS
 */
class CANVASTemplate extends ObjectExtendable
{
	/**
	 * Define constants
	 */
	protected $maxgrid      = 12;
	protected $widthprefix  = 'span';
	protected $nonrspprefix = 'span';
	protected $spancls      = '/(\s*)span(\d+)(\s*)/';
	protected $responcls    = false;								//indicate this will use data-[device] property or not
	protected $rowfluidcls  = 'row-fluid';
	protected $defdv        = 'default';
	protected $devices      = array('default', 'wide', 'normal', 'xtablet', 'tablet', 'mobile');
	protected $maxcol       = array('default' => 6, 'wide' => 6, 'normal' => 6, 'xtablet' => 4, 'tablet' => 3, 'mobile' => 2);
	protected $minspan      = array('default' => 2, 'wide' => 2, 'normal' => 2, 'xtablet' => 3, 'tablet' => 4, 'mobile' => 6);
	protected $prefixes     = array('span');

	/**
	 * Current template instance
	 */
	public $_tpl = null;


	/**
	 * Store layout settings if exist
	 */
	protected $_layoutsettings = null;


	/**
	 * page class
	 */
	protected $_pageclass = array();


	/**
	 * Class constructor
	 *
	 * @param   object $template Current template instance
	 */
	public function __construct($template = null)
	{
		// merge the base theme information
		$this->maxgrid      = CANVAS_BASE_MAX_GRID;
		$this->widthprefix  = CANVAS_BASE_WIDTH_PREFIX;
		$this->nonrspprefix = CANVAS_BASE_NONRSP_WIDTH_PREFIX;
		$this->spancls      = CANVAS_BASE_WIDTH_REGEX;
		$this->responcls    = CANVAS_BASE_RSP_IN_CLASS;
		$this->rowfluidcls  = CANVAS_BASE_ROW_FLUID_PREFIX;
		$this->defdv        = CANVAS_BASE_DEFAULT_DEVICE;
		$this->devices      = json_decode(CANVAS_BASE_DEVICES, true);
		$this->maxcol       = json_decode(CANVAS_BASE_DV_MAXCOL, true);
		$this->minspan      = json_decode(CANVAS_BASE_DV_MINWIDTH, true);
		$this->prefixes     = json_decode(CANVAS_BASE_DV_PREFIX, true);

		// layout settings
		$this->_layoutsettings = new JRegistry;

		if ($template) {
			$this->_tpl = $template;
			$this->_extend(array($template));

			// merge layout setting
			$layout = JFactory::getApplication()->input->getCmd('canvaslayout', '');
			if (empty($layout)) {
				$layout = $template->params->get('mainlayout', 'default');
			}

			$fconfig = CANVASPath::getPath('etc/layout/' . $layout . '.ini');
			if (is_file($fconfig)) {
				jimport('joomla.filesystem.file');
				$this->_layoutsettings->loadString(JFile::read($fconfig), 'INI', array('processSections' => true));
			}
		}

		JDispatcher::getInstance()->trigger('onCANVASTplInit', array($this));
	}


	/**
	 * Get template parameter
	 * @param  string  $name     parameter name
	 * @param  mixed   $default  parameter default value
	 *
	 * @return  mixed  parameter value
	 */
	public function getParam($name, $default = null)
	{
		return $this->_tpl->params->get($name, $default);
	}


	/**
	 * Set template parameter. It will not store to database. This should not be used
	 * @param  string  $name   parameter name
	 * @param  mixed   $value  parameter value
	 *
	 * @return  null
	 */
	public function setParam($name, $value)
	{
		return $this->_tpl->params->set($name, $value);
	}


	/**
	 * Get current layout tpls
	 *
	 * @return string Layout name
	 */
	public function getLayout()
	{
		$input = JFactory::getApplication()->input;
		return $input->getCmd('tmpl') ? $input->getCmd('tmpl') : $this->getParam('mainlayout', 'default');
	}


	/**
	 * Get layout settings (Layout Tab)
	 * @param  string  $name     parameter name
	 * @param  mixed   $default  parameter default value
	 *
	 * @return string Layout name
	 */
	public function getLayoutSetting($name, $default = null)
	{
		return isset($this->_layoutsettings) ? $this->_layoutsettings->get($name, $default) : $default;
	}


	/**
	 * Load block content
	 * @param  string  $block  Block name - the real block is tpls/blocks/[block].php
	 * @param  array   $vars   information of block (used in template layout)
	 *
	 * @return string Block content
	 */
	function loadBlock($block, $vars = array())
	{
		$path = CANVASPath::getPath('tpls/blocks/' . $block . '.php');
		if ($path) {
			if($block == 'footer'){

				ob_start();
				include $path;
				$buffer = ob_get_contents();
				ob_end_clean();
				$buffer = CANVAS::fixCANVASLink($buffer);
				echo $buffer;

			} else {
				include $path;
			}
		} else {
			echo "<div class=\"error\">Block [$block] not found!</div>";
		}
	}


	/**
	 * Load block layout
	 *
	 * @param string &layout  Block name - the real block is tpls/[layout].php
	 *
	 * @return null
	 */
	function loadLayout($layout)
	{
		$path = CANVASPath::getPath('tpls/' . $layout . '.php', 'tpls/default.php');

		JDispatcher::getInstance()->trigger('onCANVASLoadLayout', array(&$path, $layout));

		if (is_file($path)) {

			ob_start();
			include $path;
			$buffer = ob_get_contents();
			ob_end_clean();
			if($this->responcls && !$this->getParam('responsive', 1)){
				//replace
				$buffer = preg_replace_callback('@class\s?=\s?(\'|")(([^\'"]*)(' . implode('|', $this->prefixes) . ')+([^\'"]*))(\'|")@m', array($this, 'responCls'), $buffer);
			}
			// check if exist megamenu renderer, place megamenurender on the top to render megamenu before render head
			if (preg_match_all ('/(<jdoc:include type="megamenu"[^>]*>)/i', $buffer, $match)) {
				foreach ($match[1] as $m) {
					$buffer = str_replace ('type="megamenu"', 'type="megamenurender"', $m).$buffer;
					CANVAS::import('renderer/megamenurender');
				}
			}
			//output
			echo $buffer;

		} else {
			echo "<div class=\"error\">Layout [$layout] or [Default] not found!</div>";
		}
	}

	/**
	 * Load spotlight block
	 * @param  string  $name       Name of the spotlight. Default will load positions base on this name: [name]-1, [name]-2...
	 * @param  string  $positions  The positions of spotlight, separated by comma
	 * @param  array   $info       Other information of spotlight
	 *
	 * @return null
	 * modified by themezart
	 */
	function spotlight($name, $positions, array $info = array()){
		$defdv  = $this->defdv;
		$defpos = preg_split('/\s*,\s*/', $positions);
		$vars   = is_array($info) ? $info : array();
		$cols   = count($defpos);
		$poss   = $defpos;

		$splparams = array();
		for ($i = 1; $i <= $this->maxgrid; $i++) {
			$param = $this->getLayoutSetting('block' . $i . '@' . $name);
			if (empty($param)) {
				break;
			} else {
				$splparams[] = $param;
			}
		}

		//we have configuration in setting file
		if (!empty($splparams)) {
			$poss = array();
			foreach ($splparams as $idx => $splparam) {
				$param = (object)$splparam;
				$poss[] = isset($param->position) ? $param->position : $defpos[$idx];
			}

			$cols = count($poss);
		}

		// check if there's any modules
		if (!$this->countModules(implode(' or ', $poss))) {
			// check if there's any widget's enabled
			if(!$this->checkWidgets($poss)){
				return;
			}
		}

		//empty - so we will use default configuration
		if (empty($splparams)) {
			//generate a optimize default width
			$default = $this->genWidth($defdv, $cols);

			foreach ($poss as $i => $pos) {
				//is there any configuration param
				$var = isset($vars[$pos]) ? $vars[$pos] : '';

				$param = new stdClass;
				$param->position = $pos;

				$param->$defdv = ($var && isset($var[$defdv])) ? $var[$defdv] : $this->widthprefix . $default[$i];
				if ($var) {
					foreach($this->devices as $device){
						if (isset($var[$device])) {
							$param->$device = $var[$device];
						}
					}

				}

				$splparams[$i] = $param;
			}
		}

		//build data
		$responsive = $this->getParam('responsive', 1);
		$datas      = array();
		
		//make the spotlight intelligent
		$makeBalance = true;
		$totalPositionblock = count($splparams);
		$totalPositionload = 0;
		
		foreach ($splparams as $key => $splparam) {
			if($this->countModules($splparam->position)){
				$totalPositionload++;
			}elseif($this->checkWidget($splparam->position)){
				$totalPositionload++;
			}else{
				unset( $splparams[ $key ] );
			}
		}
		
		if($totalPositionblock == $totalPositionload){
			$makeBalance = false;
		}else{
			$splparams = array_values( $splparams );
		}
		foreach ($splparams as $key => $splparam) {
			
			$param = (object)$splparam;
			
			$data = '';
			
			if($responsive){
				$tmp_device_data = '';
				$i = 0;
				foreach($this->devices as $device){
					
					if(isset($param->$device)){
						$prefix = $this->responcls ? ' ' : ' data-' . $device . '="';
						$posfix = $this->responcls ? '' : '"';

						if($makeBalance){
							
							if(CANVAS_CORE_TEMPLATE_BASE == 'base-bs3'){
								$widthclassprefix = 'col-'.$device.'-';
							}else{
								$widthclassprefix = 'span';
							}
							
							switch($totalPositionload){
								case 1:
										$tmp_device_data = $widthclassprefix.'12';
									break;
								case 2:
									if($name=='mainbody' and $param->position=='core-component'){
										if($device =='xs'){
											$tmp_device_data = $widthclassprefix.'12';
										}else{
											$tmp_device_data = $widthclassprefix.'9';
										}
									}else{
										if($device =='xs'){
											$tmp_device_data = $widthclassprefix.'12';
										}else{
											$tmp_device_data = $widthclassprefix.'6';
										}
									}
									break;
								case 3:
									if($device =='xs'){
										if($key == ($totalPositionload-1)){
											//$tmp_device_data = 'col-'.$device.'-'.'12';
											$tmp_device_data = $widthclassprefix.'12';
										}else{
											//$tmp_device_data = 'col-'.$device.'-'.'6';
											$tmp_device_data = $widthclassprefix.'6';
										}
									}else{
										//$tmp_device_data = 'col-'.$device.'-'.(12 / $totalPositionload);
										$tmp_device_data = $widthclassprefix.(12 / $totalPositionload);
									}
									break;
								case 4:
									if($device =='xs'){
										//$tmp_device_data = 'col-'.$device.'-6';
										$tmp_device_data = $widthclassprefix.'6';
									}else{
										//$tmp_device_data = 'col-'.$device.'-'.(12 / $totalPositionload);
										$tmp_device_data = $widthclassprefix .(12 / $totalPositionload);
									}
									break;
								case 5:
									if($device =='sm'){
										if($key == ($totalPositionload-1) or $key == ($totalPositionload-2)){
											//$tmp_device_data = 'col-'.$device.'-6';
											$tmp_device_data = $widthclassprefix.'6';
										}else{
											//$tmp_device_data = 'col-'.$device.'-4';
											$tmp_device_data = $widthclassprefix.'4';
										}
									}elseif($device =='xs'){
										if($key == ($totalPositionload-1)){
											//$tmp_device_data = 'col-'.$device.'-12';
											$tmp_device_data = $widthclassprefix.'12';
										}else{
											//$tmp_device_data = 'col-'.$device.'-6';
											$tmp_device_data = $widthclassprefix.'6';
										}
									}else{
										if($key == ($totalPositionload-1)){
											//$tmp_device_data = 'col-'.$device.'-4';
											$tmp_device_data = $widthclassprefix.'4';
										}else{
											//$tmp_device_data = 'col-'.$device.'-2';
											$tmp_device_data = $widthclassprefix.'2';
										}
									}
									
									break;
							}
							
							if(strpos(' ' . $param->$device . ' ', ' hidden ') !== false){
								$tmp_device_data = $tmp_device_data . ' hidden-' . $device . ' ';
							}
							$param->$device = $tmp_device_data;
						}else{
							
							if(strpos(' ' . $param->$device . ' ', ' hidden ') !== false){
								$param->$device = str_replace(' hidden ', ' hidden-' . $device . ' ', ' ' . $param->$device . ' ');
							}
						}
						
						$data .= $prefix . $param->$device . $posfix;
					}
					$i++;
				}
			} else {
				$data = isset($param->$defdv) ? ' ' . $param->$defdv : '';
				
				if($makeBalance){
					if(CANVAS_CORE_TEMPLATE_BASE == 'base-bs3'){
						$widthclassprefix = 'col-xs-';
					}else{
						$widthclassprefix = 'span';
					}
					
					$tmp_device_width = '';
					switch($totalPositionload){
						case 1:
						case 2:
						case 3:
						case 4:
							//$tmp_device_width = 'col-xs-'.(12 / $totalPositionload);
							$tmp_device_width = $widthclassprefix.(12 / $totalPositionload);
							
							break;
						case 5:
							if($key == ($totalPositionload-1)){
								//$tmp_device_width = 'col-xs-4';
								$tmp_device_width = $widthclassprefix.'4';
							}else{
								//$tmp_device_width = 'col-xs-2';
								$tmp_device_width = $widthclassprefix.'2';
							}							
							break;
					}
					
					$data = $tmp_device_width;
				}
				
				if($this->nonrspprefix && ($this->nonrspprefix != $this->widthprefix)){
					$data = str_replace($this->widthprefix, $this->nonrspprefix, $data);
				}
			}

			$datas[] = $data;
		}

		//pack to single variable
		$vars['name']      = $name;
		$vars['splparams'] = $splparams;
		$vars['datas']     = $datas;
		$vars['cols']      = $cols;
		
		//override module position's type
		$tabsPosition = $this->getParam('tabs_position','');
		$slider_position = $this->getParam('slider_position','');
		$modal_position = $this->getParam('modal_position','');
		$popover_position = $this->getParam('popover_position','');
		$raw_position = $this->getParam('raw_position','');
		$feature_position = $this->getParam('feature_position','');
		if(!empty($tabsPosition) or !empty($slider_position) or !empty($modal_position) or !empty($popover_position) or !empty($raw_position)or !empty($feature_position)){
			$cstyle = '';
			$end = '';
			foreach ($poss as $i => $pos) {
				$get = false;
				$cstyle = $cstyle;
				//first check for tabs
				if(!empty($tabsPosition)){
					
					foreach($tabsPosition as $j=>$tPosition){
						if($tPosition == $pos){
							$get = true;
							$cstyle .=$end.'canvastabs';
							break;
						}
					}
				}
				
				if(!empty($slider_position)){
					if($get === false){
						foreach($slider_position as $j=>$tPosition){
							
							if($tPosition == $pos){
								$get = true;
								$cstyle .=$end.'canvasslider';
								break;
							}
						}
					}
				}
				
				if(!empty($modal_position)){
					if($get === false){
						foreach($modal_position as $j=>$tPosition){
							
							if($tPosition == $pos){
								$get = true;
								$cstyle .=$end.'canvasmodal';
								break;
							}
						}
					}
				}
				
				if(!empty($popover_position)){
					if($get === false){
						foreach($popover_position as $j=>$tPosition){
							
							if($tPosition == $pos){
								$get = true;
								$cstyle .=$end.'popover';
								break;
							}
						}
					}
				}
				
				if(!empty($raw_position)){
					if($get === false){
						foreach($raw_position as $j=>$tPosition){
							
							if($tPosition == $pos){
								$get = true;
								$cstyle .=$end.'raw';
								break;
							}
						}
					}
				}
				
				if(!empty($feature_position)){
					if($get === false){
						foreach($feature_position as $j=>$tPosition){
							
							if($tPosition == $pos){
								$get = true;
								$cstyle .=$end.'FeatureRow';
								break;
							}
						}
					}
				}
				
				if($get === false){
					$cstyle .=$end.'CANVASXhtml';
				}
				$end = ', ';
			}
			$vars['style'] = $cstyle;
		}
		
		JDispatcher::getInstance()->trigger('onCANVASSpotlight', array(&$vars, $name, $positions));

		$this->loadBlock('spotlight', $vars);
	}


	/**
	 * Render megamenu markup
	 * @param  string  $menutype  The menutype to render
	 *
	 * @deprecated  Use <jdoc:include type="megamenu" name="$menutype" /> instead
	 */
	function megamenu($menutype)
	{
		echo "<jdoc:include type=\"megamenu\" name=\"{$menutype}\" />";
	}

	/**
	 * Get data property for layout - responsive layout
	 * @param  object   $layout  Layout configuration object
	 * @param  number   $col     Column number, start from 0
	 * @param  boolean  $array   Return array or string
	 *
	 * @return  mixed  Block content
	 */
	function getData($layout, $col, $array = false)
	{
		if ($array) {
			$data = array();
			foreach ($layout as $device => $width) {
				if (!isset ($width[$col]) || !$width[$col]) continue;
				$data[$device] = $width[$col];
			}

		} else {
			$data = '';
			foreach ($layout as $device => $width) {
				if (!isset ($width[$col]) || !$width[$col]) continue;
				$data .= " data-$device=\"{$width[$col]}\"";
			}
		}

		return $data;
	}


	/**
	 * Get layout column class
	 * @param  object  $layout  Layout configuration object
	 * @param  number  $col     Column number, start from 0
	 *
	 * @return string  Block content
	 */
	function getClass($layout, $col)
	{
		$defdv = $this->defdv;

		if($this->responcls){
			$result     = '';
			$responsive = $this->getParam('responsive', 1);

			if($responsive){
				foreach ($layout as $width) {
					if (!isset ($width[$col]) || !$width[$col]) {
						continue;
					}

					$result .= ' ' . $width[$col];
				}

			} else {
				//remove all width classes
				$width   = $this->maxgrid;
				$clayout = isset($layout->$defdv) ? $layout->$defdv : false;

				if($clayout && !empty($clayout[$col])){
					$defcls = $clayout[$col];
					if(preg_match($this->spancls, $defcls, $match)){
						$width = array_pop(array_filter($match, 'is_numeric'));
						$width = ($width ? $width : $this->maxgrid);
					}
				}

				$result = ' ' . $this->nonrspprefix . $width;
			}

			return $result;

		} else {

			$width = $layout->$defdv;
			if (!isset ($width[$col]) || !$width[$col]){
				return '';
			}

			return $width[$col];
		}
	}

	/**
	 * Get layout column class
	 * @param  object  $layout  Layout configuration object
	 * @param  number  $col     Column number, start from 0
	 *
	 * @return string  Block content
	 */
	function responCls($class)
	{
		$result = $class[2];
		$queue  = array();

		//remove all width classes
		foreach ($this->prefixes as $prefix) {
			if($result && preg_match_all('@' . preg_quote($prefix) . '[^\s]*@', $result, $match)){
				$result = preg_replace('@' . preg_quote($prefix) . '[^\s]*@', ' ', $result);

				foreach ($match[0] as $m) {
					$parts = preg_split('@(\d+)@', $m, -1, PREG_SPLIT_DELIM_CAPTURE);
					$parts[0] = str_replace($prefix, $this->nonrspprefix, $parts[0]);
					if(!isset($queue[$parts[0]])){
						$queue[$parts[0]] = $parts[1];
					}
				}
			}
		}

		if(!empty($queue)){
			$result = trim($result); //would be better than preg_replace ?
			foreach ($queue as $key => $value) {
				$result .= ' ' . $key . $value;
			}
		}

		return 'class="' . trim($result) . '"';
	}


	/**
	 * Add page class
	 */
	function addPageClass($class)
	{
		$this->_pageclass = array_merge($this->_pageclass, (array)($class));
	}

	/**
	 * Add page class
	 *
	 * @deprecated
	 */
	function addBodyClass($class)
	{
		$this->_pageclass = array_merge($this->_pageclass, (array)($class));
	}

	/**
	 * get page class
	 */
	function getPageClass()
	{
		return $this->_pageclass;
	}


	/**
	 * Render page class
	 *
	 * @deprecated  Use <jdoc:include type="pageclass" /> instead
	 */
	function bodyClass()
	{
		$input = JFactory::getApplication()->input;

		if ($input->getCmd('option', '')) {
			$this->_pageclass[] = $input->getCmd('option', '');
		}
		if ($input->getCmd('view', '')) {
			$this->_pageclass[] = 'view-' . $input->getCmd('view', '');
		}
		if ($input->getCmd('layout', '')) {
			$this->_pageclass[] = 'layout-' . $input->getCmd('layout', '');
		}
		if ($input->getCmd('task', '')) {
			$this->_pageclass[] = 'task-' . $input->getCmd('task', '');
		}
		if ($input->getCmd('Itemid', '')) {
			$this->_pageclass[] = 'itemid-' . $input->getCmd('Itemid', '');
		}

		$menu = JFactory::getApplication()->getMenu();
		if ($menu) {
			$active = $menu->getActive();
			$default = $menu->getDefault();

			if ($active) {
				if ($default && $active->id == $default->id) {
					$this->_pageclass[] = 'home';
				}

				if ($active->params && $active->params->get('pageclass_sfx')) {
					$this->_pageclass[] = $active->params->get('pageclass_sfx');
				}
			}
		}

		// hover trigger for megamenu
		if ($this->getParam('navigation_trigger', 'hover') == 'hover') {
			$this->_pageclass[] = 'mm-hover';
		}

		$this->_pageclass[] = 'j' . str_replace('.', '', (number_format((float)JVERSION, 1, '.', '')));
		$this->_pageclass = array_unique($this->_pageclass);

		JDispatcher::getInstance()->trigger('onCANVASBodyClass', array(&$this->_pageclass));

		echo implode(' ', $this->_pageclass);
	}


	/**
	 * Render snippet
	 *
	 * @return null
	 */
	function snippet()
	{
		$places   = array();
		$contents = array();

		if (($openhead = $this->getParam('snippet_open_head', ''))) {
			$places[] = '<head>';	//not sure that any attritube can be place in head open tag, profile is not support in html5
			$contents[] = "<head>\n" . $openhead;
		}
		if (($closehead = $this->getParam('snippet_close_head', ''))) {
			$places[] = '</head>';
			$contents[] = $closehead . "\n</head>";
		}
		if (($openbody = $this->getParam('snippet_open_body', ''))) {
			$body = JResponse::getBody();

			if(strpos($body, '<body>') !== false){
				$places[] = '<body>';
				$contents[] = "<body>\n" . $openbody;
			} else {	//in case the body has other attribute	
				$body = preg_replace('@<body[^>]*?>@msU', "$0\n" . $openbody, $body);
				JResponse::setBody($body);
			}
		}

		// append modules in debug position
		if ($this->getParam('snippet_debug', 0) && $this->countModules('debug')) {
			$places[] = '</body>';
			$contents[] = '<div class="canvas-debug">' . $this->getBuffer('modules', 'debug') . "</div>\n</body>";
		}

		if (($closebody = $this->getParam('snippet_close_body', ''))) {
			$places[] = '</body>';
			$contents[] = $closebody . "\n</body>";
		}
		
		//add collaps menu
		if ($this->getParam('navigation_collapse_enable')){
				$places[] = '</body>';
				$contents[] = '
				<!-- NAVBAR BOTTOM WRAPPER -->
				<div class="navbar-bottom-wrapper">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".canvas-navbar-collapse">
						<i class="fa fa-bars icon-bars"></i>
					</button>
					<nav class="canvas-navbar-collapse navbar-collapse collapse"></nav>
				</div>
				<!-- //NAVBAR BOTTOM WRAPPER -->
				</body>';
		}
		

		if (count($places)) {
			$body = JResponse::getBody();
			$body = str_replace($places, $contents, $body);

			JResponse::setBody($body);
		}
	}


	/**
	 * Wrap of document countModules function, get position from configuration before calculate
	 * @param   string  $positions  Positions string
	 * @return  boolean  The position key is available or not
	 */
	function countModules($positions)
	{
		$pos = $this->getPosname($positions);
		return $this->_tpl && method_exists($this->_tpl, 'countModules') ? $this->_tpl->countModules($pos) : 0;
	}


	/**
	 * Wrap of document countModules function, used to detect if a spotlight is available to render or not
	 * @param  string  $name       The spotlight name
	 * @param  string  $positions  The positions name separated by comma
	 *
	 * @return  boolean  The spotlight is available or not
	 */
	function checkSpotlight($name, $positions)
	{
		$poss = array();
		
		for ($i = 1; $i <= $this->maxgrid; $i++){
			$param = $this->getLayoutSetting('block' . $i . '@' . $name);
			if (empty($param)) {
				break;
			} else {
				$param = (object)$param;
				$poss[] = isset($param->position) ? $param->position : '';
			}
		}

		if (empty($poss)) {
			$poss = preg_split('/\s*,\s*/', $positions);
		}

		return $this->_tpl && method_exists($this->_tpl, 'countModules') ? ($this->_tpl->countModules(implode(' or ', $poss)) or $this->checkWidgets($poss)) : 0;
	}


	/**
	 * Check system messages
	 *
	 * @return  boolean  The system message queue has any message or not
	 */
	function hasMessage()
	{
		// Get the message queue
		$app      = JFactory::getApplication();
		$input    =  $app->input;

		if($input->getCmd('option') == 'com_content'){
			$messages = $app->getMessageQueue();

			return !empty($messages);
		}

		return true;
	}


	/**
	 * Get mapped position name
	 * @param  string  $condition  The position key(name)
	 *
	 * @return  string  The mapped position
	 */
	function getPosname($condition)
	{
		$operators = '(,|\+|\-|\*|\/|==|\!=|\<\>|\<|\>|\<=|\>=|and|or|xor)';
		$words = preg_split('# ' . $operators . ' #', $condition, null, PREG_SPLIT_DELIM_CAPTURE);
		for ($i = 0, $n = count($words); $i < $n; $i += 2) {
			// odd parts (modules)
			$name = strtolower($words[$i]);
			$words[$i] = $this->getLayoutSetting($name, $name);
		}

		$poss = '';
		foreach ($words as $word) {
			if (is_string($word)) {
				$poss .= ' ' . $word;
			} else {
				$poss .= ' ' . (is_array($word) ? $word['position'] : (isset($word->position) ? $word->position : $name));
			}
		}
		$poss = trim($poss);

		return $poss;
	}


	/**
	 * Render position name
	 * @param  string  $condition  The key used in block
	 *
	 * @return  null
	 */
	function posname($condition)
	{
		echo $this->getPosname($condition);
	}

	/**
	 * Alias of posname
	 * @param  string  $condition
	 * @return null
	 */
	function _p($condition)
	{
		$this->posname($condition);
	}


	/**
	 * Add position additional class (show/hide)
	 * @param  string  $name  The position name
	 * @param  array   $cls   The responsive array style for responsive layout [lg, md, ...]
	 *
	 * @return null
	 */
	function _c($name, $cls = array())
	{
		$data = '';
		$param = $this->getLayoutSetting($name, '');

		if (empty($param)) {
			if (is_string($cls)) {
				$data = ' ' . $cls;
			} else if (is_array($cls)) {
				$param = (object)$cls;
			}
		}

		if (!empty($param)) {

			foreach ($this->maxcol as $device => $span) {
				//convert hidden class
				if(!empty($param->$device) && strpos(' ' . $param->$device . ' ', ' hidden ') !== false){
					$param->$device = str_replace(' hidden ', ' hidden-' . $device . ' ', ' ' . $param->$device . ' ');
				}

				if(!empty($param->$device)){
					$prefix = $this->responcls ? ' ' : ' data-' . $device . '="';
					$posfix = $this->responcls ? '' : '"';
					$data .= $prefix . trim($param->$device) . $posfix;
				}
			}

			$defdv = $this->defdv;
			if(!$this->responcls && !empty($data)){
				$data = (isset($param->$defdv) ? ' ' . $param->$defdv : '') . ' canvasrespon"' . substr($data, 0, strrpos($data, '"'));
			}
		}

		echo $data;
	}

	/**
	 * Add current template css base on template setting.
	 * @param $name           string  file name, without .css
	 * @param $addresponsive  bool    add responsive part or not
	 *
	 * @return string Block content
	 */
	function addCss($name, $addresponsive = true)
	{
		$devmode    = $this->getParam('devmode', 0);
		$themermode = $this->getParam('themermode', 1);
		$responsive = $addresponsive && !$this->responcls ? $this->getParam('responsive', 1) : false;

		if (($devmode || ($themermode && defined('CANVAS_THEMER'))) && ($url = CANVASPath::getUrl('less/' . $name . '.less', '', true, false))) {
			CANVAS::import('core/less');
			CANVASLess::addStylesheet($url);
		} else {
			$this->addStyleSheet(CANVAS_TEMPLATE_URL . '/css/' . $name . '.css');
		}

		if ($responsive && !$this->responcls) {
			$this->addCss($name . '-responsive', false);
		}
	}

	/**
	 * Add CANVAS basic head
	 *
	 * @return  null
	 */
	function addHead()
	{

		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$input = $app->input;

		$responsive = $this->getParam('responsive', 1);
		$navtype    = $this->getParam('navigation_type', 'joomla');
		$navtrigger = $this->getParam('navigation_trigger', 'hover');
		$offcanvas  = $this->getParam('navigation_collapse_offcanvas', 0) || $this->getParam('addon_offcanvas_enable', 0);
		$legacycss  = $this->getParam('legacy_css', 0);
		$frontedit  = in_array($input->getCmd('option'), array('com_media', 'com_config'))	//com_media or com_config
			|| in_array($input->getCmd('layout'), array('edit'))								//edit layout
			|| (version_compare(JVERSION, '3.2', 'ge') && $user->id && $app->get('frontediting', 1) &&
				($user->authorise('core.edit', 'com_modules') || $user->authorise('core.edit', 'com_menus')));	//frontediting

		// LEGACY COMPATIBLE
		if($legacycss){
			$this->addCss('legacy-grid');	//legacy grid
			$this->addStyleSheet(CANVAS_URL . '/fonts/font-awesome/css/font-awesome' . ($this->getParam('devmode', 0) ? '' : '.min') . '.css'); //font awesome 3
		}

		// FRONTEND EDITING
		if($frontedit){
			$this->addCss('frontend-edit');
		}

		// BOOTSTRAP CSS
		$this->addCss('bootstrap', false);

		// TEMPLATE CSS
		$this->addCss('template', false);

		if (!$responsive && $this->responcls) {
			// not responsive for BS3
			$this->addCss('non-responsive'); //no responsive

			$nonrespwidth = $this->getParam('non_responsive_width', '970px');
			if(preg_match('/^(-?\d*\.?\d+)(px|%|em|rem|pc|ex|in|deg|s|ms|pt|cm|mm|rad|grad|turn)?/', $nonrespwidth, $match)){
				$nonrespwidth = $match[1] . (!empty($match[2]) ? $match[2] : 'px');
			}
			$this->addStyleDeclaration('.container {width: ' . $nonrespwidth . ' !important;} .canvas-wrapper, .wrap {min-width: ' . $nonrespwidth . ' !important;}');

		} else if($responsive && !$this->responcls){
			// responsive for BS2
			// BOOTSTRAP RESPONSIVE CSS
			$this->addCss('bootstrap-responsive');

			// RESPONSIVE CSS
			$this->addCss('template-responsive');
		}

		// add core megamenu.css in plugin
		// deprecated - will extend the core style into template megamenu.less & megamenu-responsive.less
		// to use variable overridden in template
		if($navtype == 'megamenu'){

			// If the template does not overwrite megamenu.less & megamenu-responsive.less
			// We check and included predefined megamenu style in base
			if(!is_file(CANVAS_TEMPLATE_PATH . '/less/megamenu.less')){
				$this->addStyleSheet(CANVAS_URL . '/css/megamenu.css');

				if ($responsive && !$this->responcls){
					$this->addStyleSheet(CANVAS_URL . '/css/megamenu-responsive.css');
				}
			}

			// megamenu.css override in template
			$this->addCss('megamenu');
		}

		// Add scripts
		if (version_compare(JVERSION, '3.0', 'ge')) {
			JHtml::_('jquery.framework');
		} else {
			$scripts = @$this->_scripts;
			$jqueryIncluded = 0;
			if (is_array($scripts) && count($scripts)) {
				//simple detect for jquery library. It will work for most of cases
				$pattern = '/(^|\/)jquery([-_]*\d+(\.\d+)+)?(\.min)?\.js/i';
				foreach ($scripts as $script => $opts) {
					if (preg_match($pattern, $script)) {
						$jqueryIncluded = 1;
						break;
					}
				}
			}

			if (!$jqueryIncluded) {
				$this->addScript(CANVAS_URL . '/js/jquery-1.8.3' . ($this->getParam('devmode', 0) ? '' : '.min') . '.js');
				$this->addScript(CANVAS_URL . '/js/jquery.noconflict.js');
			}
		}

		define('JQUERY_INCLUED', 1);


		// As joomla 3.0 bootstrap is buggy, we will not use it
		$this->addScript(CANVAS_URL . '/bootstrap/js/bootstrap.js');
		// a jquery tap plugin
		$this->addScript(CANVAS_URL . '/js/jquery.tap.min.js');

		// add css/js for off-canvas
		if ($offcanvas && ($this->responcls || $responsive)) {
			$this->addCss('off-canvas', false);
			$this->addScript(CANVAS_URL . '/js/off-canvas.js');
		}

		$this->addScript(CANVAS_URL . '/js/script.js');

		//menu control script
		if ($navtrigger == 'hover') {
			$this->addPageClass('mm-hover');
		}

		if($navtrigger == 'hover' || $this->responcls){
			$this->addScript(CANVAS_URL . '/js/menu.js');
		}

		//reponsive script
		if ($responsive && !$this->responcls) {
			$this->addScript(CANVAS_URL . '/js/responsive.js');
		}

		//some helper javascript functions for frontend edit
		if($frontedit){
			$this->addScript(CANVAS_URL . '/js/frontend-edit.js');
		}

		//check and add additional assets
		$this->addExtraAssets();
	}

	/**
	 * Update head - detect if devmode or themermode is enabled and less file existed, use less file instead of css
	 * We also detect and update jQuery, Bootstrap to use CANVAS assets
	 *
	 * @return  null
	 */
	function updateHead()
	{
		//state parameters
		$devmode    = $this->getParam('devmode', 0);
		$themermode = $this->getParam('themermode', 1) && defined('CANVAS_THEMER');
		$theme      = $this->getParam('theme', '');
		$minify     = $this->getParam('minify', 0);
		$minifyjs   = $this->getParam('minify_js', 0);

		// As Joomla 3.0 bootstrap is buggy, we will not use it
		// We also prevent both Joomla bootstrap and CANVAS bootsrap are loaded
		// And upgrade jquery as our Framework require jquery 1.7+ if we are loading jquery from google
		$doc = JFactory::getDocument();
		$scripts = array();

		if (version_compare(JVERSION, '3.0', 'ge')) {
			$canvasbootstrap = false;
			$jabootstrap = false;

			foreach ($doc->_scripts as $url => $script) {
				if (strpos($url, CANVAS_URL . '/bootstrap/js/bootstrap.js') !== false) {
					$canvasbootstrap = true;
					if ($jabootstrap) { //we already have the Joomla bootstrap and we also replace to CANVAS bootstrap
						continue;
					}
				}

				if (preg_match('@media/jui/js/bootstrap(.min)?.js@', $url)) {
					if ($canvasbootstrap) { //we have CANVAS bootstrap, no need to add Joomla bootstrap
						continue;
					} else {
						$scripts[CANVAS_URL . '/bootstrap/js/bootstrap.js'] = $script;
					}

					$jabootstrap = true;
				} else {
					$scripts[$url] = $script;
				}
			}

			$doc->_scripts = $scripts;
			$scripts = array();
		}

		// VIRTUE MART / JSHOPPING compatible
		foreach ($doc->_scripts as $url => $script) {
			$replace = false;

			if ((strpos($url, '//ajax.googleapis.com/ajax/libs/jquery/') !== false &&
					preg_match_all('@/jquery/(\d+(\.\d+)*)?/@msU', $url, $jqver)) ||
				(preg_match_all('@(^|\/)jquery([-_]*(\d+(\.\d+)+))?(\.min)?\.js@i', $url, $jqver))) {

				$idx = strpos($url, '//ajax.googleapis.com/ajax/libs/jquery/') !== false ? 1 : 3;

				if (is_array($jqver) && isset($jqver[$idx]) && isset($jqver[$idx][0])) {
					$jqver = explode('.', $jqver[$idx][0]);

					if (isset($jqver[0]) && (int)$jqver[0] <= 1 && isset($jqver[1]) && (int)$jqver[1] < 7) {
						$scripts[CANVAS_URL . '/js/jquery-1.8.3' . ($devmode ? '' : '.min') . '.js'] = $script;
						$replace = true;
					}
				}
			}

			if (!$replace) {
				$scripts[$url] = $script;
			}
		}

		$doc->_scripts = $scripts;
		// end update javascript

		// detect RTL
		$dir    = $doc->direction;
		$is_rtl = ($dir == 'rtl');

		//Update css/less based on devmode and themermode
		$root        = JURI::root(true);
		$current     = JURI::current();
		$regex       = '@' . preg_quote(CANVAS_TEMPLATE_REL) . '/css/(rtl/)?(.*)\.css((\?|\#).*)?$@i';
		$stylesheets = array();
		foreach ($doc->_styleSheets as $url => $css) {
			// detect if this css in template css
			if (preg_match($regex, $url, $match)) {
				$fname = $match[2];

				if (($devmode || $themermode) && is_file(CANVAS_TEMPLATE_PATH . '/less/' . $fname . '.less')) {
					if ($themermode) {
						$newurl = CANVAS_TEMPLATE_URL . '/less/' . $fname . '.less';
						$css['mime'] = 'text/less';
					} else {
						CANVAS::import('core/less');
						$newurl = CANVASLess::buildCss(CANVASPath::cleanPath('templates/'.CANVAS_TEMPLATE.'/less/'.$fname.'.less'), true);
					}
					$stylesheets[$newurl] = $css;
				} else {
					$uri = null;
					// detect css available base on direction & theme
					if ($is_rtl && $theme) {
						$uri = CANVASPath::getUrl ('css/rtl/themes' . $theme . '/' . $fname . '.css');
					}
					if (!$uri && $is_rtl) {
						$uri = CANVASPath::getUrl ('css/rtl/' . $fname . '.css');
					}
					if (!$uri && $theme) {
						$uri = CANVASPath::getUrl ('css/themes/' . $theme . '/' . $fname . '.css');
					}
					if (!$uri) {
						$uri = CANVASPath::getUrl ('css/' . $fname . '.css');
					}

					if ($uri) {
						$stylesheets[$uri] = $css;
					}
				}
				continue;
			}

			$stylesheets[$url] = $css;
		}

		// update back
		$doc->_styleSheets = $stylesheets;

		//only check for minify if devmode is disabled
		if (!$devmode && ($minify || $minifyjs)) {
			CANVAS::import('core/minify');
			if($minify){
				CANVASMinify::optimizecss($this);
			}
			if($minifyjs){
				CANVASMinify::optimizejs($this);
			}
		}
	}

	/**
	 * Add some other condition assets (css, javascript). Use to parse /etc/assets.xml
	 *
	 * @return  null
	 */
	function addExtraAssets()
	{
		$base = JURI::base(true);
		$regurl = '#(http|https)://([a-zA-Z0-9.]|%[0-9A-Za-z]|/|:[0-9]?)*#iu';

		$afiles = CANVASPath::getAllPath('etc/assets.xml');
		foreach ($afiles as $afile) {
			if (is_file($afile)) {
				//load xml
				$axml = JFactory::getXML($afile);

				//process if exist
				if ($axml) {
					foreach ($axml as $node => $nodevalue) {
						//ignore others node
						if ($node == 'stylesheets' || $node == 'scripts') {
							foreach ($nodevalue->file as $file) {
								$compatible = (string) $file['compatible'];
								if ($compatible) {
									$parts = explode(' ', $compatible);
									$operator = '='; //exact equal to
									$operand = $parts[0];
									if (count($parts) == 2) {
										$operator = $parts[0];
										$operand = $parts[1];
									}

									//compare with Joomla version
									if (!version_compare(JVERSION, $operand, $operator)) {
										continue;
									}
								}

								$url = (string)$file;
								if (substr($url, 0, 2) == '//') { //external link

								} else if ($url[0] == '/') { //absolute link from based folder
									$url = is_file(JPATH_ROOT . $url) ? $base . $url : false;
								} else if (!preg_match($regurl, $url)) { //not match a full url -> sure internal link
									$url = CANVASPath::getUrl($url); // so get it
								}

								if ($url) {
									if ($node == 'stylesheets') {
										$type = $file['type'] ? (string) $file['type'] : 'text/css';
										$media = $file['media'] ? (string) $file['media'] : null;
										$this->addStylesheet($url, $type, $media);
									} else {
										$type = $file['type'] ? (string) $file['type'] : 'text/javascript';
										$defer = $file['defer'] ? (bool) $file['defer'] : false;
										$async = $file['async'] ? (bool) $file['async'] : false;
										$this->addScript($url, $type, $defer, $async);
									}
								}
							}
						}
					}
				}
			}
		}

		// template extended styles
		$aparams = $this->_tpl->params->toArray();
		$extras = array();
		$itemid = JFactory::getApplication()->input->get ('Itemid');
		foreach ($aparams as $name => $value) {
			if (preg_match ('/^theme_extras_(.+)$/', $name, $m)) {
				$extras[$m[1]] = $value;
			}
		}
		if (count ($extras)) {
			foreach ($extras as $extra => $pages) {
				if (!is_array($pages) || !count($pages) || in_array (0, $pages)) {
					continue; // disabled
				}
				if (in_array (-1, $pages) || in_array($itemid, $pages)) {
					// load this style
					$this->addCss ('extras/'.$extra);
				}
			}
		}
	
		// load addon lib scripts/styles
		// -------------------------------------------
		// load holder.js
		if($this->params->get('load_holder_js',1)){
			$this->addScript(CANVAS_URL . '/js/holder.js');
		}
		// load wow.js
		if($this->params->get('load_wow_js',1)){
			$this->addScript(CANVAS_URL . '/js/wow.min.js');
		}
		// load animate.css
		if($this->params->get('load_animate_css',1)){
			$this->addStyleSheet(CANVAS_URL . '/css/animate.min.css');
		}
		
	}


	/**
	 * Turn a param to DOM style value
	 * @param   string   $style  The style property
	 * @param   string   $pname  The parameter name
	 * @param   boolean  $isurl  Is url?
	 *
	 * @return  string   The css style string
	 * @deprecated   This function is no longer used in CANVAS
	 */
	function paramToStyle($style, $pname = '', $isurl = false)
	{
		if ($pname == '') {
			$pname = $style;
		}
		$param = $this->getParam($pname);

		if (!$param) return '';

		if ($isurl) {
			return "$style:url($param);";
		} else {
			return "$style:$param" . (is_numeric($param) ? 'px;' : ';');
		}
	}

	/**
	 * Internal function, auto generate optimize width in a row fit to 12 grid
	 * @param  number  $numpos  number columns in row
	 *
	 * @return  array  The span width layout columns for a row
	 */
	function fitWidth($numpos)
	{
		$result = array();
		$avg = floor($this->maxgrid / $numpos);
		$sum = 0;

		for ($i = 0; $i < $numpos - 1; $i++) {
			$result[] = $avg;
			$sum += $avg;
		}

		$result[] = $this->maxgrid - $sum;

		return $result;
	}

	/**
	 * Internal function, generate auto calculate width
	 * @param   string   $layout  The target layout
	 * @param   number   $numpos  Number of columns (block)
	 *
	 * @return  array  The span width layout columns
	 */
	function genWidth($layout, $numpos)
	{
		$cminspan = $this->minspan[$layout];
		$total = $cminspan * $numpos;

		if ($total < $this->maxgrid) {
			return $this->fitWidth($numpos);
		} else {
			$result = array();
			$rows = ceil($total / $this->maxgrid);
			$cols = ceil($numpos / $rows);

			for ($i = 0; $i < $rows - 1; $i++) {
				$result = array_merge($result, $this->fitWidth($cols));
				$numpos -= $cols;
			}

			$result = array_merge($result, $this->fitWidth($numpos));
		}

		return $result;
	}
	
	/**
	 * internal function
	 * $positions array
	 * match the components positions's with spotlight position
	 *
	 * return true or false
	 * added by themezart
	 */
	static $comPosition = 'core-component';
	/*
	* core com position
	*/
	function checkCom($positions){

		//check if its already checked and has the component position
		$positionMatch = array_intersect($comPosition, $positions);
		if(count($positionMatch)>0)
			return true;
		else 
			return false;
	}
	
	/**
	 * internal function
	 * $positions array
	 * match the widget positions's with spotlight position
	 *
	 * return true or false
	 * added by themezart
	 */
	
	public $widgetPosition = array();
	
	function checkWidgets($positions=null){
		
		// get all widgets position
		
		if(count($this->widgetPosition) < 1){
			
			//specialWidget is component area position
			$this->widgetPosition['component'] = 'core-component';
			
			$this->widgetPosition['logo_position'] = $this->getParam('logo_position','logo');
			$this->widgetPosition['mainnav_position'] = $this->getParam('mainnav_position','mainnav');
			
			$enable_totop = $this->getParam('enable_totop','1');
			if($enable_totop) $this->widgetPosition['totop_position'] = $this->getParam('totop_position','totop');
			
			$enable_login = $this->getParam('enable_login','1');
			if($enable_login) $this->widgetPosition['login_position'] = $this->getParam('login_position','login');
			
			$enable_fontresizer = $this->getParam('enable_fontresizer','1');
			if($enable_fontresizer) $this->widgetPosition['fontresizer_position'] = $this->getParam('fontresizer_position','fontresizer');
			
			$enable_date = $this->getParam('enable_date','1');
			if($enable_date) $this->widgetPosition['date_position'] = $this->getParam('date_position','date');
			
			$enable_social = $this->getParam('enable_social','1');
			if($enable_social) $this->widgetPosition['social_position'] = $this->getParam('social_position','social');
			
			$enable_copyrightinfo = $this->getParam('enable_copyrightinfo','1');
			if($enable_copyrightinfo) $this->widgetPosition['copyrightinfo_position'] = $this->getParam('copyrightinfo_position','copyright');
			
			$enable_canvas_rmvlogo = $this->getParam('canvas-rmvlogo','1');
			if($enable_canvas_rmvlogo) $this->widgetPosition['canvas_logo_position'] = $this->getParam('canvas_logo_position','brand');
		}
		
		//check if its already checked and has widget position
		if($positions){
			$positionMatch = array_intersect($this->widgetPosition, $positions);
			if(count($positionMatch)>0)
				return true;
			else
				return false;
		}
		return true;
	}
	
	/**
	 * internal function
	 * $position
	 * match the widget positions's with spotlight position
	 *
	 * return true or false
	 * added by themezart
	 */
	function checkWidget($position){
		
		if(count($this->widgetPosition) < 1){
			$this->checkWidgets();
		}
		
		//check if its already checked and has widget position
		$positionMatch = array_intersect($this->widgetPosition, array($position));
		
		if(count($positionMatch)>0) return true;
			else return false;
	}
	
	public function renderWidget($position)
	{
		
		$data = '';
		
		//check if its already checked and has widget position
		$positionMatchs = array_intersect($this->widgetPosition, array($position));
		
		foreach($positionMatchs as $name=>$key){
			$widgetname = str_replace('_position','',$name);
			$data .= $this->getWidget($widgetname);
		}		
		
		return $data;
		
	}
	
	function getWidget($tpl)
	{
		CANVAS::import('core/widget');
		CANVAS::import('widgets/'.$tpl);
		$objectClass = 'CANVASTemplateWidget'.ucfirst($tpl);
		$object = new $objectClass($this->_tpl->params); 
		return  $object->renders();
	}
	
	
}
