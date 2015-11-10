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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

CANVAS::import('core/path');
CANVAS::import('lessphp/' . CANVAS_BASE_LESS_COMPILER);

/**
 * CANVASLess class compile less
 *
 * @package CANVAS
 */
class CANVASLess
{

	static $kfilepath    = 'less-file-path';
	static $kvarsep      = 'less-content-separator';
	static $krtlsep      = 'rtl-less-content';
	static $rsplitbegin  = '@^\s*\#';
	static $rsplitend    = '[^\s]*?\s*{\s*[\r\n]*\s*content:\s*"([^"]*)";\s*[\r\n]*\s*}[\r\n]*@im';
	static $rswitchrtl   = '@/less/(themes/[^/]*/)?@';
	static $rcomment     = '@/\*[^*]*\*+([^/][^*]*\*+)*/@';
	static $rspace       = '@[\r?\n]{2,}@';
	static $rimport      = '@^\s*\@import\s+"([^"]*)"\s*;@im';
	static $rimportvars  = '@^\s*\@import\s+".*(variables-custom|variables|vars|mixins)\.less"\s*;@im';

	static $_path = null;

	public static function requirement(){
		static $setup;

		if(isset($setup)){

			@ini_set('pcre.backtrack_limit', '2M');

			$mem_limit = @ini_get('memory_limit');
			if (preg_match('@^(\d+)(.)$@', $mem_limit, $matches)) {
				if ($matches[2] == 'M') {
					$mem_limit = $matches[1] * 1024 * 1024;
				} else if ($matches[2] == 'K') {
					$mem_limit = $matches[1] * 1024;
				}
			}

			if((int)$mem_limit < 128 * 1024 * 1024) {
				@ini_set('memory_limit', '128M');
			}

			$setup = true;
		}
	}

	/**
	 * Compile LESS to CSS
	 * @param   $path   the file path of less file
	 * @return  string  the css compiled content
	 */
	public static function getCss($path)
	{
		//build vars once
		self::buildVarsOnce();

		// get vars last-modified
		$vars_lm = self::getState('vars_last_modified', 0);

		// less file last-modified
		$filepath = JPATH_ROOT . '/' . $path;
		$less_lm  = filemtime($filepath);

		// cache key
		$key   = md5($vars_lm . ':' . $less_lm . ':' . $path);
		$group = 'canvas';
		$cache = JCache::getInstance('output', array(
			'lifetime' => 1440
		));

		// get cache
		$data  = $cache->get($key, $group);
		if ($data) {
			return $data;
		}

		// not cached, build & store it
		$data = self::compileCss($path) . "\n";
		$cache->store($data, $key, $group);

		return $data;
	}

	/**
	 * Compile LESS to CSS
	 * @param   $path   the less file to compile
	 * @return  string  url to css file
	 */
	public static function buildCss ($path, $return = false) {
		$rtpl_check		= '@'.preg_quote(CANVAS_TEMPLATE_REL, '@') . '/@i';
		$rtpl_less_check		= '@'.preg_quote(CANVAS_TEMPLATE_REL, '@') . '/less/@i';

		$app     = JFactory::getApplication();
		$doc		 = JFactory::getDocument();
		$theme   = $app->getUserState('current_theme', '');
		$is_rtl      = ($app->getUserState('current_direction') == 'rtl');




		$ie8 = preg_match('/MSIE 8\./', $_SERVER['HTTP_USER_AGENT']);

		// get css cached file
		$subdir  = ($is_rtl ? 'rtl/' : '') . ($theme ? $theme . '/' : '');
		$cssdir  = CANVAS_DEV_FOLDER . ($ie8 ? '/ie8' : '') . '/' . $subdir;
		$cssfile = $cssdir . str_replace('/', '.', $path) . '.css';

		// modified time
		$less_lm = @filemtime (JPATH_ROOT . '/' . $path);
		$css_lm = @filemtime ($cssfile);
		$vars_lm = self::getState('vars_last_modified', 0);

		$list = self::parse($path);

		if (empty ($list)) return false;

		// prepare output list
		$split = !$ie8 && !$return && preg_match ($rtpl_less_check, $path) && !preg_match ('/bootstrap/', $path);
		$output_files = array();

		foreach ($list as $f => $import) {
			if ($import) {
				$css = $cssdir . str_replace('/', '.', $f) . '.css';
				if ($split) $output_files[] = $css;
				$less_lm = max ($less_lm, @filemtime(JPATH_ROOT . '/' . $f));
				$css_lm = max ($css_lm, @filemtime(JPATH_ROOT . '/' . $css));
			}
		}




		// itself
		$output_files [] = $cssfile;

		// check modified
		$rebuild = $vars_lm > $css_lm || $less_lm > $css_lm;
		if ($rebuild) {
			if ($split) {
				self::compileCss($path, $cssfile, true, $list);
			} else {
				self::compileCss($path, $cssfile, false, $list);
			}
		}




		if (!$return) {
			// add css
			foreach ($output_files as $css) {
				$doc->addStylesheet($css);
			}
		} else {
			return $cssfile;
		}
	}

	public static function relativePath($topath, $path, $default = null){
		if(defined('CANVAS_DEV_MODE') && CANVAS_DEV_MODE){
			return $topath ? CANVASPath::relativePath($topath, JUri::root(true) . '/' . $path) . '/' : (!empty($default) ? $default : CANVAS_TEMPLATE_URL . '/css/');
		} else {
			return $topath ? CANVASPath::relativePath($topath, $path) . '/' : (!empty($default) ? $default : CANVAS_TEMPLATE_URL . '/css/');
		}
	}

	/**
	 * @param   string  $path    file path of less file to compile
	 * @param   string  $topath  file path of output css file
	 * @return  bool|mixed       compile result or the css compiled content
	 */
	public static function compileCss($path, $topath = '', $split = false, $list = null) {
		$fromdir = dirname($path);
		$app     = JFactory::getApplication();
		$is_rtl      = ($app->getUserState('current_direction') == 'rtl');

		if (empty ($list)) $list = self::parse($path);
		if (empty ($list)) {
			return false;
		}
		// join $list
		$content = '';
		$importdirs = array();
		$todir = $topath ? dirname($topath) : $fromdir;

		if (!is_dir(JPATH_ROOT . '/' . $todir)) {
			JFolder::create(JPATH_ROOT . '/' . $todir);
		}
		$importdirs[JPATH_ROOT . '/' . $fromdir] = self::relativePath($todir, $fromdir);
		foreach ($list as $f => $import) {
			if ($import) {
				$importdirs[JPATH_ROOT . '/' . dirname($f)] = self::relativePath($todir, dirname($f));
				$content .= "\n#".self::$kfilepath."{content: \"{$f}\";}\n";
				// $content .= "@import \"$import\";\n\n";
				if (is_file(JPATH_ROOT . '/' . $f)) {
					$less_content = file_get_contents(JPATH_ROOT . '/' . $f);
					// remove vars/mixins for template & canvas less
					if (preg_match ('@'.preg_quote(CANVAS_TEMPLATE_REL, '@') . '/less/@i', $f) || preg_match ('@'.preg_quote(CANVAS_REL, '@') . '/less/@i', $f)) {
						$less_content = preg_replace(self::$rimportvars, '', $less_content);
					}
					self::$_path = CANVASPath::relativePath($fromdir, dirname($f)) . '/';
					$less_content = preg_replace_callback(self::$rimport, array('CANVASLess', 'cb_import_path'), $less_content);
					$content .= $less_content;
				}
			} else {
				$content .= "\n#".self::$kfilepath."{content: \"{$path}\";}\n";
				$content .= $f . "\n\n";
			}
		}

		// get vars
		$vars_files = explode('|', self::getVars('urls'));

		// build source
		$source = '';
		// build import vars
		foreach ($vars_files as $var) {
			$vars_path = CANVASPath::relativePath($fromdir, dirname($var));
			if ($vars_path) $vars_path .= "/";
			$var_file = $vars_path . basename($var);
			$source .= "@import \"" . $var_file . "\";\n";
		}
		// less content
		$source .= "\n#" . self::$kvarsep . "{content: \"separator\";}\n" . $content;

		// call Less to compile
		$output = CANVASLessCompiler::compile($source, $path, $todir, $importdirs);

		// process content
		//use cssjanus to transform the content
		if ($is_rtl) {
			$output = preg_split(self::$rsplitbegin . self::$krtlsep . self::$rsplitend, $output, -1, PREG_SPLIT_DELIM_CAPTURE);
			$rtlcontent = isset($output[2]) ? $output[2] : false;
			$output = $output[0];

			CANVAS::import('jacssjanus/ja.cssjanus');
			$output = JACSSJanus::transform($output, true);

			// join with rtl content
			if($rtlcontent){
				$output = $output . "\n" . $rtlcontent;
			}
		}
		// skip duplicate clearfix
		$arr = preg_split(self::$rsplitbegin . self::$kvarsep . self::$rsplitend, $output, 2);
		if (preg_match ('/bootstrap.less/', $path)) {
			$output = implode ("\n", $arr);
		} else {
			$output = count($arr) > 1 ? $arr[1] : $arr[0];
		}

		//remove comments and clean up
		$output = preg_replace(self::$rcomment, '', $output);
		$output = preg_replace(self::$rspace, "\n\n", $output);

		// split if needed
		if ($split) {
			//update path and store to files
			$split_contents = preg_split(self::$rsplitbegin . self::$kfilepath . self::$rsplitend, $output, -1, PREG_SPLIT_DELIM_CAPTURE);
			$file_contents  = array();
			$file       		= $path;
			$isfile         = false;

			foreach ($split_contents as $chunk) {
				if ($isfile) {
					$isfile  = false;
					$file = $chunk;
				} else {
					$file_contents[$file] = (isset($file_contents[$file]) ? $file_contents[$file] : '') . "\n" . $chunk . "\n\n";
					$isfile = true;
				}
			}

			if(!empty($file_contents)){
				//output the file to content and add to document
				foreach ($file_contents as $file => $content) {
					$content = trim($content);
					$filename = str_replace('/', '.', $file) . '.css';
					JFile::write(JPATH_ROOT . '/' . $todir . '/' . $filename, $content);
				}
			}
		} else {
			$output = preg_replace (self::$rsplitbegin . self::$kfilepath . self::$rsplitend, '', $output);
			$output = trim($output);
			if ($topath) {
				JFile::write(JPATH_ROOT . '/' . $topath, $output);
			} else {
				return $output;
			}
		}
		// write to path
		return true;
	}

	/**
	 * Get less variables
	 * @return mixed
	 */
	public static function getVars($name = '')
	{
		return self::getState('vars_' . ($name ? $name.'_' : '') . 'content');
	}

	/**
	 * get value from cache
	 */
	public static function getState ($key, $default = null) {
		$app = JFactory::getApplication();
		$keysfx = $app->getUserState('current_key_sufix');
		// cache key
		$ckey   = $key.$keysfx;
		$group = 'canvas';
		$cache = JCache::getInstance('output', array(
			'lifetime' => 25200,
			'caching'	=> true,
			'cachebase' => JPATH_ROOT.'/'.CANVAS_DEV_FOLDER
		));

		// get cache
		$data  = $cache->get($ckey, $group);
		return $data===false ? $app->getUserState($ckey, $default) : $data;
	}

	/**
	 * store value to cache
	 */
	public static function setState ($key, $value) {
		$app = JFactory::getApplication();
		$keysfx = $app->getUserState('current_key_sufix');
		// cache key
		$ckey   = $key.$keysfx;
		$group = 'canvas';
		$cache = JCache::getInstance('output', array(
			'lifetime' => 25200,
			'caching'	=> true,
			'cachebase' => JPATH_ROOT.'/'.CANVAS_DEV_FOLDER
		));
		if (!$cache->store($value, $ckey, $group)) {
			$app->setUserState($ckey, $value);
		}
	}

	/**
	 * @param  string  $theme  template theme
	 * @param  string  $dir    direction (ltr or rtl)
	 * @return mixed
	 */
	public static function buildVars($theme = null, $dir = null){
		$app  = JFactory::getApplication();
		$params = null;
		if ($app->isAdmin()) {
			$params = $app->getUserState ('current_template_params');
		} else {
			$tpl   =  $app->getTemplate(true);
			$params = $tpl->params;
		}
		if (!$params) {
			CANVAS::error(JText::_('CANVAS_MSG_CANNOT_DETECT_TEMPLATE'));
			exit;
		}

		$responsive = $params->get('responsive', 1);
		// theme style
		if ($theme === null) {
			$theme = $params->get('theme');
		}
		
		// ---------
		// preset selector from url
		//----------
//		if($theme ===null){
//			$theme = $app->getUserState('vars_preset', null);
//		}
//		$theme = (JRequest::getVar('theme',null) ? JRequest::getVar('theme','') : $theme);
		
//		$app->setUserState('vars_preset', $theme);
		// ------------------------
		// end preset selector from url
		
		// detect RTL
		if ($dir === null) {
			$doc = JFactory::getDocument();
			$dir = $doc->direction;
		}
		$app->setUserState('current_theme', $theme);
		$app->setUserState('current_direction', $dir);
		$app->setUserState('current_key_sufix', "_{$theme}_{$dir}");

		$path = CANVAS_TEMPLATE_PATH . '/less/vars.less';
		if(!is_file($path)){
			CANVAS::error(JText::_('CANVAS_MSG_LESS_NOT_VALID'));
			exit;
		}

		// force re-build less if switch responsive mode and get last modified time
		if ($responsive !== self::getState('current_responsive')) {
			self::setState('current_responsive', $responsive);
			$last_modified = time();
			touch($path, $last_modified);
		} else {
			$last_modified = filemtime($path);
		}

		$vars_content          = file_get_contents($path);
		$vars_urls = array();

		preg_match_all('#^\s*@import\s+"([^"]*)"#im', $vars_content, $matches);
		if (count($matches[0])) {
			foreach ($matches[1] as $url) {
				$path = CANVASPath::cleanPath(CANVAS_TEMPLATE_PATH . '/less/' . $url);
				if (file_exists($path)) {
					$last_modified = max($last_modified, filemtime($path));
					$vars_urls[] = CANVASPath::cleanPath(CANVAS_TEMPLATE_REL . '/less/' . $url);
				}
			}
		}

		// add override variables
		$paths = array();
		if ($theme) {
			$paths[] = CANVAS_TEMPLATE_REL . "/less/themes/{$theme}/variables.less";
			$paths[] = CANVAS_TEMPLATE_REL . "/less/themes/{$theme}/variables-custom.less";
		}
		if ($dir == 'rtl') {
			$paths[] = CANVAS_TEMPLATE_REL . "/less/rtl/variables.less";
			if ($theme) $paths[] = CANVAS_TEMPLATE_REL . "/less/rtl/themes/{$theme}/variables.less";
		}
		if (!defined('CANVAS_LOCAL_DISABLED')) {
			$paths[] = CANVAS_LOCAL_REL . "/less/variables.less";
			if ($theme) {
				$paths[] = CANVAS_LOCAL_REL . "/less/themes/{$theme}/variables.less";
				$paths[] = CANVAS_LOCAL_REL . "/less/themes/{$theme}/variables-custom.less";
			}
			if ($dir == 'rtl') {
				$paths[] = CANVAS_LOCAL_REL . "/less/rtl/variables.less";
				if ($theme) $paths[] = CANVAS_LOCAL_REL . "/less/rtl/themes/{$theme}/variables.less";
			}
		}
		if (!$responsive) {
			$paths[] = CANVAS_REL . '/less/non-responsive-variables.less';
			$paths[] = CANVAS_TEMPLATE_REL . '/less/non-responsive-variables.less';
		}

		foreach ($paths as $file) {
			if (is_file(JPATH_ROOT . '/' . $file)) {
				$last_modified = max($last_modified, filemtime(JPATH_ROOT . '/' . $file));
				$vars_urls[] = $file;
			}
		}

		if (self::getState('vars_last_modified') != $last_modified) {
			self::setState('vars_last_modified', $last_modified);
		}
		self::setState('vars_urls_content', implode('|', $vars_urls));
	}

	/**
	 * Build vars only one per request
	 */
	public static function buildVarsOnce(){
		// build less vars, once only
		static $vars_built = false;
		if (!$vars_built) {
			self::buildVars();
			$vars_built = true;
		}
	}

	/**
	 * Wrapper function to add a stylesheet to html document
	 * @param  string  $lesspath  the less file to add
	 */
	public static function addStylesheet($lesspath)
	{
		//build vars once
		self::buildVarsOnce();

		$app   = JFactory::getApplication();
		$doc   = JFactory::getDocument();
		$tpl   = $app->getTemplate(true);
		$theme = $tpl->params->get('theme');

		if (defined('CANVAS_THEMER') && $tpl->params->get('themermode', 1)) {
			// in Themer mode, using js to parse less, so we will use 'text/less' content type
			$doc->addStylesheet(JURI::base(true) . '/' . CANVASPath::cleanPath($lesspath), 'text/less');

			// just to make sure this function is call once
			if(!defined('CANVAS_LESS_JS')){
				// Add lessjs to process lesscss
				$doc->addScript(CANVAS_URL . '/js/less.js');

				if($doc->direction == 'rtl'){
					$doc->addScript(CANVAS_URL . '/js/cssjanus.js');
				}

				define('CANVAS_LESS_JS', 1);
			}
		} else {
			self::buildCss(CANVASPath::cleanPath($lesspath));
		}
	}


	/**
	 * Compile LESS to CSS for a specific theme or all themes
	 * @param  string  $theme  the specific theme
	 */
	public static function compileAll($theme = null)
	{
		$params   = CANVAS::getTplParams();
		JFactory::getApplication()->setUserState ('current_template_params', $params);

		// get files need to compile
		$files    = array();
		$lesspath = CANVAS_TEMPLATE_REL . '/less/';
		$csspath  = CANVASPath::getLocalPath('css/', true);
		$fullpath = JPath::clean(JPATH_ROOT . '/' . $lesspath);

		// canvas core plugin files
		$canvasfiles  = array('frontend-edit', 'legacy-grid', 'legacy-navigation', 'megamenu', 'off-canvas');

		// all less file in less folders
		$lessFiles    = JFolder::files($fullpath, '.less', true, true, array('rtl', 'themes', '.svn', 'CVS', '.DS_Store', '__MACOSX'));

		$lessContent  = '';
		$relLessFiles = array();

		foreach ($lessFiles as $file) {
			$lessContent .= JFile::read($file) . "\n";
			$relLessFiles[] = ltrim(str_replace($fullpath, '', $file), '/\\');
		}

		$lessFiles = $relLessFiles;

		// get files imported in this list
		if (preg_match_all('#^\s*@import\s+"([^"]*)"#im', $lessContent, $matches)) {
			foreach ($lessFiles as $f) {
				if (!in_array($f, $matches[1])) {
					$files[] = substr($f, 0, -5);
				}
			}

			//build canvasfiles
			foreach ($canvasfiles as $key => $file) {
				if(in_array($file, $files)){
					unset($canvasfiles[$key]);
				}
			}
		}

		// build default
		if (!$theme || $theme == 'default') {
			self::buildVars('', 'ltr');

			// compile all less files in template "less" folder
			foreach ($files as $file) {
				self::compileCss($lesspath . $file . '.less', $csspath . $file . '.css');
			}

			// if the template not overwrite the canvas core, we will compile those missing files
			if(!empty($canvasfiles)){
				foreach ($canvasfiles as $file) {
					self::compileCss(CANVAS_REL . '/less/' . $file . '.less', $csspath . $file . '.css');
				}
			}
		}

		// build themes
		if (!$theme) {
			// get themes
			$themes = JFolder::folders(JPATH_ROOT . '/' . $lesspath . '/themes');
		} else {
			$themes = $theme != 'default' ? (array)($theme) : array();
		}

		if (is_array($themes)) {
			foreach ($themes as $t) {
				self::buildVars($t, 'ltr');

				// compile
				foreach ($files as $file) {
					self::compileCss($lesspath . $file . '.less', $csspath . 'themes/' . $t . '/' . $file . '.css');
				}

				if(!empty($canvasfiles)){
					foreach ($canvasfiles as $file) {
						self::compileCss(CANVAS_REL . '/less/' . $file . '.less', $csspath . 'themes/' . $t . '/' . $file . '.css');
					}
				}
			}
		}

		// compile rtl css
		if($params && $params->get('build_rtl', 0)){
			// compile default
			if (!$theme || $theme == 'default') {
				self::buildVars('', 'rtl');

				// compile
				foreach ($files as $file) {
					self::compileCss($lesspath . $file . '.less', $csspath . 'rtl/' . $file . '.css');
				}

				if(!empty($canvasfiles)){
					foreach ($canvasfiles as $file) {
						self::compileCss(CANVAS_REL . '/less/' . $file . '.less', $csspath . 'rtl/' . $file . '.css');
					}
				}
			}

			if (is_array($themes)) {
				// rtl for themes
				foreach ($themes as $t) {
					self::buildVars($t, 'rtl');

					// compile
					foreach ($files as $file) {
						self::compileCss($lesspath . $file . '.less', $csspath . 'rtl/' . $t . '/' . $file . '.css');
					}

					if(!empty($canvasfiles)){
						foreach ($canvasfiles as $file) {
							self::compileCss(CANVAS_REL . '/less/' . $file . '.less', $csspath . 'rtl/' . $t . '/' . $file . '.css');
						}
					}
				}
			}
		}
	}


	/**
	 * Parse a less file to get all its overrides before compile
	 * @param  string  $path the less file
	 */
	public static function parse($path) {
		$rtpl_check		= '@'.preg_quote(CANVAS_TEMPLATE_REL, '@') . '/@i';
		$rtpl_less_check		= '@'.preg_quote(CANVAS_TEMPLATE_REL, '@') . '/less/@i';

		$app    = JFactory::getApplication();
		$theme  = $app->getUserState('current_theme');
		$dir  = $app->getUserState('current_direction');
		$is_rtl = ($dir == 'rtl');

		$less_rel_path = preg_replace($rtpl_less_check, '', $path);
		$less_rel_dir = dirname($less_rel_path);
		$less_rel_dir = $less_rel_dir == '.' ? '' : $less_rel_dir . '/';

		// check path
		$realpath = realpath(JPATH_ROOT . '/' . $path);
		if (!is_file($realpath)) {
			return false;
		}

		// get file content
		$content = file_get_contents($realpath);

		//remove vars.less
		$content = preg_replace(self::$rimportvars, '', $content);

		// split into array, separated by the import
		$arr = preg_split(self::$rimport, $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		$arr[] = $less_rel_path;
		$arr[] = '';

		$list = array();
		$rtl_list = array();
		$list[$path] = '';
		$import = false;

		foreach ($arr as $chunk) {
			if ($import) {
				$import = false;
				$import_url = CANVASPath::cleanPath(CANVAS_TEMPLATE_REL.'/less/'.$less_rel_dir.$chunk);
				// if $url in template, get all its overrides
				if (preg_match ($rtpl_less_check, $import_url)) {
					$less_rel_url = CANVASPath::cleanPath($less_rel_dir.$chunk);
					$array = CANVASPath::getAllPath('less/' . $less_rel_url, true);
					if ($theme) {
						$array = array_merge($array, CANVASPath::getAllPath('less/themes/'.$theme.'/'.$less_rel_url, true));
					}

					foreach ($array as $f) {
						// add file in template only
						if (preg_match ($rtpl_check, $f)) {
							$list [$f] = CANVASPath::relativePath(dirname($path), $f);
						}
					}

					// rtl overrides
					if ($is_rtl) {
						$array = CANVASPath::getAllPath('less/rtl/'.$less_rel_url, true);
						if ($theme) {
							$array = array_merge($array, CANVASPath::getAllPath('less/rtl/themes/'.$theme.'/'.$less_rel_url, true));
						}

						foreach ($array as $f) {
							// add file in template only
							if (preg_match ($rtpl_check, $f)) {
								$rtl_list [$f] = CANVASPath::relativePath(dirname($path), $f);
							}
						}
					}
				} else {
					$list [$import_url] = $chunk;
				}
			} else {
				$import = true;
				$list [$chunk] = false;
			}
		}

		// remove itself
		unset($list[$path]);

		// join rtl
		if ($is_rtl) {
			$list ["\n\n#" . self::$krtlsep . "{content: \"separator\";}\n\n"] = false;
			$list = array_merge($list, $rtl_list);
		}

		return $list;
	}

	public static	function cb_import_path ($match) {
		$f = $match[1];
		$newf = CANVASPath::cleanPath(self::$_path . $f);
		return str_replace($f, $newf, $match[0]);
	}
}
