<?php
/**
 * This is the template for generating a controller class file for CRUD feature.
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

$modelClass = $this->modelClass;
if(preg_match('/Core/', $modelClass))
	$modelClass = preg_replace('(Core)', '', $modelClass);
else
	$modelClass = preg_replace('(Ommu)', '', $modelClass);
$label = $this->class2name($modelClass);
$nameColumn=$this->getTableAttribute($this->tableSchema->columns)
?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $this->controllerClass."\n"; ?>
 * @var $this <?php echo $this->controllerClass."\n"; ?>
 * @var $model <?php echo $this->modelClass."\n"; ?>
 * @var $form CActiveForm
 *
 * Reference start
 * TOC :
 *	Index
<?php if(!$this->forBackendController):?>
 *	View
<?php endif; ?>
 *	Manage
 *	Add
 *	Edit
<?php if($this->forBackendController):?>
 *	View
<?php endif; ?>
<?php if(array_key_exists('publish', $this->tableSchema->columns)): ?>
 *	RunAction
<?php endif; ?>
 *	Delete
<?php if(array_key_exists('publish', $this->tableSchema->columns)): ?>
 *	Publish
<?php endif; ?>
<?php if(array_key_exists('headline', $this->tableSchema->columns)): ?>
 *	Headline
<?php endif; ?>
 *
 *	LoadModel
 *	performAjaxValidation
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

class <?php echo $this->controllerClass; ?> extends <?php echo $this->baseControllerClass."\n"; ?>
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	//public $layout='//layouts/column2';
	public $defaultAction = 'index';

	/**
	 * Initialize admin page theme
	 */
	public function init() 
	{
<?php if($this->forBackendController):?>
		if(!Yii::app()->user->isGuest) {
			if(Yii::app()->user->level == 1) {
			//if(in_array(Yii::app()->user->level, array(1,2))) {
				$arrThemes = Utility::getCurrentTemplate('admin');
				Yii::app()->theme = $arrThemes['folder'];
				$this->layout = $arrThemes['layout'];
				//Utility::applyViewPath(__dir__);
			}
		} else
			$this->redirect(Yii::app()->createUrl('site/login'));
<?php else:?>
		$arrThemes = Utility::getCurrentTemplate('public');
		Yii::app()->theme = $arrThemes['folder'];
		$this->layout = $arrThemes['layout'];
<?php endif; ?>
	}

	/**
	 * @return array action filters
	 */
	public function filters() 
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			//'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() 
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(),
				'users'=>array('@'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','manage','add','edit','view','runaction','delete','publish','headline'),
				//'actions'=>array('manage','add','edit','view','runaction','delete','publish','headline'),
				'users'=>array('@'),
				'expression'=>'in_array($user->level, array(1,2))',
				//'expression'=>'$user->level == 1',
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array(),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	/**
	 * Lists all models.
	 */
	public function actionIndex() 
	{
<?php if($this->forBackendController):?>
		$this->redirect(array('manage'));
<?php else:?>
		$arrThemes = Utility::getCurrentTemplate('public');
		Yii::app()->theme = $arrThemes['folder'];
		$this->layout = $arrThemes['layout'];
		Utility::applyCurrentTheme($this->module);
		
		$setting = <?php echo $this->modelClass; ?>::model()->findByPk(1, array(
			'select' => 'meta_description, meta_keyword',
		));

		$criteria=new CDbCriteria;
		$criteria->condition = 'publish = :publish';
		$criteria->params = array(':publish'=>1);
		$criteria->order = 'creation_date DESC';

		$dataProvider = new CActiveDataProvider('<?php echo $this->modelClass; ?>', array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>10,
			),
		));

		$this->pageTitle = Yii::t('phrase', '<?php echo $inflector->pluralize($label); ?>');
		$this->pageDescription = $setting->meta_description;
		$this->pageMeta = $setting->meta_keyword;
		$this->render('front_index', array(
			'dataProvider'=>$dataProvider,
		));
<?php endif; ?>
	}
	
<?php if(!$this->forBackendController):?>
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) 
	{
		$arrThemes = Utility::getCurrentTemplate('public');
		Yii::app()->theme = $arrThemes['folder'];
		$this->layout = $arrThemes['layout'];
		Utility::applyCurrentTheme($this->module);
		
		$setting = <?php echo $this->modelClass; ?>::model()->findByPk(1, array(
			'select' => 'meta_keyword',
		));

		$model=$this->loadModel($id);

		$this->pageTitle = Yii::t('phrase', 'Detail <?php echo $inflector->singularize($label); ?>: {<?php echo $nameColumn;?>}', array('{<?php echo $nameColumn;?>}'=>$model-><?php echo $nameColumn;?>));
		$this->pageDescription = '';
		$this->pageMeta = $setting->meta_keyword;
		$this->render('front_view', array(
			'model'=>$model,
		));
	}

<?php endif; ?>
	/**
	 * Manages all models.
	 */
	public function actionManage() 
	{
		$model=new <?php echo $this->modelClass; ?>('search');
		$model->unsetAttributes();  // clear any default values
		if(Yii::app()->getRequest()->getParam('<?php echo $this->modelClass; ?>')) {
			$model->attributes=Yii::app()->getRequest()->getParam('<?php echo $this->modelClass; ?>');
		}

		$gridColumn = Yii::app()->getRequest()->getParam('GridColumn');
		$columnTemp = array();
		if($gridColumn) {
			foreach($gridColumn as $key => $val) {
				if($gridColumn[$key] == 1)
					$columnTemp[] = $key;
			}
		}
		$columns = $model->getGridColumn($columnTemp);

		$this->pageTitle = Yii::t('phrase', '<?php echo $inflector->pluralize($label); ?>');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_manage', array(
			'model'=>$model,
			'columns' => $columns,
		));
	}
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionAdd() 
	{
		$model=new <?php echo $this->modelClass; ?>;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['<?php echo $this->modelClass; ?>'])) {
			$model->attributes=$_POST['<?php echo $this->modelClass; ?>'];

			$jsonError = CActiveForm::validate($model);
			if(strlen($jsonError) > 2) {
				echo $jsonError;
				/*
				$errors = $model->getErrors();
				$summary['msg'] = "<div class='errorSummary'><strong>".Yii::t('phrase', 'Please fix the following input errors:')."</strong>";
				$summary['msg'] .= "<ul>";
				foreach($errors as $key => $value) {
					$summary['msg'] .= "<li>{$value[0]}</li>";
				}
				$summary['msg'] .= "</ul></div>";

				$message = json_decode($jsonError, true);
				$merge = array_merge_recursive($summary, $message);
				$encode = json_encode($merge);
				echo $encode;
				*/

			} else {
				if(Yii::app()->getRequest()->getParam('enablesave') == 1) {
					if($model->save()) {
						echo CJSON::encode(array(
							'type' => 5,
							'get' => Yii::app()->controller->createUrl('manage'),
							'id' => 'partial-<?php echo $this->class2id($this->modelClass); ?>',
							'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success created.').'</strong></div>',
						));
						/*
						Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success created.'));
						$this->redirect(array('manage'));
						*/
					} else
						print_r($model->getErrors());
				}
			}
			Yii::app()->end();

			/* 
			if($model->save()) {
				Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success created.'));
				//$this->redirect(array('view','id'=>$model-><?php echo $this->tableSchema->primaryKey; ?>));
				$this->redirect(array('manage'));
			}
			*/
		}
		
		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 600;

		$this->pageTitle = Yii::t('phrase', 'Create <?php echo $inflector->singularize($label); ?>');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_add', array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionEdit($id) 
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['<?php echo $this->modelClass; ?>'])) {
			$model->attributes=$_POST['<?php echo $this->modelClass; ?>'];

			$jsonError = CActiveForm::validate($model);
			if(strlen($jsonError) > 2) {
				echo $jsonError;
				/*
				$errors = $model->getErrors();
				$summary['msg'] = "<div class='errorSummary'><strong>".Yii::t('phrase', 'Please fix the following input errors:')."</strong>";
				$summary['msg'] .= "<ul>";
				foreach($errors as $key => $value) {
					$summary['msg'] .= "<li>{$value[0]}</li>";
				}
				$summary['msg'] .= "</ul></div>";

				$message = json_decode($jsonError, true);
				$merge = array_merge_recursive($summary, $message);
				$encode = json_encode($merge);
				echo $encode;
				*/

			} else {
				if(Yii::app()->getRequest()->getParam('enablesave') == 1) {
					if($model->save()) {
						echo CJSON::encode(array(
							'type' => 5,
							'get' => Yii::app()->controller->createUrl('manage'),
							'id' => 'partial-<?php echo $this->class2id($this->modelClass); ?>',
							'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.').'</strong></div>',
						));
						/*
						Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.'));
						$this->redirect(array('manage'));
						*/
					} else
						print_r($model->getErrors());
				}
			}
			Yii::app()->end();

			/* 
			if($model->save()) {
				Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.'));
				//$this->redirect(array('view','id'=>$model-><?php echo $this->tableSchema->primaryKey; ?>));
				$this->redirect(array('manage'));
			}
			*/
		}
		
		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 600;

		$this->pageTitle = Yii::t('phrase', 'Update <?php echo $inflector->singularize($label); ?>: {<?php echo $nameColumn;?>}', array('{<?php echo $nameColumn;?>}'=>$model-><?php echo $nameColumn;?>));
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_edit', array(
			'model'=>$model,
		));
	}

<?php if($this->forBackendController):?>
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) 
	{
		$model=$this->loadModel($id);
		
		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 600;

		$this->pageTitle = Yii::t('phrase', 'Detail <?php echo $inflector->singularize($label); ?>: {<?php echo $nameColumn;?>}', array('{<?php echo $nameColumn;?>}'=>$model-><?php echo $nameColumn;?>));
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_view', array(
			'model'=>$model,
		));
	}

<?php endif;
if(array_key_exists('publish', $this->tableSchema->columns)): ?>
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionRunAction() 
	{
		$id       = $_POST['trash_id'];
		$criteria = null;
		$actions  = Yii::app()->getRequest()->getParam('action');

		if(count($id) > 0) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('<?php echo $this->tableSchema->primaryKey; ?>', $id);

			if($actions == 'publish') {
				<?php echo $this->modelClass; ?>::model()->updateAll(array(
					'publish' => 1,
				),$criteria);
			} elseif($actions == 'unpublish') {
				<?php echo $this->modelClass; ?>::model()->updateAll(array(
					'publish' => 0,
				),$criteria);
			} elseif($actions == 'trash') {
				<?php echo $this->modelClass; ?>::model()->updateAll(array(
					'publish' => 2,
				),$criteria);
			} elseif($actions == 'delete') {
				<?php echo $this->modelClass; ?>::model()->deleteAll($criteria);
			}
		}

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!Yii::app()->getRequest()->getParam('ajax')) {
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('manage'));
		}
	}

<?php endif; ?>
	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) 
	{
		$model=$this->loadModel($id);
		
		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
<?php if(array_key_exists('publish', $this->tableSchema->columns)): ?>
			$model->publish = 2;
<?php if(array_key_exists('modified_id', $this->tableSchema->columns)): ?>
			$model->modified_id = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;
<?php endif; ?>
			
			if($model->update()) {
<?php else: ?>
			if($model->delete()) {
<?php endif; ?>
				echo CJSON::encode(array(
					'type' => 5,
					'get' => Yii::app()->controller->createUrl('manage'),
					'id' => 'partial-<?php echo $this->class2id($this->modelClass); ?>',
					'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success deleted.').'</strong></div>',
				));
				/*
				Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success deleted.'));
				$this->redirect(array('manage'));
				*/
			}
			Yii::app()->end();
		}

		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 350;

		$this->pageTitle = Yii::t('phrase', 'Delete <?php echo $inflector->singularize($label); ?>: {<?php echo $nameColumn;?>}', array('{<?php echo $nameColumn;?>}'=>$model-><?php echo $nameColumn;?>));
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_delete');
	}

<?php 
//echo '<pre>';
//print_r($this->tableSchema->columns);
if(array_key_exists('publish', $this->tableSchema->columns)):
$publishComment = $this->tableSchema->columns['publish']->comment;?>
	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionPublish($id) 
	{
		$model=$this->loadModel($id);
<?php if($publishComment != ''):
$commentArray = explode(',', $publishComment); ?>
		$title = $model->publish == 1 ? Yii::t('phrase', '<?php echo $commentArray['1'];?>') : Yii::t('phrase', '<?php echo $commentArray['0'];?>');
<?php else: ?>
		$title = $model->publish == 1 ? Yii::t('phrase', 'Unpublish') : Yii::t('phrase', 'Publish');
<?php endif; ?>
		$replace = $model->publish == 1 ? 0 : 1;

		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			//change value active or publish
			$model->publish = $replace;
<?php if(array_key_exists('modified_id', $this->tableSchema->columns)): ?>
			$model->modified_id = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;
<?php endif; ?>

			if($model->update()) {
				echo CJSON::encode(array(
					'type' => 5,
					'get' => Yii::app()->controller->createUrl('manage'),
					'id' => 'partial-<?php echo $this->class2id($this->modelClass); ?>',
					'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.').'</strong></div>',
				));
				/*
				Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.'));
				$this->redirect(array('manage'));
				*/
			}
			Yii::app()->end();
		}

		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 350;

		$this->pageTitle = Yii::t('phrase', '{title} <?php echo $inflector->singularize($label); ?>: {<?php echo $nameColumn;?>}', array('{title}'=>$title, '{<?php echo $nameColumn;?>}'=>$model-><?php echo $nameColumn;?>));
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_publish', array(
			'title'=>$title,
			'model'=>$model,
		));
	}

<?php endif;
if(array_key_exists('headline', $this->tableSchema->columns)): ?>
	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionHeadline($id) 
	{
		$model=$this->loadModel($id);

		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			if(isset($id)) {
				//change value active or publish
				$model->headline = 1;
				$model->publish = 1;
<?php if(array_key_exists('modified_id', $this->tableSchema->columns)): ?>
				$model->modified_id = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;
<?php endif; ?>

				if($model->update()) {
					echo CJSON::encode(array(
						'type' => 5,
						'get' => Yii::app()->controller->createUrl('manage'),
						'id' => 'partial-<?php echo $this->class2id($this->modelClass); ?>',
						'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.').'</strong></div>',
					));
					/*
					Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.'));
					$this->redirect(array('manage'));
					*/
				}
			}
			Yii::app()->end();
		}

		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 350;

		$this->pageTitle = Yii::t('phrase', 'Headline <?php echo $inflector->singularize($label); ?>: {<?php echo $nameColumn;?>}', array('{<?php echo $nameColumn;?>}'=>$model-><?php echo $nameColumn;?>));
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_headline');
	}

<?php endif; ?>
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) 
	{
		$model = <?php echo $this->modelClass; ?>::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404, Yii::t('phrase', 'The requested page does not exist.'));
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) 
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='<?php echo $this->class2id($this->modelClass); ?>-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
