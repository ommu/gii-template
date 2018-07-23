<?php
/**
 * This is the template for generating a controller class file for CRUD feature.
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

$label = $this->class2name($modelClass);
$nameColumn = $this->tableAttribute($columns);
$otherActions = $this->otherActions;
$shortLabel = ucwords($this->shortLabel($modelClass));
$relationColumn = $this->tableRelationAttributes($table, '->');
$controllerFor = $this->controllerFor;

$primaryKey = $table->primaryKey;
if(!$primaryKey)
	$primaryKey = key($columns);

$isStatisticTable = 0;
if($columns[$primaryKey]->comment == 'trigger')
	$isStatisticTable = 1;

$uploadCondition = 0;
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if($column->dbType == 'text' && in_array('file', $commentArray))
		$uploadCondition = 1;
endforeach;

echo "<?php\n"; ?>
/**
 * <?php echo $this->controllerClass."\n"; ?>
 * @var $this <?php echo $this->controllerClass."\n"; ?>
 * @var $model <?php echo $modelClass."\n"; ?>
 * @var $form CActiveForm
 *
 * Reference start
 * TOC :
 *	Index
<?php if($this->generateAction['suggest']['generate'])
	echo " *\tSuggest\n";
if($this->generateAction['public']['generate'])
	echo " *\tView\n";
if($this->generateAction['manage']['generate'])
	echo " *\tManage\n";
if($this->generateAction['create']['generate'])
	echo " *\tAdd\n";
if($this->generateAction['update']['generate'])
	echo " *\tEdit\n";
if($this->generateAction['view']['generate'])
	echo " *\tView\n";
if($this->generateAction['delete']['generate']) 
	echo " *\tDelete\n";
if(!empty($otherActions)):
	foreach($otherActions as $action):
		if($this->generateAction[$action]['generate'])
			echo " *\t".ucfirst($action)."\n";
	endforeach;
endif;?>
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
<?php if($this->generateAction['manage']['generate'] || ($this->generateAction['manage']['generate'] && $this->generateAction['public']['generate'])):?>
		if(!Yii::app()->user->isGuest) {
<?php if(count($controllerFor) == 1):?>
			if(Yii::app()->user->level == <?php echo $controllerFor[0];?>) {
<?php else:?>
			if(in_array(Yii::app()->user->level, array(<?php echo implode(',', $controllerFor);?>))) {
<?php endif; ?>
				$arrThemes = $this->currentTemplate('admin');
				Yii::app()->theme = $arrThemes['folder'];
				$this->layout = $arrThemes['layout'];
			}
		} else
			$this->redirect(Yii::app()->createUrl('site/login'));
<?php else:?>
		$arrThemes = $this->currentTemplate('public');
		Yii::app()->theme = $arrThemes['folder'];
		$this->layout = $arrThemes['layout'];
		$this->applyViewPath(__dir__);
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

<?php if($this->generateAction['manage']['generate'] || ($this->generateAction['manage']['generate'] && $this->generateAction['public']['generate'])):?>
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() 
	{
		return array(
<?php if($this->generateAction['public']['generate']):?>
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
<?php endif; ?>
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('<?php echo implode('\',\'', $this->actions);?>'),
				'users'=>array('@'),
<?php if(count($controllerFor) == 1):?>
				'expression'=>'Yii::app()->user->level == <?php echo $controllerFor[0];?>',
<?php else:?>
				'expression'=>'in_array(Yii::app()->user->level, array(<?php echo implode(',', $controllerFor);?>))',
<?php endif; ?>
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

<?php endif; ?>
	/**
	 * Lists all models.
	 */
	public function actionIndex() 
	{
<?php if($this->generateAction['public']['generate']):
	if($this->generateAction['manage']['generate']):?>
		$arrThemes = $this->currentTemplate('public');
		Yii::app()->theme = $arrThemes['folder'];
		$this->layout = $arrThemes['layout'];
		$this->applyCurrentTheme($this->module);
		$this->applyViewPath(__dir__);

<?php endif; ?>
		$setting = <?php echo $modelClass; ?>::model()->findByPk(1, array(
			'select' => 'meta_description, meta_keyword',
		));

		$criteria=new CDbCriteria;
		$criteria->condition = 'publish = :publish';
		$criteria->params = array(':publish'=>1);
		$criteria->order = 'creation_date DESC';

		$dataProvider = new CActiveDataProvider('<?php echo $modelClass; ?>', array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>10,
			),
		));

		$this->pageTitle = Yii::t('phrase', '<?php echo $inflector->pluralize($shortLabel); ?>');
		$this->pageDescription = $setting->meta_description;
		$this->pageMeta = $setting->meta_keyword;
		$this->render('front_index', array(
			'dataProvider'=>$dataProvider,
		));
<?php else:?>
		$this->redirect(array('manage'));
<?php endif; ?>
	}

<?php if($this->generateAction['public']['generate']):?>
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) 
	{
<?php if($this->generateAction['manage']['generate']):?>
		$arrThemes = $this->currentTemplate('public');
		Yii::app()->theme = $arrThemes['folder'];
		$this->layout = $arrThemes['layout'];
		$this->applyCurrentTheme($this->module);
		$this->applyViewPath(__dir__);

<?php endif; ?>
		$setting = <?php echo $modelClass; ?>::model()->findByPk(1, array(
			'select' => 'meta_keyword',
		));

		$model=$this->loadModel($id);

		$this->pageTitle = Yii::t('phrase', 'Detail <?php echo $inflector->singularize($shortLabel); ?>: {<?php echo key($relationColumn);?>}', array('{<?php echo key($relationColumn);?>}'=>$model-><?php echo implode('->', $relationColumn);?>));
		$this->pageDescription = '';
		$this->pageMeta = $setting->meta_keyword;
		$this->render('front_view', array(
			'model'=>$model,
		));
	}

<?php endif;
if($this->generateAction['suggest']['generate']):?>
	/**
	 * Suggest a particular model.
	 * @param integer $limit
<?php if(array_key_exists('publish', $columns)):?>
	 * @param integer $publish
<?php endif;?>
	 */
	public function actionSuggest($limit=10<?php echo array_key_exists('publish', $columns) ? ', $publish=1' : '';?>) 
	{
		if(Yii::app()->request->isAjaxRequest) {
			$term = Yii::app()->getRequest()->getParam('term');
			if($term) {
				$criteria = new CDbCriteria;
				$criteria->select = 't.<?php echo $table->primaryKey; ?>, t.<?php echo key($relationColumn);?>';
<?php if(array_key_exists('publish', $columns)):?>
				$criteria->compare('t.publish', $publish);
<?php endif;?>
				$criteria->compare('<?php echo count($relationColumn) > 1 ? implode('.', $relationColumn) : 't.'.implode('.', $relationColumn);?>', $term, true);
				$criteria->limit = $limit;
				$criteria->order = "t.<?php echo $table->primaryKey; ?> ASC";
				$model = <?php echo $modelClass; ?>::model()->findAll($criteria);

				if($model) {
					foreach($model as $val) {
						$result[] = array('id'=>$val-><?php echo $table->primaryKey; ?>, 'value'=>$val-><?php echo implode('->', $relationColumn);?>);
					}
				} else
					$result[] = array('id'=>0, 'value'=>$term);
			}
			echo CJSON::encode($result);
			Yii::app()->end();
			
		} else
			throw new CHttpException(404, Yii::t('phrase', 'The requested page does not exist.'));
	}

<?php endif;
if($this->generateAction['manage']['generate']):?>
	/**
	 * Manages all models.
	 */
	public function actionManage() 
	{
		$model=new <?php echo $modelClass; ?>('search');
		$model->unsetAttributes();	// clear any default values
		$<?php echo $modelClass; ?> = Yii::app()->getRequest()->getParam('<?php echo $modelClass; ?>');
		if($<?php echo $modelClass; ?>)
			$model->attributes=$<?php echo $modelClass; ?>;

		$columns = $model->getGridColumn($this->gridColumnTemp());

		$this->pageTitle = Yii::t('phrase', '<?php echo $inflector->pluralize($label); ?>');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_manage', array(
			'model'=>$model,
			'columns' => $columns,
		));
	}

<?php endif;
if($this->generateAction['create']['generate']):?>
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionAdd() 
	{
		$model=new <?php echo $modelClass; ?>;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['<?php echo $modelClass; ?>'])) {
			$model->attributes=$_POST['<?php echo $modelClass; ?>'];

<?php if(!$uploadCondition):?>
			$jsonError = CActiveForm::validate($model);
			if(strlen($jsonError) > 2) {
<?php if($this->generateAction['create']['dialog']):?>
				echo $jsonError;

<?php else:?>
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

<?php endif;?>
			} else {
				if(Yii::app()->getRequest()->getParam('enablesave') == 1) {
					if($model->save()) {
						echo CJSON::encode(array(
							'type' => 5,
<?php if($this->generateAction['create']['redirect'] == 'manage'):?>
							'get' => Yii::app()->controller->createUrl('manage'),
<?php elseif($this->generateAction['create']['redirect'] == 'update'):?>
							'get' => Yii::app()->controller->createUrl('edit', array('id'=>$model-><?php echo $table->primaryKey; ?>)),
<?php elseif($this->generateAction['create']['redirect'] == 'view'):?>
							'get' => Yii::app()->controller->createUrl('view', array('id'=>$model-><?php echo $table->primaryKey; ?>)),
<?php endif;?>
							'id' => 'partial-<?php echo $this->class2id($modelClass); ?>',
							'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success created.').'</strong></div>',
						));
					} else
						print_r($model->getErrors());
				}
			}
			Yii::app()->end();
<?php else:?>
			if($model->save()) {
				Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success created.'));
<?php if($this->generateAction['create']['redirect'] == 'manage'):?>
				$this->redirect(array('manage'));
<?php elseif($this->generateAction['create']['redirect'] == 'update'):?>
				$this->redirect(array('edit','id'=>$model-><?php echo $table->primaryKey; ?>));
<?php elseif($this->generateAction['create']['redirect'] == 'view'):?>
				$this->redirect(array('view','id'=>$model-><?php echo $table->primaryKey; ?>));
<?php endif;?>
			}
<?php endif;?>
		}

<?php if($this->generateAction['create']['dialog']):?>
		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 600;

<?php endif;?>
		$this->pageTitle = Yii::t('phrase', 'Create <?php echo $inflector->singularize($shortLabel); ?>');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_add', array(
			'model'=>$model,
		));
	}

<?php endif;
if($this->generateAction['update']['generate']):?>
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

		if(isset($_POST['<?php echo $modelClass; ?>'])) {
			$model->attributes=$_POST['<?php echo $modelClass; ?>'];

<?php if(!$uploadCondition):?>
			$jsonError = CActiveForm::validate($model);
			if(strlen($jsonError) > 2) {
<?php if($this->generateAction['update']['dialog']):?>
				echo $jsonError;

<?php else:?>
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

<?php endif;?>
			} else {
				if(Yii::app()->getRequest()->getParam('enablesave') == 1) {
					if($model->save()) {
						echo CJSON::encode(array(
							'type' => 5,
<?php if($this->generateAction['update']['redirect'] == 'manage'):?>
							'get' => Yii::app()->controller->createUrl('manage'),
<?php elseif($this->generateAction['update']['redirect'] == 'update'):?>
							'get' => Yii::app()->controller->createUrl('edit', array('id'=>$model-><?php echo $table->primaryKey; ?>)),
<?php elseif($this->generateAction['update']['redirect'] == 'view'):?>
							'get' => Yii::app()->controller->createUrl('view', array('id'=>$model-><?php echo $table->primaryKey; ?>)),
<?php endif;?>
							'id' => 'partial-<?php echo $this->class2id($modelClass); ?>',
							'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.').'</strong></div>',
						));
					} else
						print_r($model->getErrors());
				}
			}
			Yii::app()->end();
<?php else:?>
			if($model->save()) {
				Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.'));
<?php if($this->generateAction['update']['redirect'] == 'manage'):?>
				$this->redirect(array('manage'));
<?php elseif($this->generateAction['update']['redirect'] == 'update'):?>
				$this->redirect(array('edit','id'=>$model-><?php echo $table->primaryKey; ?>));
<?php elseif($this->generateAction['update']['redirect'] == 'view'):?>
				$this->redirect(array('view','id'=>$model-><?php echo $table->primaryKey; ?>));
<?php endif;?>
			}
<?php endif;?>
		}

<?php if($this->generateAction['update']['dialog']):?>
		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 600;

<?php endif;?>
		$this->pageTitle = Yii::t('phrase', 'Update <?php echo $inflector->singularize($shortLabel); ?>: {<?php echo key($relationColumn);?>}', array('{<?php echo key($relationColumn);?>}'=>$model-><?php echo implode('->', $relationColumn);?>));
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_edit', array(
			'model'=>$model,
		));
	}

<?php endif;
if($this->generateAction['view']['generate']):?>
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) 
	{
		$model=$this->loadModel($id);

<?php if($this->generateAction['view']['dialog']):?>
		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 600;

<?php endif;?>
		$this->pageTitle = Yii::t('phrase', 'Detail <?php echo $inflector->singularize($shortLabel); ?>: {<?php echo key($relationColumn);?>}', array('{<?php echo key($relationColumn);?>}'=>$model-><?php echo implode('->', $relationColumn);?>));
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_view', array(
			'model'=>$model,
		));
	}

<?php endif;
if($this->generateAction['delete']['generate']): ?>
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
<?php if(array_key_exists('publish', $columns) && $this->generateAction['publish']['generate']): ?>
			$model->publish = 2;
<?php if(array_key_exists('modified_id', $columns)): ?>
			$model->modified_id = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;
<?php endif; ?>
			
			if($model->update()) {
<?php else: ?>
			if($model->delete()) {
<?php endif; ?>
				echo CJSON::encode(array(
					'type' => 5,
					'get' => Yii::app()->controller->createUrl('manage'),
					'id' => 'partial-<?php echo $this->class2id($modelClass); ?>',
					'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success deleted.').'</strong></div>',
				));
<?php /*
				Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success deleted.'));
				$this->redirect(array('manage'));
*/?>
			}
			Yii::app()->end();
		}

		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 350;

		$this->pageTitle = Yii::t('phrase', 'Delete <?php echo $inflector->singularize($shortLabel); ?>: {<?php echo key($relationColumn);?>}', array('{<?php echo key($relationColumn);?>}'=>$model-><?php echo implode('->', $relationColumn);?>));
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_delete');
	}

<?php endif;
if(!$isStatisticTable && array_key_exists('publish', $columns)): ?>
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionRunaction() 
	{
		$id       = $_POST['trash_id'];
		$criteria = null;
		$actions  = Yii::app()->getRequest()->getParam('action');

		if(count($id) > 0) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('<?php echo $table->primaryKey; ?>', $id);

			if($actions == 'publish') {
				<?php echo $modelClass; ?>::model()->updateAll(array(
					'publish' => 1,
				), $criteria);
			} elseif($actions == 'unpublish') {
				<?php echo $modelClass; ?>::model()->updateAll(array(
					'publish' => 0,
				), $criteria);
			} elseif($actions == 'trash') {
				<?php echo $modelClass; ?>::model()->updateAll(array(
					'publish' => 2,
				), $criteria);
			} elseif($actions == 'delete') {
				<?php echo $modelClass; ?>::model()->deleteAll($criteria);
			}
		}

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!Yii::app()->getRequest()->getParam('ajax'))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('manage'));
	}

<?php endif;
if(!empty($otherActions)):
	foreach($columns as $name=>$column):
		if(in_array($column->name, $otherActions) && $this->generateAction[$column->name]['generate']):
			$actionName = $inflector->id2camel($column->name, '_');
			$publish = $column->comment;
			if($column->name == 'publish' && $column->comment == '')
				$publish = 'Publish,Unpublish';
			if($column->name == 'headline' && $column->comment == '')
				$publish = 'Headline,Unheadline';
			$publishArray = explode(',', $publish);?>
	/**
	 * <?php echo $actionName;?> a particular model.
	 * If <?php echo lcfirst($actionName);?> is successful, the browser will be redirected to the '<?php echo $this->generateAction[$column->name]['redirect'];?>' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function action<?php echo $actionName;?>($id) 
	{
		$model=$this->loadModel($id);
		$title = $model-><?php echo $column->name;?> == 1 ? Yii::t('phrase', '<?php echo $publishArray['1'];?>') : Yii::t('phrase', '<?php echo $publishArray['0'];?>');
		$replace = $model-><?php echo $column->name;?> == 1 ? 0 : 1;

		if(Yii::app()->request->isPostRequest) {
			// we only allow <?php echo lcfirst($actionName);?> via POST request
			$model-><?php echo $column->name;?> = $replace;
<?php if(array_key_exists('modified_id', $columns)): ?>
			$model->modified_id = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;
<?php endif; ?>

			if($model->update()) {
				echo CJSON::encode(array(
					'type' => 5,
<?php if($this->generateAction[$column->name]['redirect'] == 'manage'):?>
					'get' => Yii::app()->controller->createUrl('manage'),
<?php elseif($this->generateAction[$column->name]['redirect'] == 'update'):?>
					'get' => Yii::app()->controller->createUrl('edit', array('id'=>$model-><?php echo $table->primaryKey; ?>)),
<?php elseif($this->generateAction[$column->name]['redirect'] == 'view'):?>
					'get' => Yii::app()->controller->createUrl('view', array('id'=>$model-><?php echo $table->primaryKey; ?>)),
<?php endif;?>
					'id' => 'partial-<?php echo $this->class2id($modelClass); ?>',
					'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.').'</strong></div>',
				));
<?php /*
				Yii::app()->user->setFlash('success', Yii::t('phrase', '<?php echo ucfirst(strtolower($inflector->singularize($label))); ?> success updated.'));
				$this->redirect(array('manage'));
*/?>
			}
			Yii::app()->end();
		}

		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 350;

		$this->pageTitle = Yii::t('phrase', '{title} <?php echo $inflector->singularize($shortLabel); ?>: {<?php echo key($relationColumn);?>}', array('{title}'=>$title, '{<?php echo key($relationColumn);?>}'=>$model-><?php echo implode('->', $relationColumn);?>));
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_<?php echo $column->name;?>', array(
			'title'=>$title,
			'model'=>$model,
		));
	}
	
<?php	endif;
	endforeach;
endif;?>
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) 
	{
		$model = <?php echo $modelClass; ?>::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='<?php echo $this->class2id($modelClass); ?>-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

}
