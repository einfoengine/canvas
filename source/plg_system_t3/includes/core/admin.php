<?php
/** 
 *------------------------------------------------------------------------------
 * @package       CANVAS Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 ThemezArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       ThemezArt
 *                & t3-framework.org as base version
 * @Google group: https://groups.google.com/forum/#!forum/canvasfw
 * @Link:         http://themezart.com/canvas-framework 
 *------------------------------------------------------------------------------
 */

// Define constant
class CANVASAdmin {

	protected $langs = array();

	/**
	 * function render
	 * render CANVAS administrator configuration form
	 *
	 * @return render success or not
	 */
	public function render(){
		$input  = JFactory::getApplication()->input;
		$body   = JResponse::getBody();
		$layout = CANVAS_ADMIN_PATH . '/admin/tpls/default.php';

		if(file_exists($layout) && 'style' == $input->getCmd('view')){
			
			ob_start();
			$this->renderAdmin();
			$buffer = ob_get_clean();

			//this cause backtrack_limit in some server
			//$body = preg_replace('@<form\s[^>]*name="adminForm"[^>]*>(.*)</form>@msU', $buffer, $body);
			$opentags = explode('<form', $body);
			$endtags = explode('</form>', $body);
			$open = array_shift($opentags);
			$close = array_pop($endtags);

			//should not happend
			if(count($opentags) > 1){
	
				$iopen = 0;
				$iclose = count($opentags);

				foreach ($opentags as $index => $value) {
					if($iopen !== -1 && strpos($value, 'name="adminForm"') === false){
						$iopen++;
						$open = $open . '<form' . $value;
					} else {
						$iopen = -1;
					}

					if($iclose !== -1 && strpos($endtags[--$iclose], 'name="adminForm"') === false){
						$close = $endtags[$iclose] . '</form>' . $close;
					} else {
						$iclose = -1;
					}
				}
			}

			$body = $open . $buffer . $close;
		}

		if(!$input->getCmd('file')){
			$body = $this->replaceToolbar($body);
		}

		$body = $this->replaceDoctype($body);

		JResponse::setBody($body);
	}

	public function addAssets(){

		// load template language
		JFactory::getLanguage()->load ('tpl_'.CANVAS_TEMPLATE.'.sys', JPATH_ROOT, null, true);

		$langs = array(
			'unknownError' => JText::_('CANVAS_MSG_UNKNOWN_ERROR'),

			'logoPresent' => JText::_('CANVAS_LAYOUT_LOGO_TEXT'),
			'emptyLayoutPosition' => JText::_('CANVAS_LAYOUT_EMPTY_POSITION'),
			'defaultLayoutPosition' => JText::_('CANVAS_LAYOUT_DEFAULT_POSITION'),
			
			'layoutConfig' => JText::_('CANVAS_LAYOUT_CONFIG_TITLE'),
			'layoutConfigDesc' => JText::_('CANVAS_LAYOUT_CONFIG_DESC'),
			'layoutUnknownWidth' => JText::_('CANVAS_LAYOUT_UNKN_WIDTH'),
			'layoutPosWidth' => JText::_('CANVAS_LAYOUT_POS_WIDTH'),
			'layoutPosName' => JText::_('CANVAS_LAYOUT_POS_NAME'),

			'layoutCanNotLoad' => JText::_('CANVAS_LAYOUT_LOAD_ERROR'),

			'askCloneLayout' => JText::_('CANVAS_LAYOUT_ASK_ADD_LAYOUT'),
			'correctLayoutName' => JText::_('CANVAS_LAYOUT_ASK_CORRECT_NAME'),
			'askDeleteLayout' => JText::_('CANVAS_LAYOUT_ASK_DEL_LAYOUT'),
			'askDeleteLayoutDesc' => JText::_('CANVAS_LAYOUT_ASK_DEL_LAYOUT_DESC'),
			'askPurgeLayout' => JText::_('CANVAS_LAYOUT_ASK_DEL_LAYOUT'),
			'askPurgeLayoutDesc' => JText::_('CANVAS_LAYOUT_ASK_PURGE_LAYOUT_DESC'),

			'lblDeleteIt' => JText::_('CANVAS_LAYOUT_LABEL_DELETEIT'),
			'lblCloneIt' => JText::_('CANVAS_LAYOUT_LABEL_CLONEIT'),

			'layoutEditPosition' => JText::_('CANVAS_LAYOUT_EDIT_POSITION'),
			'layoutShowPosition' => JText::_('CANVAS_LAYOUT_SHOW_POSITION'),
			'layoutHidePosition' => JText::_('CANVAS_LAYOUT_HIDE_POSITION'),
			'layoutChangeNumpos' => JText::_('CANVAS_LAYOUT_CHANGE_NUMPOS'),
			'layoutDragResize' => JText::_('CANVAS_LAYOUT_DRAG_RESIZE'),
			'layoutHiddenposDesc' => JText::_('CANVAS_LAYOUT_HIDDEN_POS_DESC'),
			
			'updateFailedGetList' => JText::_('CANVAS_OVERVIEW_FAILED_GETLIST'),
			'updateDownLatest' => JText::_('CANVAS_OVERVIEW_GO_DOWNLOAD'),
			'updateCheckUpdate' => JText::_('CANVAS_OVERVIEW_CHECK_UPDATE'),
			'updateChkComplete' => JText::_('CANVAS_OVERVIEW_CHK_UPDATE_OK'),
			'updateHasNew' => JText::_('CANVAS_OVERVIEW_TPL_NEW'),
			'updateCompare' => JText::_('CANVAS_OVERVIEW_TPL_COMPARE'),
			'switchResponsiveMode' => JText::_('CANVAS_MSG_SWITCH_RESPONSIVE_MODE')
		);
		
		$japp   = JFactory::getApplication();
		$jdoc   = JFactory::getDocument();
		$db     = JFactory::getDbo();
		$params = CANVAS::getTplParams();
		$input  = $japp->input;

		//just in case
		if(!($params instanceof JRegistry)){
			$params = new JRegistry;
		}

		//get extension id of framework and template
		$query  = $db->getQuery(true);
		$query
			->select('extension_id')
			->from('#__extensions')
			->where('(element='. $db->quote(CANVAS_TEMPLATE) . ' AND type=' . $db->quote('template') . ') 
					OR (element=' . $db->quote(CANVAS_ADMIN) . ' AND type=' . $db->quote('plugin'). ')');

		$db->setQuery($query);
		$results = $db->loadRowList();
		$eids = array();
		foreach ($results as $eid) {
			$eids[] = $eid[0];
		}

		//check for version compatible
		if(version_compare(JVERSION, '3.0', 'ge')){
			JHtml::_('bootstrap.framework');
		} else {
			$jdoc->addStyleSheet(CANVAS_ADMIN_URL . '/admin/bootstrap/css/bootstrap.css');

			$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/js/jquery-1.8.3.min.js');
			$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/bootstrap/js/bootstrap.js');
			$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/js/jquery.noconflict.js');
		}

		if(!$this->checkAssetsLoaded('chosen.css', '_styleSheets')){
			$jdoc->addStyleSheet(CANVAS_ADMIN_URL . '/admin/plugins/chosen/chosen.css');
		}

		$jdoc->addStyleSheet(CANVAS_ADMIN_URL . '/includes/depend/css/depend.css');
		$jdoc->addStyleSheet(CANVAS_URL . '/css/layout-preview.css');
		$jdoc->addStyleSheet(CANVAS_ADMIN_URL . '/admin/layout/css/layout.css');
		if(file_exists(CANVAS_TEMPLATE_PATH . '/admin/layout-custom.css')) {
			$jdoc->addStyleSheet(CANVAS_TEMPLATE_URL . '/admin/layout-custom.css');
		}
		$jdoc->addStyleSheet(CANVAS_ADMIN_URL . '/admin/css/admin.css');

		if(version_compare(JVERSION, '3.0', 'ge')){
			$jdoc->addStyleSheet(CANVAS_ADMIN_URL . '/admin/css/admin-j30.css');

			if($input->get('file') && version_compare(JVERSION, '3.2', 'ge')){
				$jdoc->addStyleSheet(CANVAS_ADMIN_URL . '/admin/css/file-manager.css');
			}
		} else {
			$jdoc->addStyleSheet(CANVAS_ADMIN_URL . '/admin/css/admin-j25.css');
		}

		if(!$this->checkAssetsLoaded('chosen.jquery.min.js', '_scripts')){
			$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/plugins/chosen/chosen.jquery.min.js');	
		}

		$jdoc->addScript(CANVAS_ADMIN_URL . '/includes/depend/js/depend.js');
		$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/js/json2.js');
		$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/js/jimgload.js');
		$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/layout/js/layout.js');
		$jdoc->addScript(CANVAS_ADMIN_URL . '/admin/js/admin.js');


		$jdoc->addScriptDeclaration ( '
			CANVASAdmin = window.CANVASAdmin || {};
			CANVASAdmin.adminurl = \'' . JUri::getInstance()->toString() . '\';
			CANVASAdmin.canvasadminurl = \'' . CANVAS_ADMIN_URL . '\';
			CANVASAdmin.baseurl = \'' . JURI::base(true) . '\';
			CANVASAdmin.rooturl = \'' . JURI::root() . '\';
			CANVASAdmin.template = \'' . CANVAS_TEMPLATE . '\';
			CANVASAdmin.templateid = \'' . JFactory::getApplication()->input->get('id') . '\';
			CANVASAdmin.langs = ' . json_encode($langs) . ';
			CANVASAdmin.devmode = ' . $params->get('devmode', 0) . ';
			CANVASAdmin.themermode = ' . $params->get('themermode', 1) . ';
			CANVASAdmin.eids = [' . implode($eids, ',') .'];
			CANVASAdmin.telement = \'' . CANVAS_TEMPLATE . '\';
			CANVASAdmin.felement = \'' . CANVAS_ADMIN . '\';
			CANVASAdmin.themerUrl = \'' . JUri::getInstance()->toString() . '&canvasaction=theme&canvastask=thememagic' . '\';
			CANVASAdmin.megamenuUrl = \'' . JUri::getInstance()->toString() . '&canvasaction=megamenu&canvastask=megamenu' . '\';
			CANVASAdmin.canvasupdateurl = \'' . JURI::base() . 'index.php?option=com_installer&view=update&task=update.ajax' . '\';
			CANVASAdmin.canvaslayouturl = \'' . JURI::base() . 'index.php?canvasaction=layout' . '\';
			CANVASAdmin.jupdateUrl = \'' . JURI::base() . 'index.php?option=com_installer&view=update' . '\';'
		);
	}

	public function addJSLang($key = '', $value = '', $overwrite = true){
		if($key && $value && ($overwrite || !array_key_exists($key, $this->langs))){
			$this->langs[$key] = $value ? $value : JText::_($key);
		}
	}
	
	/**
	 * function loadParam
	 * load and re-render parameters
	 *
	 * @return render success or not
	 */
	function renderAdmin(){
		$frwXml = CANVAS_ADMIN_PATH . '/'. CANVAS_ADMIN . '.xml';
		$tplXml = CANVAS_TEMPLATE_PATH . '/templateDetails.xml';
		$cusXml = CANVASPath::getPath('etc/assets.xml');
		$jtpl = CANVAS_ADMIN_PATH . '/admin/tpls/default.php';
		
		if(file_exists($tplXml) && file_exists($jtpl)){
			
			CANVAS::import('depend/canvasform');

			//get the current joomla default instance
			$form = JForm::getInstance('com_templates.style', 'style', array('control' => 'jform', 'load_data' => true));

			//wrap
			$form = new CANVASForm($form);
			
			//remove all fields from group 'params' and reload them again in right other base on template.xml
			$form->removeGroup('params');
			//load the template
			$form->loadFile(CANVAS_PATH . '/params/template.xml');
			//overwrite / extend with params of template
			$form->loadFile($tplXml, true, '//config');
			//overwrite / extend with custom config in custom/etc/assets.xml
			if ($cusXml && file_exists($cusXml))
				$form->loadFile($cusXml, true, '//config');
			// extend parameters
			CANVASBot::prepareForm($form);

			$xml = JFactory::getXML($tplXml);
			$fxml = JFactory::getXML($frwXml);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('id, title')
				->from('#__template_styles')
				->where('template='. $db->quote(CANVAS_TEMPLATE));
			
			$db->setQuery($query);
			$styles = $db->loadObjectList();
			foreach ($styles as $key => &$style) {
				$style->title = ucwords(str_replace('_', ' ', $style->title));
			}
			
			$session = JFactory::getSession();
			$canvaslock = $session->get('CANVAS.canvaslock', 'overview_params');
			$session->set('CANVAS.canvaslock', null);
			$input = JFactory::getApplication()->input;

			include $jtpl;
			
			/*
			//search for global parameters
			$japp = JFactory::getApplication();
			$pglobals = array();
			foreach($form->getGroup('params') as $param){
				if($form->getFieldAttribute($param->fieldname, 'global', 0, 'params')){
					$pglobals[] = array('name' => $param->fieldname, 'value' => $form->getValue($param->fieldname, 'params')); 
				}
			}
			$japp->setUserState('oparams', $pglobals);
			*/

			return true;
		}
		
		return false;
	}

	function replaceToolbar($body){
		$canvastoolbar = CANVAS_ADMIN_PATH . '/admin/tpls/toolbar.php';
		$input = JFactory::getApplication()->input;

		if(file_exists($canvastoolbar) && class_exists('JToolBar')){
			//get the existing toolbar html
			jimport('joomla.language.help');
			$params  = CANVAS::getTplParams();
			$toolbar = JToolBar::getInstance('toolbar')->render('toolbar');
			$helpurl = JHelp::createURL($input->getCmd('view') == 'template' ? 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT' : 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_STYLES_EDIT');
			$helpurl = htmlspecialchars($helpurl, ENT_QUOTES);

			//render our toolbar
			ob_start();
			include $canvastoolbar;
			$canvastoolbar = ob_get_clean();

			//replace it
			$body = str_replace($toolbar, $canvastoolbar, $body);
		}

		return $body;
	}

	function replaceDoctype($body){
		return preg_replace('@<!DOCTYPE\s(.*?)>@', '<!DOCTYPE html>', $body);
	}

	function checkAssetsLoaded($pattern, $hash){
		$doc = JFactory::getDocument();
		$hash = $doc->$hash;

		foreach ($hash as $path => $object) {
			if(strpos($path, $pattern) !== false){
				return true;
			}
		}

		return false;
	}
}

?>