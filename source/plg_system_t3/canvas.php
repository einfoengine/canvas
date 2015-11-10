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

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * CANVAS plugin class
 *
 * @package        CANVAS
 */

class plgSystemCANVAS extends JPlugin
{
	/**
	 * Switch template for thememagic
	 */
	function onAfterInitialise()
	{
		$canvas_local_disabled = $this->params->get('canvas_local_disabled',0);
		if($canvas_local_disabled) define ('CANVAS_LOCAL_DISABLED', 1);
		
		include_once dirname(__FILE__) . '/includes/core/defines.php';
		include_once dirname(__FILE__) . '/includes/core/canvas.php';
		include_once dirname(__FILE__) . '/includes/core/bot.php';

		//must be in frontend
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			return;
		}

		$input = $app->input;

		if($input->getCmd('themer', 0) && ($canvastmid = $input->getCmd('canvastmid', 0))){
			$user = JFactory::getUser();

			if($canvastmid > 0 && ($user->authorise('core.manage', 'com_templates') || 
					(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], JUri::base()) !== false))){

				$current = CANVAS::getDefaultTemplate();
				if(!$current || ($current->id != $canvastmid)){

					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query
						->select('home, template, params')
						->from('`#__template_styles`')
						->where('`client_id` = 0 AND `id`= ' . (int)$canvastmid)
						->order('`id` ASC');
					$db->setQuery($query);
					$tm = $db->loadObject();

					if (is_object($tm) && file_exists(JPATH_THEMES . '/' . $tm->template)) {
						
						$app->setTemplate($tm->template, (new JRegistry($tm->params)));
						// setTemplate is buggy, we need to update more info
						// update the template 
						$template = $app->getTemplate(true);
						$template->id = $canvastmid;
						$template->home = $tm->template;
					}
				}
			}
		}
	}

	function onAfterRoute()
	{
		if(defined('CANVAS_PLUGIN')){

			CANVASBot::preload();
			$template = CANVAS::detect();
			
			if ($template) {
				
				// load the language
				$this->loadLanguage();

				CANVASBot::beforeInit();
				CANVAS::init($template);
				CANVASBot::afterInit();

				//load CANVAS plugins
				JPluginHelper::importPlugin('canvas');

				if (is_file(CANVAS_TEMPLATE_PATH . '/templateHook.php')) {
					include_once CANVAS_TEMPLATE_PATH . '/templateHook.php';
				}

				$tplHookCls = preg_replace('/(^[^A-Z_]+|[^A-Z0-9_])/i', '', CANVAS_TEMPLATE . 'Hook');
				$dispatcher = JDispatcher::getInstance();

				if (class_exists($tplHookCls)) {
					new $tplHookCls($dispatcher, array());
				}

				$dispatcher->trigger('onCANVASInit');

				//check and execute the canvasaction
				CANVAS::checkAction();

				//check and change template for ajax
				CANVAS::checkAjax();
			}
		}
	}

	function onBeforeRender()
	{
		if (defined('CANVAS_PLUGIN') && CANVAS::detect()) {
			$japp = JFactory::getApplication();

			JDispatcher::getInstance()->trigger('onCANVASBeforeRender');

			if ($japp->isAdmin()) {

				$canvasapp = CANVAS::getApp();
				$canvasapp->addAssets();
			} else {
				$params = $japp->getTemplate(true)->params;
				if (defined('CANVAS_THEMER') && $params->get('themermode', 1)) {
					CANVAS::import('admin/theme');
					CANVASAdminTheme::addAssets();
				}

				//check for ajax action and render canvasajax type to before head type
				if (class_exists('CANVASAjax')) {
					CANVASAjax::render();
				}
			}
		}
	}

	function onBeforeCompileHead()
	{
		if (defined('CANVAS_PLUGIN') && CANVAS::detect() && !JFactory::getApplication()->isAdmin()) {
			// call update head for replace css to less if in devmode
			$canvasapp = CANVAS::getApp();
			if ($canvasapp) {

				JDispatcher::getInstance()->trigger('onCANVASBeforeCompileHead');

				$canvasapp->updateHead();

				JDispatcher::getInstance()->trigger('onCANVASAfterCompileHead');
			}
		}
	}

	function onAfterRender()
	{
		if (defined('CANVAS_PLUGIN') && CANVAS::detect()) {
			$canvasapp = CANVAS::getApp();

			if ($canvasapp) {

				if (JFactory::getApplication()->isAdmin()) {
					$canvasapp->render();
				} else {
					$canvasapp->snippet();
				}

				JDispatcher::getInstance()->trigger('onCANVASAfterRender');
			}
		}
	}

	/**
	 * Add JA Extended menu parameter in administrator
	 *
	 * @param   JForm $form   The form to be altered.
	 * @param   array $data   The associated data for the form
	 *
	 * @return  null
	 */
	function onContentPrepareForm($form, $data)
	{

		if(defined('CANVAS_PLUGIN')){
			if (CANVAS::detect() && (
				$form->getName() == 'com_templates.style'
				|| $form->getName() == 'com_config.templates' 
			)) {

				$_form = clone $form;
				$_form->loadFile(CANVAS_PATH . '/params/template.xml', false);
				//custom config in custom/etc/assets.xml
				$cusXml = CANVASPath::getPath ('etc/assets.xml');
				if ($cusXml && file_exists($cusXml))
					$_form->loadFile($cusXml, true, '//config');

				// extend parameters
				CANVASBot::prepareForm($form);

				//search for global parameters and store in user state
				$app      = JFactory::getApplication();
				$gparams = array();				
				foreach($_form->getGroup('params') as $param){
					if($_form->getFieldAttribute($param->fieldname, 'global', 0, 'params')){
						$gparams[] = $param->fieldname; 
					}
				}
				$this->gparams = $gparams;
			}

			$tmpl = CANVAS::detect() ? CANVAS::detect() : (CANVAS::getDefaultTemplate(true) ? CANVAS::getDefaultTemplate(true) : false);

			if ($tmpl) {
				$tplpath  = JPATH_ROOT . '/templates/' . (is_object($tmpl) && !empty($tmpl->tplname) ? $tmpl->tplname : $tmpl);
				$formpath = $tplpath . '/etc/form/';
				JForm::addFormPath($formpath);

				$extended = $formpath . $form->getName() . '.xml';
				if (is_file($extended)) {
					JFactory::getLanguage()->load('tpl_' . $tmpl, JPATH_SITE);
					$form->loadFile($form->getName(), false);
				}

				// load extra fields for specified module in format com_modules.module.module_name.xml
				if ($form->getName() == 'com_modules.module') {
					$module = isset($data->module) ? $data->module : '';
					if (!$module) {
						$jform = JFactory::getApplication()->input->get ("jform", null, 'array');
						$module = $jform['module'];
					}
					$extended = $formpath . $module . '.xml';
					if (is_file($extended)) {
						JFactory::getLanguage()->load('tpl_' . $tmpl, JPATH_SITE);
						$form->loadFile($module, false);
					}
				}

				//extend extra fields
				CANVASBot::extraFields($form, $data, $tplpath);
			}
		}
	}

	function onExtensionAfterSave($option, $data)
	{
		if (defined('CANVAS_PLUGIN') && CANVAS::detect() && $option == 'com_templates.style' && !empty($data->id)) {
			//get new params value
			$japp = JFactory::getApplication();
			$params = new JRegistry;
			$params->loadString($data->params);						
			//if we have any changed, we will update to global
			if (isset($this->gparams) && count($this->gparams)) {

				//get all other styles that have the same template
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query
					->select('*')
					->from('#__template_styles')
					->where('template=' . $db->quote($data->template));

				$db->setQuery($query);
				$themes = $db->loadObjectList();

				//update all global parameters
				foreach ($themes as $theme) {
					$registry = new JRegistry;
					$registry->loadString($theme->params);

					foreach ($this->gparams as $pname) {
						$registry->set($pname, $params->get($pname)); //overwrite with new value
					}

					$query = $db->getQuery(true);
					$query
						->update('#__template_styles')
						->set('params =' . $db->quote($registry->toString()))
						->where('id =' . (int)$theme->id)
						->where('id <>' . (int)$data->id);

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	/**
	 * Implement event onRenderModule to include the module chrome provide by CANVAS
	 * This event is fired by overriding ModuleHelper class
	 * Return false for continueing render module
	 *
	 * @param   object &$module   A module object.
	 * @param   array $attribs   An array of attributes for the module (probably from the XML).
	 *
	 * @return  bool
	 */
	function onRenderModule(&$module, $attribs)
	{
		static $chromed = false;
		// Detect layout path in CANVAS themes
		if (defined('CANVAS_PLUGIN') && CANVAS::detect()) {

			// fix JA Backlink
			if($module->module == 'mod_footer'){
				$module->content = CANVAS::fixJALink($module->content);
			}

			// Chrome for module
			if (!$chromed) {
				$chromed = true;
				// We don't need chrome multi times
				$chromePath = CANVASPath::getPath('html/modules.php');
				if (file_exists($chromePath)) {
					include_once $chromePath;
				}
			}
		}
		return false;
	}

	/**
	 * Implement event onGetLayoutPath to return the layout which override by CANVAS & CANVAS templates
	 * This event is fired by overriding ModuleHelper class
	 * Return path to layout if found, false if not
	 *
	 * @param   string $module  The name of the module
	 * @param   string $layout  The name of the module layout. If alternative
	 *                           layout, in the form template:filename.
	 *
	 * @return  null
	 */
	function onGetLayoutPath($module, $layout)
	{
		// Detect layout path in CANVAS themes
		if (defined('CANVAS_PLUGIN') && CANVAS::detect()) {
			
			CANVAS::import('core/path');

			$tPath = CANVASPath::getPath('html/' . $module . '/' . $layout . '.php');
			if ($tPath) {
				return $tPath;
			}
		}
		
		return false;
	}

	/**
	 * Update params before rendering content
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed   true if there is an error. Void otherwise.
	 *
	 * @since   1.6
	 */
	public function onContentPrepare ($context, &$article, &$params, $page = 0) {
		// update params for Article View
		if ($context == 'com_content.article') {
			$app = JFactory::getApplication();
			$tmpl = $app->getTemplate(true);
			if ($tmpl->params->get('link_titles') !== NULL) {
				$article->params->set('link_titles', $tmpl->params->get('link_titles'));
			}
		}
	}
}
