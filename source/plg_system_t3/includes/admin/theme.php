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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
// add new Less format class to work with joomla 3.3
if (version_compare(JVERSION, '3.3.0') >= 0) {
	CANVAS::import('format/less3.3');
}

/**
 *
 * Admin helper module class
 * @author ThemezArt
 *
 */
class CANVASAdminTheme
{
	/**
	 *
	 * save Profile
	 */
	
	public static function response($data){
		die(json_encode($data));
	}
	
	public static function error($msg){
		return self::response(array('error' => $msg));
	}
	
	public static function save($path)
	{
		$result = array();
		
		if(empty($path)){
			return self::error(JText::_('CANVAS_TM_UNKNOWN_THEME'));
		}
		
		$theme = JFactory::getApplication()->input->getCmd('theme');
		$from = JFactory::getApplication()->input->getCmd('from');
		if (!$theme) {
		  return self::error(JText::_('CANVAS_TM_INVALID_DATA_TO_SAVE'));
		}

		//incase empty from
		if(!$from){
			$from = 'base';
		}

		$file = $path . '/less/themes/' . $theme . '/variables-custom.less';

		if(!class_exists('JRegistryFormatLESS')){
			CANVAS::import('format/less');
		}
		$variables = new JRegistry();
		$variables->loadObject($_POST);
		
		$data = $variables->toString('LESS');
		$type = 'new';
		if (JFile::exists($file)) {
			$type = 'overwrite';
		} else {

			if($theme != $from && JFolder::exists($path . '/less/themes/' . $from)){
				if(@JFolder::copy($path . '/less/themes/' . $from, $path . '/less/themes/' . $theme) != true){
					return self::error(JText::_('CANVAS_TM_NOT_FOUND'));
				}
			} else if($from == 'base') {
				$dummydata = "";
				@JFile::write($path . '/less/themes/' . $theme . '/template.less', $dummydata);
				@JFile::write($path . '/less/themes/' . $theme . '/variables.less', $dummydata);
				@JFile::write($path . '/less/themes/' . $theme . '/template-responsive.less', $dummydata);
			}
		}
		
		$return = @JFile::write($file, $data);

		if (!$return) {
			return self::error(JText::_('CANVAS_TM_OPERATION_FAILED'));
		} else {
			$result['success'] = JText::sprintf('CANVAS_TM_SAVE_SUCCESSFULLY', $theme);
			$result['theme'] = $theme;
			$result['type'] = $type;
		}

		//LessHelper::compileForTemplate(CANVAS_TEMPLATE_PATH, $theme);
		CANVAS::import ('core/less');
		CANVASLess::compileAll($theme);
		return self::response($result);
	}

	/**
	 *
	 * Clone Profile
	 */
	public static function duplicate($path)
	{
		$theme = JFactory::getApplication()->input->getCmd('theme');
		$from = JFactory::getApplication()->input->getCmd('from');
		$result = array();
		
		if (empty($theme) || empty($from)) {
			return self::error(JText::_('CANVAS_TM_INVALID_DATA_TO_SAVE'));
		}

		$source = $path . '/less/themes/' . $from;
		if (!JFolder::exists($source)) {
			return self::error(JText::sprintf('CANVAS_TM_NOT_FOUND', $from));
		}
		
		$dest = $path . '/less/themes/' . $theme;
		if (JFolder::exists($dest)) {
			return self::error(JText::sprintf('CANVAS_TM_EXISTED', $theme));
		}

		$result = array();
		if (@JFolder::copy($source, $dest) == true) {
			$result['success'] = JText::_('CANVAS_TM_CLONE_SUCCESSFULLY');
			$result['theme'] = $theme;
			$result['reset'] = true;
			$result['type'] = 'duplicate';
		} else {
			return self::error(JText::_('CANVAS_TM_OPERATION_FAILED'));
		}
		
		//LessHelper::compileForTemplate(CANVAS_TEMPLATE_PATH , $theme);
		CANVAS::import ('core/less');
		CANVASLess::compileAll($theme);
		return self::response($result);
	}

	/**
	 *
	 * Delete a profile
	 */
	public static function delete($path)
	{
		// Initialize some variables
		$theme = JFactory::getApplication()->input->getCmd('theme');
		$result = array();
		
		if (!$theme) {
			return self::error(JText::_('CANVAS_TM_UNKNOWN_THEME'));
		}

		$file = $path . '/less/themes/' . $theme;
		$return = false;
		if (!JFolder::exists($file)) {
			return self::error(JText::sprintf('CANVAS_TM_NOT_FOUND', $theme));
		}
		
		$return = @JFolder::delete($file);
		
		if (!$return) {
			return self::error(JText::sprintf('CANVAS_TM_DELETE_FAIL', $file));
		} else {
			
			$result['template'] = '0';
			$result['success'] = JText::sprintf('CANVAS_TM_DELETE_SUCCESSFULLY', $theme);
			$result['theme'] = $theme;
			$result['type'] = 'delete';
		}

		JFolder::delete($path . '/css/themes/' . $theme);
		return self::response($result);
	}

	/**
	 *
	 * Show thememagic form
	 */
	public static function thememagic($path)
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$isadmin   = $app->isAdmin();
		
		if($isadmin){
			$tplparams = CANVAS::getTplParams();
		} else {
			$tplparams = $app->getTemplate(true)->params;
		}

		$url = $isadmin ? JUri::root(true).'/index.php' : JUri::current();
		$url .= (preg_match('/\?/', $url) ? '&' : '?') . 'themer=1';
		$url .= ($tplparams->get('theme', -1) != -1 ? ('&canvasstyle=' . $tplparams->get('theme')) : '');
		if($isadmin){
			$url .= '&canvastmid=' . $input->getCmd('id');
		}

		$assetspath = CANVAS_TEMPLATE_PATH;
		$themepath = $assetspath . '/less/themes';
		if(!class_exists('JRegistryFormatLESS')){
			include_once CANVAS_ADMIN_PATH . '/includes/format/less.php';
		}

		$themes   = array();
		$jsondata = array();

		//push a default theme
		$tobj = new stdClass();
		$tobj->id    = 'base';
		$tobj->title = JText::_('JDEFAULT');

		$themes['base'] = $tobj;

		$varfile = $assetspath . '/less/variables.less';
		if(file_exists($varfile)){
			$params = new JRegistry;
			$params->loadString(JFile::read($varfile), 'LESS');
			$jsondata['base'] = $params->toArray();
		}

		if (JFolder::exists($themepath)) {
			$listthemes = JFolder::folders($themepath);
			if (count($listthemes)) {
				foreach ($listthemes as $theme) {
					$varsfile = $themepath . '/' . $theme . '/variables-custom.less';
					if(file_exists($varsfile)){

						$tobj = new stdClass();
						$tobj->id    = $theme;
						$tobj->title = $theme;

						//check for all less file in theme folder
						$params = false;
						$others = JFolder::files($themepath . '/' . $theme, '.less');
						foreach($others as $other){
							//get those developer custom values
							if($other == 'variables.less'){
								$params = new JRegistry;
								$params->loadString(JFile::read($themepath . '/' . $theme . '/variables.less'), 'LESS');								
							}

							if($other != 'variables-custom.less'){
								$tobj->$other = true;
							}
						}

						$cparams = new JRegistry;
						$cparams->loadString(JFile::read($varsfile), 'LESS');
						if($params){
							foreach ($cparams->toArray() as $key => $value) {
								$params->set($key, $value);
							}	
						} else {
							$params = $cparams;
						}

						$themes[$theme] = $tobj;
						$jsondata[$theme] = $params->toArray();
					}
				}
			}
		}

		$langs = array (
			'addTheme'       => JText::_('CANVAS_TM_ASK_ADD_THEME'),
			'delTheme'       => JText::_('CANVAS_TM_ASK_DEL_THEME'),
			'overwriteTheme' => JText::_('CANVAS_TM_ASK_OVERWRITE_THEME'),
			'correctName'    => JText::_('CANVAS_TM_ASK_CORRECT_NAME'),
			'themeExist'     => JText::_('CANVAS_TM_EXISTED'),
			'saveChange'     => JText::_('CANVAS_TM_ASK_SAVE_CHANGED'),
			'previewError'   => JText::_('CANVAS_TM_PREVIEW_ERROR'),
			'unknownError'   => JText::_('CANVAS_MSG_UNKNOWN_ERROR'),
			'lblCancel'      => JText::_('JCANCEL'),
			'lblOk'          => JText::_('CANVAS_TM_LABEL_OK'),
			'lblNo'          => JText::_('JNO'),
			'lblYes'         => JText::_('JYES'),
			'lblDefault'     => JText::_('JDEFAULT')
		);

		//Keepalive
		$config      = JFactory::getConfig();
		$lifetime    = ($config->get('lifetime') * 60000);
		$refreshTime = ($lifetime <= 60000) ? 30000 : $lifetime - 60000;

		// Refresh time is 1 minute less than the liftime assined in the configuration.php file.
		// The longest refresh period is one hour to prevent integer overflow.
		if ($refreshTime > 3600000 || $refreshTime <= 0){
			$refreshTime = 3600000;
		}

		$backurl = JFactory::getURI();
		$backurl->delVar('canvasaction');
		$backurl->delVar('canvastask');

		if(!$isadmin){
			$backurl->delVar('tm');
			$backurl->delVar('themer');
		}

		CANVAS::import('depend/canvasform');

		$form = new CANVASForm('thememagic.themer', array('control' => 'canvasform'));
		$form->load(JFile::read(JFile::exists(CANVAS_TEMPLATE_PATH . '/thememagic.xml') ? CANVAS_TEMPLATE_PATH . '/thememagic.xml' : CANVAS_PATH . '/params/thememagic.xml'));
		$form->loadFile(CANVAS_TEMPLATE_PATH . '/templateDetails.xml', true, '//config');

		$tplform = new CANVASForm('thememagic.overwrite', array('control' => 'canvasform'));
		$tplform->loadFile(CANVAS_TEMPLATE_PATH . '/templateDetails.xml', true, '//config');

		$fieldSets = $form->getFieldsets('thememagic');
		$tplFieldSets = $tplform->getFieldsets('thememagic');

		$disabledFieldSets = array();
		foreach ($tplFieldSets as $name => $fieldSet){
			if(isset($fieldSet->disabled)){
				$disabledFieldSets[] = $name;
			}
		}

		include CANVAS_ADMIN_PATH.'/admin/thememagic/thememagic.tpl.php';
		
		exit();
	}

	public static function addAssets(){
		$japp = JFactory::getApplication();
		$user = JFactory::getUser();

		//do nothing when site is offline and user has not login (the offline page is only show login form)
		if ($japp->getCfg('offline') && !$user->authorise('core.login.offline')) {
			return;
		}

		$jdoc = JFactory::getDocument();
		$params = $japp->getTemplate(true)->params;
		
		if(defined('CANVAS_THEMER') && $params->get('themermode', 1)){

			$jdoc->addStyleSheet(CANVAS_URL.'/css/thememagic.css');
			$jdoc->addScript(CANVAS_URL.'/js/thememagic.js');
			
			$theme     = $params->get('theme');
			$params    = new JRegistry;
			$themeinfo = new stdClass;

			if($theme){
				$themepath = CANVAS_TEMPLATE_PATH . '/less/themes/' . $theme;

				if(file_exists($themepath . '/variables-custom.less')){
					if(!class_exists('JRegistryFormatLESS')){
						include_once CANVAS_ADMIN_PATH . '/includes/format/less.php';
					}

					//default variables
					$varfile = CANVAS_TEMPLATE_PATH . '/less/variables.less';
					if(file_exists($varfile)){
						$params->loadString(JFile::read($varfile), 'LESS');
						
						//get all less files in "theme" folder
						$others = JFolder::files($themepath, '.less');
						foreach($others as $other){
							//get those developer custom values
							if($other == 'variables.less'){
								$devparams = new JRegistry;
								$devparams->loadString(JFile::read($themepath . '/variables.less'), 'LESS');

								//overwrite the default variables
								foreach ($devparams->toArray() as $key => $value) {
									$params->set($key, $value);
								}
							}

							//ok, we will import it later
							if($other != 'variables-custom.less' && $other != 'variables.less'){
								$themeinfo->$other = true;
							}
						}

						//load custom variables
						$cparams = new JRegistry;
						$cparams->loadString(JFile::read($themepath . '/variables-custom.less'), 'LESS');
						
						//and overwrite those defaults variables
						foreach ($cparams->toArray() as $key => $value) {
							$params->set($key, $value);
						}
					}
				}
			}

			$cache = array();
			
			// a little security
			if($user->authorise('core.manage', 'com_templates') || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], JUri::base() . 'administrator') !== false)){
				CANVAS::import('core/path');
				$baseurl = JUri::base();
				
				//should we provide a list of less path
				foreach (array(CANVAS_TEMPLATE_PATH . '/less', CANVAS_PATH . '/bootstrap/less', CANVAS_PATH . '/less') as $lesspath) {
					if(is_dir($lesspath)){
						$lessfiles = JFolder::files($lesspath, '.less', true, true);
						if(is_array($lessfiles)){
							foreach ($lessfiles as $less) {
								$path            = ltrim(str_replace(array(JPATH_ROOT, '\\'), array('', '/'), $less), '/');
								$path            = CANVASPath::cleanPath($path);
								$fullurl         = $baseurl . preg_replace('@(\\+)|(/+)@', '/', $path);
								$cache[$fullurl] = JFile::read($less);
							}
						}
					}
				}
			}

			//workaround for bootstrap icon path
			$sparams = new JRegistry;
			if(defined('CANVAS_BASE_RSP_IN_CLASS') && CANVAS_BASE_RSP_IN_CLASS){
				$sparams->set('icon-font-path', '"' . JUri::base() . 'plugins/system/canvas/base-bs3/bootstrap/fonts/"');
			}

			$jdoc->addScriptDeclaration('
				var CANVASTheme = window.CANVASTheme || {};
				CANVASTheme.vars = ' . json_encode($params->toArray()) . ';
				CANVASTheme.svars = ' . json_encode($sparams->toArray()) . ';
				CANVASTheme.others = ' . json_encode($themeinfo) . ';
				CANVASTheme.theme = \'' . $theme . '\';
				CANVASTheme.template = \'' . CANVAS_TEMPLATE . '\';
				CANVASTheme.base = \'' . JURI::base() . '\';
				CANVASTheme.cache = ' . json_encode($cache) . ';
				if(typeof less != \'undefined\'){
					
					//we need to build one - cause the js will have unexpected behavior
					try{
						if(window.parent != window && 
							window.parent.CANVASTheme && 
							window.parent.CANVASTheme.applyLess){
							
							window.parent.CANVASTheme.applyLess(true);
						} else {
							less.refresh();
						}
					} catch(e){

					}
				}'
			);
		}
	}
}