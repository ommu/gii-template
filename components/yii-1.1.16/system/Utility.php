<?php
/**
 * Utility class file
 *
 * Contains many function that most used :
 *	getCurrentTemplate
 *	applyCurrentTheme
 *	applyViewPath
 *	getProtocol

 
 *	getConnected
 *	isServerAvailible
 *	getContentMenu 
 *	flashSuccess
 *	flashError
 *	getDifferenceDay
 *	getLocalDayName
 *	getLocalMonthName
 *	dateFormat
 *	getTimThumb
 *	replaceSpaceWithUnderscore
 *	getUrlTitle
 *	deleteFolder
 *	getPublish
 *	shortText
 *	convert_smart_quotes
 *	softDecode
 *	hardDecode
 *	cleanImageContent
 *	chmodr
 *	recursiveDelete
 *	formatSizeUnits
 *
 * @author Putra Sudaryanto <putra@ommu.co>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) 2013 Ommu Platform (www.ommu.co)
 * @created date November 27, 2013 15:02 WIB
 * @modified date April 3, 2014 15:02 WIB
 * @link https://github.com/ommu/gii-template
 *
 */

class Utility
{
	/**
	* Return setting template with typePage: public, admin_sweeto or back_office
	*/
	public static function getCurrentTemplate($typePage) {
		$model = OmmuThemes::model()->find(array(
			'select' => 'folder, layout',
			'condition' => 'group_page = :group AND default_theme = "1"',
			'params' => array(':group' => $typePage)
		));
		if($model != null) {
			return array('folder' => $model->folder, 'layout' => $model->layout);
		}
	}

	/**
	 * Refer layout path to current applied theme.
	 *
	 * @param object $module that currently active [optional]
	 * @return void
	 */
	public static function applyCurrentTheme($module = null) {
		$theme = Yii::app()->theme->name;
		Yii::app()->theme = $theme;

		if($module !== null) {
			$themePath = Yii::getPathOfAlias('webroot.themes.'.$theme.'.views.layouts');
			$module->setLayoutPath($themePath);
		}
	}

	/**
	 * @return string the root directory of view files. Defaults to 'moduleDir/views' where
	 * moduleDir is the directory containing the module class.
	 */
	public function applyViewPath($path)
	{
		$module = strtolower(Yii::app()->controller->module->id);
		$basePath = Yii::app()->basePath;
		$modulePath = Yii::app()->modulePath;
		$viewPath = Yii::app()->controller->module->viewPath;
		if($module == null)
			$viewPath = Yii::app()->viewPath;
		
		$path = preg_replace('(controllers)', 'views', $path);
		$viewPathSlashes = addcslashes($viewPath, '/');
		if(!preg_match("/$viewPathSlashes/", $path)) {
			Yii::app()->controller->module->viewPath = join('/', [$modulePath, $module, 'views']);
			if($module == null)
				Yii::app()->viewPath = join('/', [$basePath, 'views']);
		}
	}

	/**
	 * Get the proper http URL prefix depending on if this was a secure page request or not
	 *
	 * @return string https or https
	 */
	public static function getProtocol() {
		if(Yii::app()->request->isSecureConnection)
			return 'https';
		return 'http';
	}

















	
	/**
	 * get alternatif connected domain for inlis sso server
	 * @param type $operator not yet using
	 * @return type
	 */
	public static function getConnected($serverOptions) {
		if(Yii::app()->params['server_options']['default'] == true)
			$connectedUrl = Yii::app()->params['server_options']['default_host'];
			
		else {
			$connectedUrl = 'neither-connected';
			
			foreach($serverOptions as $val) {
				if(self::isServerAvailible($val)) {
					$connectedUrl = $val;
					break;
				}
			}
			file_put_contents('assets/utility_server_actived.txt', $connectedUrl);
		}

		return $connectedUrl;
	}

	//returns true, if domain is availible, false if not
	public static function isServerAvailible($domain) 
	{
		if(Yii::app()->params['server_options']['status'] == true) {
			//check, if a valid url is provided
			if (!filter_var($domain, FILTER_VALIDATE_URL))
				return false;

			//initialize curl
			$curlInit = curl_init($domain);
			curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
			curl_setopt($curlInit,CURLOPT_HEADER,true);
			curl_setopt($curlInit,CURLOPT_NOBODY,true);
			curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

			//get answer
			$response = curl_exec($curlInit);
			curl_close($curlInit);
			if($response)
				return true;

			return false;
		
		} else
			return false;
	}
	
	/**
	* Return setting template with typePage: public, admin_sweeto or back_office
	*/
	public static function getArrayFromYML($path)
	{
		Yii::import('mustangostang.spyc.Spyc');
			
		if(file_exists($path)) {
			$arraySpyc = Spyc::YAMLLoad($path);	
			
			if(!empty($arraySpyc))
				return $arraySpyc;
			else
				return false;
			
		} else
			return false;
	}
	
	/**
	* Return setting template with typePage: public, admin_sweeto or back_office
	*/
	public static function getModuleInfo($module=null, $parent=null)
	{
		define('DS', DIRECTORY_SEPARATOR);		
		if($module != null) {
			$YMLPath = Yii::getPathOfAlias('ommu.'.$module).DS.$module.'.yaml';
			if($parent != null) {
				$module = $parent.'-'.$module;
				$YMLPath = Yii::getPathOfAlias('ommu.'.$parent).DS.$module.'.yaml';
			}
		} else
			$YMLPath = Yii::getPathOfAlias('application.libraries.core').DS.'core.yaml';
			
		if(file_exists($YMLPath)) {
			$moduleInfo = self::getArrayFromYML($YMLPath);
			
			return $moduleInfo;
			
		} else
			return false;
	}
	
	/**
	* Return setting template with typePage: public, admin_sweeto or back_office
	*/
	public static function getContentMenu($module=null, $parent=null) 
	{
		$moduleInfo = self::getModuleInfo($module, $parent);
		$contentMenu = $moduleInfo['content_menu'];
		
		if($contentMenu != null) {
			foreach ($contentMenu as $key => $val) {
				$contentSubmenu = $val['submenu'];
				if($contentSubmenu != null) {
					$contentSubmenuData = array_filter($contentSubmenu, function($a) {
						$siteType = explode(',', $a['urlRules']['siteType']);
						$userLevel = explode(',', $a['urlRules']['userLevel']);
						
						return in_array(OmmuSettings::getInfo('site_type'), $siteType) && in_array(Yii::app()->user->level, $userLevel);
					});
					$contentMenu[$key]['submenu'] = array_values($contentSubmenuData);	
				}
			}
			
			$contentMenuData = array_filter($contentMenu, function($a) {					
				$siteType = explode(',', $a['urlRules']['siteType']);
				$userLevel = explode(',', $a['urlRules']['userLevel']);
				
				return in_array(OmmuSettings::getInfo('site_type'), $siteType) && in_array(Yii::app()->user->level, $userLevel);
			});
			return $contentMenuData;
			
		} else
			return false;
	}
	
	/**
	* Return setting template with typePage: public, admin_sweeto or back_office
	*/
	public static function getModuleMenu($module=null, $parent=null) 
	{
		$moduleInfo = self::getModuleInfo($module, $parent);
		$moduleMenu = $moduleInfo['plugin_menu'];
		
		if($moduleMenu != null) {
			foreach ($moduleMenu as $key => $val) {
				$moduleSubmenu = $val['submenu'];
				if($moduleSubmenu != null) {
					$moduleSubmenuData = array_filter($moduleSubmenu, function($a) {
						$siteType = explode(',', $a['urlRules']['siteType']);
						$userLevel = explode(',', $a['urlRules']['userLevel']);
						
						return in_array(OmmuSettings::getInfo('site_type'), $siteType) && in_array(Yii::app()->user->level, $userLevel);
					});
					$moduleMenu[$key]['submenu'] = array_values($moduleSubmenuData);	
				}
			}
			
			$moduleMenuData = array_filter($moduleMenu, function($a) {
				$siteType = explode(',', $a['urlRules']['siteType']);
				$userLevel = explode(',', $a['urlRules']['userLevel']);
				
				return in_array(OmmuSettings::getInfo('site_type'), $siteType) && in_array(Yii::app()->user->level, $userLevel);
			});
			return array_values($moduleMenuData);
			
		} else
			return false;
	}
	
	/**
	 * Provide style for success message
	 *
	 * @param mixed $msg
	 */
	 public static function flashSuccess($msg) {
		$result  = '<div class="errorSummary success"><p>';
		$result .= $msg.'</p></div>';
		return $result;
	}

	/**
	 * Provide style for error message
	 *
	 * @param mixed $msg
	 */
	public static function flashError($msg) {
		if($msg != ''){
			$result  = '<div class="errorSummary"><p>';
			$result .= $msg.'</p></div>';
		}
		return $result;
	}
	
	/**
	 * get Language
	 *
	 */
    public static function getLanguage(){
		if(Yii::app()->session['language'] != null)
			$lang = Yii::app()->session['language'];
			
        else {
			$lang = Yii::app()->getRequest()->getParam('lang') != '' ? Yii::app()->getRequest()->getParam('lang') : null;
			if($lang == null) //find default language 
			    $lang = Yii::app()->params['primaryLang'];
        }
        return $lang;
    }

	/**
	 * Difference day
	 */
	public static function getDifferenceDay($date1, $date2)
	{
		$date1 = date('Y-m-d', strtotime($date1));
		$date2 = date('Y-m-d', strtotime($date2));

		$old_splite = explode("-", $date1);
		$old_date = $old_splite[2];
		$old_month = $old_splite[1];
		$old_year = $old_splite[0];

		$new_splite = explode("-", $date2);
		$new_date = $new_splite[2];
		$new_month = $new_splite[1];
		$new_year =  $new_splite[0];

		$old_day = GregorianToJD($old_month, $old_date, $old_year);
		$new_day = GregorianToJD($new_month, $new_date, $new_year);

		$difference = $new_day - $old_day;

		return $difference;
	}

	/**
	Mengembalikan nama hari dalam bahasa indonesia.
	@params short=true, tampilkan dalam 3 huruf, JUM, SAB
	*/
	public static function getLocalDayName($date, $short=true) {
		$dayName = date('N', strtotime($date));
		switch($dayName) {
			case 0:
			    return ($short ? 'Min' : 'Minggu');
			break;

			case 1:
			    return ($short ? 'Sen' : 'Senin');
			break;

			case 2:
			    return ($short ? 'Sel' : 'Selasa');
			break;

			case 3:
			    return ($short ? 'Rab' : 'Rabu');
			break;

			case 4:
			    return ($short ? 'Kam' : 'Kamis');
			break;

			case 5:
			    return ($short ? 'Jum' : 'Jumat');
			break;

			case 6:
			    return ($short ? 'Sab' : 'Sabtu');
			break;
		}
	}

	/* Ubah bulan angka ke nama bulan */
	public static function getLocalMonthName($date, $short=false) {
		if(empty($date))
			return false;
		
		$month = date('m', strtotime($date));

		$bulan = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
		$shortBulan = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');

		if($short == true)
			return $shortBulan[$month-1];
		else
			return $bulan[$month-1];
	}
	
	/**
	 * Get date format (general setting)
	 */
	public static function dateFormat($date, $time=false) 
	{
		if(is_numeric($date) && (int)$date == $date)
			$date = date('Y-m-d H:i:s', $date);
		
		$setting = OmmuSettings::model()->findByPk(1, array(
			'select' => 'site_dateformat, site_timeformat',
		));
		
		if($time == true) {
			$date = date($setting->site_dateformat, strtotime($date)).' '.date($setting->site_timeformat, strtotime($date)).' WIB';
		} else{
			$date = date($setting->site_dateformat, strtotime($date));
		}
		return $date;
	}
	
	/**
	 * getTimThumb function
	 */
	public static function getTimThumb($src, $width, $height, $zoom, $crop='c') 
	{
		if(Yii::app()->params['timthumb_url_replace'] == 1)
			$src = str_replace(Yii::app()->request->baseUrl, Yii::app()->params['timthumb_url_replace_website'], $src);
		
		$image = self::getProtocol().'://'.Yii::app()->request->serverName.Yii::app()->request->baseUrl.'/timthumb.php?src='.$src.'&h='.$height.'&w='.$width.'&zc='.$zoom.'&a='.$crop;
        return $image;
    }

	/**
	 * replace space with underscore
	 */
	public static function replaceSpaceWithUnderscore($fileName) {
		return str_ireplace(' ', '_', strtolower(trim($fileName)));
	}
	
	/**
	 * Create URL Title
	 *
	 * Takes a "title" string as input and creates a
	 * human-friendly URL string with a "separator" string
	 * as the word separator.
	 *
	 * @todo    Remove old 'dash' and 'underscore' usage in 3.1+.
	 * @param   string  $str        Input string
	 * @param   string  $separator  Word separator
	 *          (usually '-' or '_')
	 * @param   bool    $lowercase  Wether to transform the output string to lowercase
	 * @return  string
	 */
	public static function getUrlTitle($str, $separator = '-', $lowercase = true) {
		if($separator === 'dash') {
			$separator = '-';
		}
		elseif($separator === 'underscore') {
			$separator = '_';
		}

		$qSeparator = preg_quote($separator, '#');
		$trans = array(
				'&.+?:;'         => '',
				'[^a-z0-9 _-]'      => '',
				'\s+'           => $separator,
				'('.$qSeparator.')+'   => $separator
			);

		$str = strip_tags($str);
		foreach ($trans as $key => $val) {
			$str = preg_replace('#'.$key.'#i', $val, $str);
		}

		if ($lowercase === true) {
			$str = strtolower($str);
		}

		return trim(trim($str, $separator));
	}

	/**
	 * remove folder and file
	 */
	public static function deleteFolder($path) {
		if(file_exists($path)) {
			$fh = dir($path);
			while (false !== ($files = $fh->read())) {
				@unlink($fh->path.'/'.$files);
			}
			$fh->close();
			@rmdir($path);
			return true;

		} else {
			return false;
		}
	}

	/**
	 * get publish status
	 * 1 = Publish
	 * 2 = Active
	 * 3 = Enabled
	 * 4 = Dialog
	 * 5 = BUG and Report
	 * 6 = Default
	 * 7 = Verify
	 * 8 = Subcribe
	 * 9 = Headline
	 * 10 = Install
	 */
	public static function getPublish($url, $id, $type=1) 
	{
		$baseUrl = !empty(Yii::app()->theme->name) ? Yii::app()->theme->baseUrl : Yii::app()->request->baseUrl;
		
		$arrType = explode(',', $type);
		if(count($arrType) > 1 )
			$phrase = $id == '0' ? Yii::t('phrase', trim($arrType[0])) : Yii::t('phrase', trim($arrType[1]));
		else {
			if($type == '1')
				$phrase = $id == '0' ? Yii::t('phrase', 'Publish') : Yii::t('phrase', 'Unpublish');
			else if($type == '2')
				$phrase = $id == '0' ? Yii::t('phrase', 'Actived') : Yii::t('phrase', 'Deactived');
			else if($type == '3')
				$phrase = $id == '0' ? Yii::t('phrase', 'Enabled') : Yii::t('phrase', 'Disabled');
			else if($type == '4')
				$phrase = $id == '0' ? Yii::t('phrase', 'Enabled Dialog') : Yii::t('phrase', 'Disable Dialog');
			else if($type == '5')
				$phrase = $id == '0' ? Yii::t('phrase', 'Unresolved') : Yii::t('phrase', 'Resolved');
			else if($type == '6')
				$phrase = $id == '0' ? Yii::t('phrase', 'Defaults') : Yii::t('phrase', 'Defaults');
			else if($type == '7')
				$phrase = $id == '0' ? Yii::t('phrase', 'Verified') : Yii::t('phrase', 'Unverified');
			else if($type == '8')
				$phrase = $id == '0' ? Yii::t('phrase', 'Subcribe') : Yii::t('phrase', 'Unsubcribe');
			else if($type == '9')
				$phrase = $id == '0' ? Yii::t('phrase', 'Headline') : Yii::t('phrase', 'Headline');
			else if($type == '10')
				$phrase = $id == '0' ? Yii::t('phrase', 'Install Module') : Yii::t('phrase', 'Install Module');
		}
		
		$image = $id == '0' ? $baseUrl.'/images/icons/unpublish.png' : $baseUrl.'/images/icons/publish.png';
		$publish = '<a href="'.$url.'" title="'.$phrase.'"><img src="'.$image.'"></a>';

		return $publish;
	}

    /**
	 * shortText
	 */
	public static function shortText ($var, $len = 60, $dotted = "...") {
		if (strlen ($var) < $len) { return $var; }
		if (preg_match ("/(.{1,$len})\s/", $var, $match)) {  return $match [1] . $dotted;  }
		else { return substr ($var, 0, $len) . $dotted; }
	}

    /**
	 * replace smart quotes or franky ugly char by micrsooft word 'copas'
	 */
	public static function convert_smart_quotes($string) {
		$search = array(chr(145), chr(146), chr(147), chr(148), chr(151), chr(150), chr(133), chr(149));
		$replace = array("'", "'", '"', '"', '--', '-', '...', "&bull;");
		return str_replace($search, $replace, $string);
	}

    /**
	 * Cleaning html entities for detail view, so it still 
	 * html tag<p> or <strong>,etc
	 */
	public static function softDecode($string) {
		/*
		$data = htmlspecialchars_decode($string);
		$data= html_entity_decode($string);
		$data = ereg_replace("&quot;", chr(34),$data);
		$data = ereg_replace("&lt;", chr(60),$data);
		$data = ereg_replace("&gt;", chr(62),$data);
		$data = ereg_replace("&amp;", chr(38),$data);
		$data = ereg_replace("&nbsp;", chr(32),$data);
		$data = ereg_replace("&amp;nbsp;", "",$data);
		$data= html_entity_decode($data);
		*/

		$data = get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES);
		$data = array_flip($data);
		$original = strtr($string, $data);

		return $original;
	}

    /**
	 * Super Cleaning for decode and strip all html tag
	 */
	public static function hardDecode($string) {
		$data = htmlspecialchars_decode($string);
		$data = html_entity_decode($data);
		$data = strip_tags($data);
		$data = chop(Utility::convert_smart_quotes($data));
		$data = str_replace(array("\r", "\n", "	"), "", $data);

		return ($data);
	}

    /**
	 * Super Cleaning for decode and strip all html tag
	 */
	public static function otherDecode($string) {
		$data = str_replace(array("\r", "\n", "	"), "", $string);

		return ($data);
	}
	
	// Ambil isi berita dan buang image darinya.
	public static function cleanImageContent($content) {
		$posImg = strpos($content, '<img');
		
		$result = $content;
		if($posImg !== false) {
			$posClosedImg = strpos($content, '/>', $posImg) + 2;
			$img = substr($content, $posImg, ($posClosedImg-$posImg));

			$result = str_replace($img, '', $content);
		}
		return $result;	
	}

	/**
	 * Recursively chmod file/folder
	 *
	 * @param string $path
	 * @param octal $fileMode
	 */
	public static function chmodr($path, $fileMode) {
		if (!is_dir($path))
			return chmod($path, $fileMode);

		$dh = opendir($path);
		while (($file = readdir($dh)) !== false) {
			if($file != '.' && $file != '..') {
				$fullpath = $path.'/'.$file;
				if(is_link($fullpath))
					return false;
				elseif(!is_dir($fullpath) && !@chmod($fullpath, $fileMode))
						return false;
				elseif(!self::chmodr($fullpath, $fileMode))
					return false;
			}
		}
		closedir($dh);

		if(@chmod($path, $fileMode))
			return true;
		else
			return false;
	}

	/**
	 * Delete files and folder recursively
	 *
	 * @param string $path path of file/folder
	 */
	public static function recursiveDelete($path) {
		if(is_file($path)) {
			@unlink($path);
		}else {
			$it = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($path),
				RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ($it as $file) {
				if (in_array($file->getBasename(), array('.', '..'))) {
					continue;
				}elseif ($file->isDir()) {
					rmdir($file->getPathname());
				}elseif ($file->isFile() || $file->isLink()) {
					unlink($file->getPathname());
				}
			}
			rmdir($path);
		}
	}

	/**
	 * PHP filesize MB/KB conversion
	 */
	public static function formatSizeUnits($path) {
		$bytes = sprintf('%u', filesize($_SERVER["DOCUMENT_ROOT"].'/'.$path));
		
		$units = explode(' ','B KB MB GB TB PB');
		$mod = 1024;
		for ($i = 0; $bytes > $mod; $i++) {
			 $bytes /= $mod;
		}
		$endIndex = strpos($bytes, ".")+3;
		return substr( $bytes, 0, $endIndex).' '.$units[$i];
	}

	/**
	 * Explode Implode Type File
	 * $type = true (explode), false (implode)
	 */
	public static function formatFileType($data, $type=true, $separator=',') 
	{
		if($type == true) {
			$result = array_map("trim", explode($separator, $data));
		} else {
			$result = implode($separator.' ', $data);
		}
		return $result;	
	}
}
