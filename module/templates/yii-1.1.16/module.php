<?php echo "<?php\n"; ?>
/**
 * <?php echo $this->moduleClass."\n"; ?>
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (www.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($this->useModified):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php endif; ?>
 * @link <?php echo $this->linkSource."\n";?>
 *
 *----------------------------------------------------------------------------------------------------------
 */

class <?php echo $this->moduleClass; ?> extends CWebModule
{
	use ThemeTrait;

	public $publicControllers = array();
	private $_module = '<?php echo $this->moduleID;?>';

	public $defaultController = 'site';
	
	// getAssetsUrl()
	//	return the URL for this module's assets, performing the publish operation
	//	the first time, and caching the result for subsequent use.
	private $_assetsUrl;

	public function init() 
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'<?php echo $this->moduleID; ?>.models.*',
			'<?php echo $this->moduleID; ?>.components.*',
		));

		// this method is called before any module controller action is performed
		// you may place customized code here
		// list public controller in this module
		$controllerMap = array();

		$controllerPath = 'application.modules.'.$this->_module.'.controllers';
		if(!empty($controllerMap))
			$controllerMap = array_merge($controllerMap, $this->getController($controllerPath));
		else
			$controllerMap = $this->getController($controllerPath);

		$this->controllerMap = $controllerMap;
		$this->publicControllers = array_keys($this->controllerMap);
	}

	public function beforeControllerAction($controller, $action) 
	{
		if(parent::beforeControllerAction($controller, $action)) 
		{
			// pake ini untuk set theme per action di controller..
			// $currentAction = Yii::app()->controller->id.'/'.$action->id;
			if(!in_array(Yii::app()->controller->id, $this->publicControllers) && !Yii::app()->user->isGuest) {
				$arrThemes = $this->currentTemplate('admin');
				Yii::app()->theme = $arrThemes['folder'];
				$this->layout = $arrThemes['layout'];
			}
			$this->applyCurrentTheme($this);
			
			return true;
		}
		else
			return false;
	}
 
	public function getAssetsUrl()
	{
		if ($this->_assetsUrl === null)
			$this->_assetsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('<?php echo $this->moduleID; ?>.assets'));
		
		return $this->_assetsUrl;
	}

	public function getController($path, $sub=null)
	{
		$controllerMap = array();
		$controllerPath = Yii::getPathOfAlias($path);
		$pathArray = explode('.', $path);
		$lastPath = end($pathArray);

		foreach (new DirectoryIterator($controllerPath) as $fileInfo) {
			if($fileInfo->isDot() && $fileInfo->isDir())
				continue;
			
			if($fileInfo->isFile() && !in_array($fileInfo->getFilename(), array('index.php','.DS_Store'))) {
				$getFilename = $fileInfo->getFilename();
				$controller = strtolower(preg_replace('(Controller.php)', '', $getFilename));
				if($lastPath != 'controllers')
					$controller = join('', array($lastPath, preg_replace('(Controller.php)', '', $getFilename)));
				$controllerClass = preg_replace('(.php)', '', $getFilename);

				$controllerMap[$controller] = array(
					'class'=>join('.', array($path, $controllerClass)),
				);

			} else if($fileInfo->isDir()) {
				$sub = $fileInfo->getFilename();
				$subPath = join('.', array($path, $sub));
				$controllerMap = array_merge($controllerMap, $this->getController($subPath, $sub));
			}
		}
		
		return $controllerMap;
	}
}
