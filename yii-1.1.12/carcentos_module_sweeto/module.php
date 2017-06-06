<?php echo "<?php\n"; ?>

class <?php echo $this->moduleClass; ?> extends CWebModule
{
	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application
		
		// import the module-level models and components
		$this->setImport(array(
			'<?php echo $this->moduleID; ?>.models.*',
			'<?php echo $this->moduleID; ?>.components.*',
		));
	}

	public function beforeControllerAction($controller, $action) {
		if(parent::beforeControllerAction($controller, $action)) {
			// this method is called before any module controller action is performed
			// you may place customized code here
			
			//list public controller in this module
			$publicControllers = array(
				'site'
			);
			$currentAction = strtolower(Yii::app()->controller->id);
			
			/* if controller action have two auth (public, admin), uncomment this 
			//list public controller action
			$publicControllers = array(
				//'site/action'
			);
			$currentAction = strtolower(Yii::app()->controller->id.'/'.$action->id);
			*/
			
			
			// pake ini untuk set theme per action di controller..
			if(!in_array($currentAction, $publicControllers) && !Yii::app()->user->isGuest) {
				$groupPage = Yii::app()->user->id == 1 ? 'admin_sweeto' : 'back_office';
				$arrThemes = Utility::getCurrentTemplate($groupPage);
				Yii::app()->theme = $arrThemes['template'];
				$this->layout = $arrThemes['layout'];
			}
			Utility::applyCurrentTheme($this);
			
			return true;
		}
		else
			return false;
	}
}
