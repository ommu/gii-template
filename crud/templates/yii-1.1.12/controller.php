<?php
/**
 * This is the template for generating a controller class file for CRUD feature.
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php\n"; ?>
/* <?php echo $this->controllerClass; ?>
* Handle <?php echo $this->controllerClass; ?>
* Copyright (c) 2012, SWEVEL. All rights reserved.
* version: 0.0.1
* Reference start
*
* TOC :
*	Index
*	View
*	AdminManage
*	AdminAdd
*	AdminEdit
*	AdminView
*	AdminDelete
*
*	LoadModel
*	performAjaxValidation
*
* ----------------------------------------------------------------------------------------------------------
*/

class <?php echo $this->controllerClass; ?> extends /*SBaseController*/ <?php echo $this->baseControllerClass."\n"; ?> {
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	//public $layout='//layouts/column2';
	//public $layout='admin';
	public $defaultAction = 'index';

	/**
	 * Initialize admin page theme
	 */
	public function init() {
		if(!Yii::app()->user->isGuest) {
			$groupPage = Yii::app()->user->id == 1 ? 'admin_sweeto' : 'back_office';
			$arrThemes = Utility::getCurrentTemplate($groupPage);
			Yii::app()->theme = $arrThemes['template'];
			$this->layout = $arrThemes['layout'];
		}
		/* $arrThemes = Utility::getCurrentTemplate('public');
		Yii::app()->theme = $arrThemes['template'];
		$this->layout = $arrThemes['layout']; */
	}	

	/**
	 * @return array action filters
	 */
	public function filters() {
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
	public function accessRules() {
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('adminmanage','adminadd','adminedit','adminview','admindelete','publish'),
				'users'=>array('@'),
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
	public function actionIndex() {
		// Set public themes
		$arrThemes = Utility::getCurrentTemplate('public');
		Yii::app()->theme = $arrThemes['template'];
		$this->layout = $arrThemes['layout'];
		
		//$criteria = new CDbCriteria;
		//$criteria->compare('$name',\$this->$name,true);
		
		$dataProvider=new CActiveDataProvider('<?php echo $this->modelClass; ?>', array(
			//'criteria' => $criteria,
			//'pagination' => array(
			//	'size' => 10
			//)
		));
		$this->render('front_index',array(
			'dataProvider'=>$dataProvider,
		));
		//$this->redirect(array('adminmanage'));
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		// Set public themes
		$arrThemes = Utility::getCurrentTemplate('public');
		Yii::app()->theme = $arrThemes['template'];
		$this->layout = $arrThemes['layout'];

		$this->pageTitle = '<?php echo $label; ?> List';
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('front_view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdminManage() {
		$this->layout = 'office_manage';
		$model=new <?php echo $this->modelClass; ?>('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['<?php echo $this->modelClass; ?>'])) {
			$model->attributes=$_GET['<?php echo $this->modelClass; ?>'];
		}

		$columnTemp = array();
		if(isset($_GET['GridColumn'])) {
			foreach($_GET['GridColumn'] as $key => $val) {
				if($_GET['GridColumn'][$key] == 1) {
					$columnTemp[] = $key;
				}
			}
		}
		$columns = $model->getGridColumn($columnTemp);

		if(isset($_GET['type'])) {
			$message['data'] = $this->renderPartial('ajax_admin_manage',array(
				'model'=>$model,
				'columns' => $columns,
			), true, false);
			echo CJSON::encode($message);

		} else {
			$this->pageTitle = '<?php echo $label; ?> Manage';
			$this->pageDescription = '';
			$this->pageMeta = '';
			$this->render('admin_manage',array(
				'model'=>$model,
				'columns' => $columns,
			));
		}

	}	
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionAdminAdd() {
		$model=new <?php echo $this->modelClass; ?>;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['<?php echo $this->modelClass; ?>'])) {
			$model->attributes=$_POST['<?php echo $this->modelClass; ?>'];
			if($model->save()) {
				Yii::app()->user->setFlash('success', Yii::t('', '<?php echo $this->modelClass; ?> success created.'));
				$this->redirect(array('adminview','id'=>$model-><?php echo $this->tableSchema->primaryKey; ?>));
				//$this->redirect(array('adminmanage'));
			}
		}

		$this->pageTitle = '<?php echo $label; ?> Create';
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_add',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionAdminEdit($id) {
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['<?php echo $this->modelClass; ?>'])) {
			$model->attributes=$_POST['<?php echo $this->modelClass; ?>'];
			if($model->save()) {
				Yii::app()->user->setFlash('success', Yii::t('', '<?php echo $this->modelClass; ?> success updated.'));
				$this->redirect(array('adminview','id'=>$model-><?php echo $this->tableSchema->primaryKey; ?>));
				//$this->redirect(array('adminmanage'));
			}
		}

		$this->pageTitle = '<?php echo $label; ?> Update';
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_edit',array(
			'model'=>$model,
		));
	}
	
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionAdminView($id) {
		$this->pageTitle = '<?php echo $label; ?> view';
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_view',array(
			'model'=>$this->loadModel($id),
		));
	}	

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionAdminDelete($id) {
		
		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			if(isset($id)) {
				$this->loadModel($id)->delete();

				echo CJSON::encode(array(
					'type' => 3,
					'id' => 'partial-<?php echo $this->class2id($this->modelClass); ?>',
					'msg' => '<div class="errorSummary success"><strong>'.Yii::t('', '<?php echo $this->modelClass; ?> success deleted.').'</strong></div>',
					'get' => Yii::app()->controller->createUrl('adminmanage',array('type'=>'ajax')),
				));
			}

		}else {
			$data = '<form action="'.Yii::app()->controller->createUrl('admindelete',array('id'=>$id)).'" method="post">';
			$data .= '<div class="dialog-header">'.Yii::t('', 'Hapus <?php echo $this->modelClass; ?>').'</div>';
			$data .= '<div class="dialog-content">';
			$data .= 'Apakah anda yakin ingin menghapus item ini?';
			$data .= '</div>';
			$data .= '<div class="dialog-submit">';
			$data .= '<input type="submit" value="Delete" />';
			$data .= '<input id="closed" type="button" value="Keluar" />';
			$data .= '</div>';
			$data .= '</form>';

			$result['data'] = $data;
			echo CJSON::encode($result);
		}
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionPublish($id) 
	{
		$model=$this->loadModel($id);
		if($model->publish == 1) {
			$title = 'Unpublish';
			$replace = 0;
		} else {
			$title = 'Publish';
			$replace = 1;
		}

		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			if(isset($id)) {
				//change value active or publish
				$model->publish = $replace;

				if($model->update()) {
					echo CJSON::encode(array(
						'type' => 2,
						'id' => 'partial-<?php echo $this->class2id($this->modelClass); ?>',
						'title' => $model->publish == 1 ? 'Unpublish' : 'Publish',
						'replace' => $model->publish == 1 ? '<img src="'.Yii::app()->theme->baseUrl.'/images/icons/publish.png" alt="Publish">' : '<img src="'.Yii::app()->theme->baseUrl.'/images/icons/unpublish.png" alt="Unpublish">',
					));
				}
			}

		} else {
			$data = '<form action="'.Yii::app()->controller->createUrl('publish',array('id'=>$id)).'" method="post">';
			$data .= '<div class="dialog-header">'.$title.'</div>';
			$data .= '<div class="dialog-content">';
			$data .= 'Apakah anda yakin ingin mempublish item ini?';
			$data .= '</div>';
			$data .= '<div class="dialog-submit">';
			$data .= '<input type="submit" value="'.$title.'" />';
			$data .= '<input id="closed" type="button" value="Keluar" />';
			$data .= '</div>';
			$data .= '</form>';

			$result['data'] = $data;
			echo CJSON::encode($result);
		}
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=<?php echo $this->modelClass; ?>::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='<?php echo $this->class2id($this->modelClass); ?>-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}