<?php
Yii::import('application.libraries.gii.Inflector');

class CrudCode extends CCodeModel
{
	public $model;
	public $controller;
	public $baseControllerClass='Controller';
	public $controllerPath='application.controllers';
	public $viewPath='application.views';
	public $uploadPath=array(
		'directory' => 'public/main',
	);
	public $defaultActions=array(
		'public' => array(
			'redirect' => false,
			'dialog' => false,
			'file' => 'front_index.php, _view.php, front_view.php',
		),
		'suggest' => array(
			'redirect' => false,
			'dialog' => false,
			'file' => false,
		),
		'manage' => array(
			'redirect' => false,
			'dialog' => false,
			'file' => 'admin_manage.php, _option_form.php, _search.php',
		),
		'create' => array(
			'redirect' => true,
			'dialog' => true,
			'file' => 'admin_add.php, _form.php',
		),
		'update' => array(
			'redirect' => true,
			'dialog' => true,
			'file' => 'admin_edit.php, _form.php',
		),
		'view' => array(
			'redirect' => false,
			'dialog' => true,
			'file' => 'admin_view.php, _detail.php',
		),
		'delete' => array(
			'redirect' => false,
			'dialog' => false,
			'file' => 'admin_delete.php',
		)
	);
	public $generateAction=array();
	public $controllerFor;
	public $useModified=false;
	public $link='https://github.com/ommu';

	private $_modelClass;
	private $_table;
	private static $_output;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('model, controller, controllerPath, viewPath', 'filter', 'filter'=>'trim'),
			array('model, controller, baseControllerClass, controllerPath, viewPath, uploadPath, controllerFor, useModified, link', 'required'),
			array('model', 'match', 'pattern'=>'/^\w+[\w+\\.]*$/', 'message'=>'{attribute} should only contain word characters and dots.'),
			array('controller', 'match', 'pattern'=>'/^\w+[\w+\\/]*$/', 'message'=>'{attribute} should only contain word characters and slashes.'),
			array('baseControllerClass', 'match', 'pattern'=>'/^[a-zA-Z_\\\\][\w\\\\]*$/', 'message'=>'{attribute} should only contain word characters and backslashes.'),
			array('controllerPath, viewPath', 'match', 'pattern'=>'/^(\w+[\w\.]*|\*?|\w+\.\*)$/', 'message'=>'{attribute} should only contain word characters, dots, and an optional ending asterisk.'),
			array('baseControllerClass', 'validateReservedWord', 'skipOnError'=>true),
			array('controllerPath', 'validateControllerPath', 'skipOnError'=>true),
			array('viewPath', 'validateViewPath', 'skipOnError'=>true),
			array('model', 'validateModel'),
			array('baseControllerClass, controllerPath, viewPath, uploadPath, link', 'sticky'),
			array('generateAction', 'safe'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'model'=>'Model Class',
			'controller'=>'Controller ID',
			'baseControllerClass'=>'Base Controller Class',
			'controllerPath'=>'Controller Path',
			'viewPath'=>'View Path',
			'uploadPath[directory]'=>'Upload Path (path location)',
			'uploadPath[subfolder]'=>'Use Subfolder with PrimaryKey',
			'generateAction'=>'Generate Action',
			'controllerFor'=>'Controller For',
			'useModified'=>'Modified',
			'link'=>'Link Repository',
		));
	}

	public function requiredTemplates()
	{
		return array(
			'controller.php',
		);
	}

	public function init()
	{
		if(Yii::app()->db===null)
			throw new CHttpException(500,'An active "db" connection is required to run this generator.');
		parent::init();
	}

	public function successMessage()
	{
		$link=CHtml::link('try it now', Yii::app()->createUrl($this->controller), array('target'=>'_blank'));
		return "The controller has been generated successfully. You may $link.";
	}

	public function validateModel($attribute,$params)
	{
		if($this->hasErrors('model'))
			return;

		$class=@Yii::import($this->model,true);
		if(!is_string($class) || !$this->classExists($class))
			$this->addError('model', "Class '{$this->model}' does not exist or has syntax error.");
		elseif(!is_subclass_of($class,'CActiveRecord'))
			$this->addError('model', "'{$this->model}' must extend from CActiveRecord.");
		else
		{
			$table=CActiveRecord::model($class)->tableSchema;
			if($table->primaryKey===null)
				$this->addError('model',"Table '{$table->name}' does not have a primary key.");
			elseif(is_array($table->primaryKey))
				$this->addError('model',"Table '{$table->name}' has a composite primary key which is not supported by crud generator.");
			else
			{
				$this->_modelClass=$class;
				$this->_table=$table;
			}
		}
	}

	public function prepare()
	{
		$this->files=array();
		$templatePath=$this->templatePath;
		$controllerTemplateFile=$templatePath.DIRECTORY_SEPARATOR.'controller.php';
		
		$table=$this->getTableSchema();
		$breadcrumbRelationAttribute = $this->tableRelationAttribute($table->name, '->');
		$generateFiles = $this->getFileGenerate($this->generateAction);
		
		$params=array(
			'modelClass'=>$this->getModelClass(),
			'columns'=>$table->columns,
			'breadcrumbRelationAttribute'=>$breadcrumbRelationAttribute,
			'table'=>$table,
			'module'=>$this->getModuleName($this->getModelClass()),
			'feature'=>$this->getModuleName($this->getModelClass(), true),
		);

		$this->files[]=new CCodeFile(
			$this->controllerFile,
			$this->render($controllerTemplateFile, $params)
		);

		$files=scandir($templatePath);
		//foreach($files as $file)
		foreach($generateFiles as $file)
		{
			//if(is_file($templatePath.'/'.$file) && CFileHelper::getExtension($file)==='php' && $file!=='controller.php')
			if(CFileHelper::getExtension($file)==='php' && $file!=='controller.php')
			{
				$params['fileName'] = $file;
				if(in_array($file, $files)) {
					$this->files[]=new CCodeFile(
						//$this->viewPath.DIRECTORY_SEPARATOR.$file,
						Yii::getPathOfAlias($this->viewPath).DIRECTORY_SEPARATOR.$this->controller.DIRECTORY_SEPARATOR.$file,
						$this->render(join('/', array($templatePath, $file)), $params)
					);
				} else {
					$this->files[]=new CCodeFile(
						//$this->viewPath.DIRECTORY_SEPARATOR.$file,
						Yii::getPathOfAlias($this->viewPath).DIRECTORY_SEPARATOR.$this->controller.DIRECTORY_SEPARATOR.$file,
						$this->render(join('/', array($templatePath, 'admin_publish.php')), $params)
					);
				}
			}
		}
	}

	public function getModelClass()
	{
		return $this->_modelClass;
	}

	public function getControllerClass()
	{
		if(($pos=strrpos($this->controller,'/'))!==false)
			return ucfirst(substr($this->controller,$pos+1)).'Controller';
		else
			return ucfirst($this->controller).'Controller';
	}

	public function getModule()
	{
		if(($pos=strpos($this->controller,'/'))!==false)
		{
			$id=substr($this->controller,0,$pos);
			if(($module=Yii::app()->getModule($id))!==null)
				return $module;
		}
		return Yii::app();
	}

	public function getControllerID()
	{
		if($this->getModule()!==Yii::app())
			$id=substr($this->controller,strpos($this->controller,'/')+1);
		else
			$id=$this->controller;
		if(($pos=strrpos($id,'/'))!==false)
			$id[$pos+1]=strtolower($id[$pos+1]);
		else
			$id[0]=strtolower($id[0]);
		return $id;
	}

	public function getUniqueControllerID()
	{
		$id=$this->controller;
		if(($pos=strrpos($id,'/'))!==false)
			$id[$pos+1]=strtolower($id[$pos+1]);
		else
			$id[0]=strtolower($id[0]);
		return $id;
	}

	public function getControllerFile()
	{
		$module=$this->getModule();
		$id=$this->getControllerID();
		if(($pos=strrpos($id,'/'))!==false)
			$id[$pos+1]=strtoupper($id[$pos+1]);
		else
			$id[0]=strtoupper($id[0]);
		//return $module->getControllerPath().'/'.$id.'Controller.php';
		return Yii::getPathOfAlias($this->controllerPath).'/'.$id.'Controller.php';
	}

	public function getViewPath()
	{
		return $this->getModule()->getViewPath().'/'.$this->getControllerID();
	}

	public function getTableSchema($tableName=null)
	{
		if($tableName == null)
			return $this->_table;

		else {
			$connection=Yii::app()->db;
			return $connection->getSchema()->getTable($tableName, $connection->schemaCachingDuration!==0);
		}
	}

	public function generateInputLabel($modelClass,$column)
	{
		return "CHtml::activeLabelEx(\$model,'{$column->name}')";
	}

	public function generateInputField($modelClass,$column)
	{
		if($column->type==='boolean')
			return "CHtml::activeCheckBox(\$model,'{$column->name}')";
		elseif(stripos($column->dbType,'text')!==false)
			return "CHtml::activeTextArea(\$model,'{$column->name}',array('rows'=>6, 'cols'=>50))";
		else
		{
			if(preg_match('/^(password|pass|passwd|passcode)$/i',$column->name))
				$inputField='activePasswordField';
			else
				$inputField='activeTextField';

			if($column->type!=='string' || $column->size===null)
				return "CHtml::{$inputField}(\$model,'{$column->name}')";
			else
			{
				if(($size=$maxLength=$column->size)>60)
					$size=60;
				return "CHtml::{$inputField}(\$model,'{$column->name}',array('size'=>$size,'maxlength'=>$maxLength))";
			}
		}
	}

	public function generateActiveLabel($modelClass,$column, $type=false)
	{
		$formDialogCondition = 0;
		if($this->generateAction['create']['dialog'] || $this->generateAction['update']['dialog'])
			$formDialogCondition = 1;

		$tableSchema = $this->getTableSchema();
		$foreignKeys = $this->foreignKeys($tableSchema->foreignKeys);

		$commentArray = explode(',', $column->comment);
		$publicAttribute = $column->name;
		if(in_array('trigger[delete]', $commentArray))
			$publicAttribute = $column->name.'_i';
		else if($column->name == 'tag_id') {
			$relationName = $this->setRelation($column->name, true);
			$publicAttribute = $relationName.'_i';
		}

		if($type == false)
			return "\$form->labelEx(\$model, '{$publicAttribute}')";
		else {
			$labelClass = $formDialogCondition ? 'col-lg-4 col-md-4 col-sm-12' : 'col-lg-3 col-md-3 col-sm-12';

			return "\$form->labelEx(\$model, '{$publicAttribute}', array('class'=>'col-form-label {$labelClass}'))";
		}
	}

	public function generateActiveField($modelClass,$column,$form=true)
	{
		$inflector = new Inflector;
		
		$tableSchema = $this->getTableSchema();
		$foreignKeys = $this->foreignKeys($tableSchema->foreignKeys);
		$primaryKey = $tableSchema->primaryKey;

		$commentArray = explode(',', $column->comment);
		if($column->type==='boolean' || $column->dbType == 'tinyint(1)') {		// 01
if($form == true) {
	if($column->dbType == 'tinyint(1)' && $column->defaultValue === null)
		return "echo \$form->textField(\$model, '{$column->name}', array('class'=>'form-control'))";
	else {
		if($column->comment[0] == '"') {
			$functionName = ucfirst($inflector->singularize($inflector->id2camel($column->name, '_')));
			return "echo \$form->radioButtonList(\$model, '{$column->name}', $modelClass::get$functionName(), array('class'=>'form-control'))";
		} else {
			if($column->name == 'permission') {
				return "if(\$model->isNewRecord && !\$model->getErrors())
					\$model->permission = 1;
				echo \$form->radioButtonList(\$model, '{$column->name}', array(
					1 => Yii::t('phrase', 'Yes, the public can view report unless they are made private.'),
					0 => Yii::t('phrase', 'No, the public cannot view report.'),
				), array('class'=>'form-control'))";
			} else
				return "echo \$form->checkBox(\$model, '{$column->name}', array('class'=>'form-control'))";
		}
	}
} else {
	if($column->dbType == 'tinyint(1)' && $column->defaultValue === null)
		if($column->comment != '')
			return "echo \$form->dropDownList(\$model, '{$column->name}', array('1'=>Yii::t('phrase', '".$commentArray[0]."'), '0'=>Yii::t('phrase', '".$commentArray[1]."')), array('prompt'=>'', 'class'=>'form-control'))";
		else
			return "echo \$form->textField(\$model, '{$column->name}', array('class'=>'form-control'))";
	else {
		if($column->comment != '') {
			if($column->comment[0] == '"') {
				$functionName = ucfirst($inflector->singularize($inflector->id2camel($column->name, '_')));
				return "echo \$form->dropDownList(\$model, '{$column->name}', $modelClass::get$functionName(), array('prompt'=>'', 'class'=>'form-control'))";
			} else
				return "echo \$form->dropDownList(\$model, '{$column->name}', array('1'=>Yii::t('phrase', '".$commentArray[0]."'), '0'=>Yii::t('phrase', '".$commentArray[1]."')), array('prompt'=>'', 'class'=>'form-control'))";
		} else
			return "echo \$form->dropDownList(\$model, '{$column->name}', \$this->filterYesNo(), array('prompt'=>'', 'class'=>'form-control'))";
	}
}
		} elseif(stripos($column->dbType,'text')!==false) {		// 02
if($form == true) {
	$textCondition = 0;
	if(trim($column->comment) != '')
		$textCondition = 1;
	if($textCondition == 0 || in_array('text', $commentArray))
		return "echo \$form->textArea(\$model, '{$column->name}', array('rows'=>6, 'cols'=>50, 'class'=>'form-control'))";
	else if(in_array('redactor', $commentArray)) {
		return "\$this->widget('yiiext.imperavi-redactor-widget.ImperaviRedactorWidget', array(
					'model'=>\$model,
					'attribute'=>'{$column->name}',
					'options'=>array(
						'fileUpload'=>Yii::app()->createUrl('post/fileUpload',array(
							'attr'=>'{$column->name}',
						)),
						'fileUploadErrorCallback'=>new CJavaScriptExpression(
							'function(obj,json) { alert(json.error); }'
						),
						'imageUpload'=>Yii::app()->createUrl('post/imageUpload',array(
							'attr'=>'{$column->name}',
						)),
						'imageUploadErrorCallback'=>new CJavaScriptExpression(
							'function(obj,json) { alert(json.error); }'
						),
						'imageManagerJson'=>Yii::app()->createUrl('post/imageList',array(
							'attr'=>'{$column->name}',
						)),
						/*
						'buttons'=>array(
							'html', 'formatting', '|', 
							'bold', 'italic', 'deleted', '|',
							'unorderedlist', 'orderedlist', 'outdent', 'indent', 'alignment', '|',
							'image', 'file', 'link', '|',
						),
						*/
					),
					'plugins' => array(
						'fontcolor' => array('js' => array('fontcolor.js')),
						'fullscreen' => array('js' => array('fullscreen.js')),
						'table' => array('js' => array('table.js')),
						'imagemanager' => array('js' => array('imagemanager.js')),
						'filemanager' => array('js' => array('filemanager.js')),
					),
					'htmlOptions'=>array(
						'class' => 'form-control',
					),
				))";
	} else if(in_array('file', $commentArray)) {
		$return = "if(!\$model->isNewRecord && \$model->old_{$column->name}_i != '') {\n";
		if($this->uploadPathSubfolder):
			$return .= "\t\t\t\t\t\$old_{$column->name}_i = join('/', array(Yii::app()->request->baseUrl, $modelClass::getUploadPath(false), \$model->{$primaryKey}, \$model->old_{$column->name}_i);?>\n";
		else:
			$return .= "\t\t\t\t\t\$old_{$column->name}_i = join('/', array(Yii::app()->request->baseUrl, $modelClass::getUploadPath(false), \$model->old_{$column->name}_i);?>\n";
		endif;
		$return .= "\t\t\t\t\t<div class=\"mb-15\"><img src=\"<?php echo Utility::getTimThumb(\$old_{$column->name}_i, 320, 150, 1);?>\" alt=\"<?php echo \$model->old_{$column->name}_i;?>\"></div>
				<?php }
				echo \$form->fileField(\$model, '{$column->name}', array('class'=>'form-control'))";
		return $return;
	}
} else
	return "echo \$form->textField(\$model, '{$column->name}', array('class'=>'form-control'))";
		} elseif(in_array($column->dbType, array('timestamp','datetime','date'))) {		// 03
if($form == true)
	return "if(!\$model->getErrors())\n\t\t\t\t\t\$model->{$column->name} = !\$model->isNewRecord ? (!in_array(date('Y-m-d', strtotime(\$model->{$column->name})), array('0000-00-00','1970-01-01','0002-12-02','-0001-11-30')) ? date('Y-m-d', strtotime(\$model->{$column->name})) : '') : '';
\t\t\t\techo \$this->filterDatepicker(\$model, '{$column->name}', false)";
else
	return "echo \$this->filterDatepicker(\$model, '{$column->name}', false)";
		} else {		// 04
			if(preg_match('/^(password|pass|passwd|passcode)$/i',$column->name))
				$inputField='passwordField';
			else
				$inputField='textField';

			$smallintCondition = 0;
			$enumCondition = 0;
			$i18n = 0;
			$publicAttribute = $column->name;
			if($column->isForeignKey) {
				$relationName = $this->setRelation($column->name, true);
				if($form == false)
					$publicAttribute = $relationName.'_search';
				if(preg_match('/(smallint)/', $column->dbType)) {
					$smallintCondition = 1;
					$publicAttribute = $column->name;
				}
			} else if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
				$relationName = $this->setRelation($column->name, true);
				if($form == false)
					$publicAttribute = $relationName.'_search';
				if($column->name == 'tag_id')
					$publicAttribute = $relationName.'_i';
			} else {
				if(in_array('trigger[delete]', $commentArray)) {
					$publicAttribute = $column->name.'_i';
					$i18n = 1;
				}
			}

			if(preg_match('/(enum)/', $column->dbType)) {
				$enumCondition = 1;
				$patterns = array();
				$patterns[0] = '(enum)';
				$patterns[1] = '(\')';
				$enumvalue = preg_replace($patterns, '', $column->dbType);
				$enumvalue = trim($enumvalue, ')|(');
				$enumArrays = explode(',', $enumvalue);
				$dropDownOptions = array();
				foreach ($enumArrays as $enumArray)
					$dropDownOptions[$enumArray] = $enumArray;
			}
			if ($enumCondition && is_array($enumArrays) && count($enumArrays) > 0) {
				$dropDownOption = self::export($dropDownOptions, $form);
				$return = "\$$publicAttribute = $dropDownOption;\n";
				if($form == true)
					$return .= "\t\t\t\techo \$form->dropDownList(\$model, '{$publicAttribute}', \$$publicAttribute, array('prompt'=>'', 'class'=>'form-control'))";
				else
					$return .= "\t\t\techo \$form->dropDownList(\$model, '{$publicAttribute}', \$$publicAttribute, array('prompt'=>'', 'class'=>'form-control'))";
					return $return;
			} else if($column->size===null)
				return "echo \$form->{$inputField}(\$model, '{$publicAttribute}', array('class'=>'form-control'))";
			else {
				$maxLength=$column->size;
				if($i18n)
					$maxLength = in_array('redactor', $commentArray) ? '~' : (in_array('text', $commentArray) ? '128' : '64');
if($form == true) {
	if($i18n) {
		if(in_array('redactor', $commentArray)) {
				return "\$this->widget('yiiext.imperavi-redactor-widget.ImperaviRedactorWidget', array(
					'model'=>\$model,
					'attribute'=>'{$column->name}',
					'options'=>array(
						'fileUpload'=>Yii::app()->createUrl('post/fileUpload',array(
							'attr'=>'{$column->name}',
						)),
						'fileUploadErrorCallback'=>new CJavaScriptExpression(
							'function(obj,json) { alert(json.error); }'
						),
						'imageUpload'=>Yii::app()->createUrl('post/imageUpload',array(
							'attr'=>'{$column->name}',
						)),
						'imageUploadErrorCallback'=>new CJavaScriptExpression(
							'function(obj,json) { alert(json.error); }'
						),
						'imageManagerJson'=>Yii::app()->createUrl('post/imageList',array(
							'attr'=>'{$column->name}',
						)),
						/*
						'buttons'=>array(
							'html', 'formatting', '|', 
							'bold', 'italic', 'deleted', '|',
							'unorderedlist', 'orderedlist', 'outdent', 'indent', 'alignment', '|',
							'image', 'file', 'link', '|',
						),
						*/
					),
					'plugins' => array(
						'fontcolor' => array('js' => array('fontcolor.js')),
						'fullscreen' => array('js' => array('fullscreen.js')),
						'table' => array('js' => array('table.js')),
						'imagemanager' => array('js' => array('imagemanager.js')),
						'filemanager' => array('js' => array('filemanager.js')),
					),
					'htmlOptions'=>array(
						'class' => 'form-control',
					),
				))";
		} elseif(in_array('text', $commentArray))
			return "echo \$form->textArea(\$model, '{$publicAttribute}', array('rows'=>6, 'cols'=>50, 'maxlength'=>$maxLength, 'class'=>'form-control'))";
		else
			return "echo \$form->textField(\$model, '{$publicAttribute}', array('maxlength'=>$maxLength, 'class'=>'form-control'))";
	} else {
		if($column->isForeignKey && $smallintCondition) {
			$relationTableName = trim($foreignKeys[$column->name]);
			$relationClassName = $this->generateClassName($relationTableName);
			$relationFunction = ucfirst($inflector->singularize($this->setRelation($relationClassName)));
			return "\$$relationName = $relationClassName::get{$relationFunction}();\n\t\t\t\tif(\$$relationName != null)\n\t\t\t\t\techo \$form->dropDownList(\$model, '{$publicAttribute}', \$$relationName, array('prompt'=>'', 'class'=>'form-control'));\n\t\t\t\telse\n\t\t\t\t\techo \$form->dropDownList(\$model, '{$publicAttribute}', array('prompt'=>''), array('class'=>'form-control'))";
		} else if(($column->isForeignKey && !$smallintCondition) || in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))
			return "echo \$form->{$inputField}(\$model, '{$publicAttribute}', array('class'=>'form-control'))";
		else {
			if($column->name == 'license') {
				return "if(\$model->isNewRecord || (!\$model->isNewRecord && \$model->$column->name == '')) {
					\$model->license = \$this->licenseCode();
					echo \$form->textField(\$model, '$column->name', array('maxlength'=>$maxLength, 'class'=>'form-control'));
				} else
					echo \$form->textField(\$model, '$column->name', array('maxlength'=>$maxLength, 'class'=>'form-control', 'disabled'=>'disabled'))";
			} else
				return "echo \$form->{$inputField}(\$model, '{$publicAttribute}', array('maxlength'=>$maxLength, 'class'=>'form-control'))";
		}
	}
} else
	if($column->isForeignKey && $smallintCondition) {
		$relationTableName = trim($foreignKeys[$column->name]);
		$relationClassName = $this->generateClassName($relationTableName);
		$relationFunction = ucfirst($inflector->singularize($this->setRelation($relationClassName)));
		return "echo \$form->dropDownList(\$model, '{$publicAttribute}', $relationClassName::get$relationFunction(), array('prompt'=>'', 'class'=>'form-control'))";
	} else if($maxLength == '~' || ($column->isForeignKey && !$smallintCondition) || in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))
		return "echo \$form->{$inputField}(\$model, '{$publicAttribute}', array('class'=>'form-control'))";
	else
		return "echo \$form->{$inputField}(\$model, '{$publicAttribute}', array('maxlength'=>$maxLength, 'class'=>'form-control'))";
			}
		}		// end
	}

	public function guessNameColumn($columns)
	{
		foreach($columns as $column)
		{
			if(!strcasecmp($column->name,'name'))
				return $column->name;
		}
		foreach($columns as $column)
		{
			if(!strcasecmp($column->name,'title'))
				return $column->name;
		}
		foreach($columns as $column)
		{
			if($column->isPrimaryKey)
				return $column->name;
		}
		return 'id';
	}

	public function getUploadPathDirectory()
	{
		return $this->uploadPath['directory'];
	}

	public function getUploadPathSubfolder()
	{
		return $this->uploadPath['subfolder'];
	}

	public function getGenerateAction()
	{
		return $this->generateAction;
	}

	public function getControllerFor()
	{
		return $this->controllerFor;
	}

	public function getUseModified()
	{
		return $this->useModified;
	}

	public function getLinkSource()
	{
		return $this->link;
	}

	public function getOtherActions()
	{
		$defaultAction = $this->defaultActions;
		$actions = $this->generateAction;
		
		$items = array();
		foreach($actions as $key=>$val) {
			if(!array_key_exists($key, $defaultAction))
				$items[] = $key;
		}
		
		return $items;
	}

	public function getActions()
	{
		$actions = $this->generateAction;
		
		$items = array('index');
		foreach($actions as $key=>$val) {
			if($val['generate']) {
				if($key == 'create')
					$items[] = 'add';
				else if($key == 'update')
					$items[] = 'edit';
				else
					$items[] = $key;
			}
		}
		
		return $items;
	}

	public function validateControllerPath($attribute,$params)
	{
		if(Yii::getPathOfAlias($this->controllerPath)===false)
			$this->addError('controllerPath','Model Path must be a valid path alias.');
	}

	public function validateViewPath($attribute,$params)
	{
		if(Yii::getPathOfAlias($this->viewPath)===false)
			$this->addError('viewPath','Model Path must be a valid path alias.');
	}

	protected function generateClassName($tableName)
	{
		if(($pos=strpos($tableName,'.'))!==false) // remove schema part (e.g. remove 'public2.' from 'public2.post')
			$tableName=substr($tableName,$pos+1);
		$className='';
		foreach(explode('_',$tableName) as $name)
		{
			if($name!=='')
				$className.=ucfirst($name);
		}
		if(preg_match('/Core/', $className))
			$className = preg_replace('(Core)', '', $className);
		else
			$className = preg_replace('(Ommu)', '', $className);

		return $tableName[0] == '_' ? join('', array('View', $className)) : $className;
	}
	
	public function foreignKeys($foreignKeys)
	{
		$column = [];
		if(!empty($foreignKeys)) {
			foreach($foreignKeys as $key=>$val) {
				// Only variables should be passed by reference
				$arrayValue = array_values($val);
				//$column[array_pop($arrVal)] = array_shift($arrVal);
				$column[$key] = array_shift($arrayValue);
			}
		}
	
		return $column;
	}

	public function relationIndex($key) 
	{
		$relation = explode('_', $key);
		$relation = array_diff($relation, array('ommu','core'));

		if(count($relation) != 1)
			return end($relation);
		else {
			if(is_array($relation))
				return implode('', $relation);
			else
				return $relation;
		}
	}

	public function setRelation($names, $column=false) 
	{
		$inflector = new Inflector;
		if($column == false) {
			$names = $inflector->camel2id($names, '_');
			$return = $this->relationIndex($names);

			return $return != 'cat' ? $return : 'category';
	
		} else {
			$key = $names;
			if (!empty($key) && strcasecmp($key, 'id')) {
				if (substr_compare($key, 'id', -2, 2, true) === 0)
					$key = rtrim(substr($key, 0, -2), '_');
				elseif (substr_compare($key, 'id', 0, 2, true) === 0)
					$key = ltrim(substr($key, 2, strlen($key)), '_');
			}
			$key = $this->relationIndex($key);
			if(strtolower($key) == 'cat')
				$key = 'category';
		
			$key = $inflector->singularize($inflector->id2camel($key, '_'));
	
			return lcfirst($key);
		}
	}

	public function tableAttribute($columns)
	{
		$primaryKey = array();
		foreach($columns as $name=>$column):
			if($column->isPrimaryKey || $column->autoIncrement)
				$primaryKey[] = $column->name;
			if(preg_match('/(name|title)/', $column->name))
				return $column->name;
		endforeach;
		$pk = $primaryKey;
	
		if(!empty($primaryKey))
			return $pk[0];
		else
			return 'id';
	}

	public function tableRelationAttributes($table, $separator='->')
	{
		$foreignKeys = $this->foreignKeys($table->foreignKeys);
		$titleCondition = 0;
		$foreignCondition = 0;

		foreach ($table->columns as $column) {
			$relationColumn = [];
			if(preg_match('/(name|title|body)/', $column->name)) {
				$commentArray = explode(',', $column->comment);
				if(in_array('trigger[delete]', $commentArray)) {
					$relationColumn[$column->name] = $this->i18nRelation($column->name);
					$relationColumn[] = 'message';
				} else {
					if($column->name == 'username')
						$relationColumn['displayname'] = 'displayname';
					else
						$relationColumn[$column->name] = $column->name;

				}
				$titleCondition = 1;
			}
			if(!empty($relationColumn))
				return $relationColumn;
		}
		if(!$titleCondition) {
			foreach ($table->columns as $column) {
				$relationColumn = [];
				if($column->name == 'tag_id') {
					$relationColumn[$column->name] = $this->setRelation($column->name, true);
					$relationColumn[] = 'body';
				}
				if(!empty($relationColumn))
					return $relationColumn;
			}
		}
		if(!$titleCondition) {
			foreach ($table->columns as $column) {
				$relationColumn = [];
				if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
					$relationTableName = trim($foreignKeys[$column->name]);
					if(!$foreignCondition) {
						$relationColumn[$column->name] = $this->setRelation($column->name, true);
						$relationColumn[] = $this->tableRelationAttribute($relationTableName, $separator);
						$foreignCondition = 1;
					}
				}
				if(!empty($relationColumn))
					return $relationColumn;
			}
		}
		if(!$titleCondition) {
			foreach ($table->columns as $column) {
				$relationColumn = [];
				if($column->name == 'user_id') {
					$relationColumn[$column->name] = $this->setRelation($column->name, true);
					$relationColumn[] = 'displayname';
				}
				if(!empty($relationColumn))
					return $relationColumn;
			}
		}
	}

	public function tableRelationAttribute($tableName, $separator='->')
	{
		$tables=array($this->getTableSchema($tableName));
		$table = $tables[0];

		$relationColumn = $this->tableRelationAttributes($table, $separator);

		if(!empty($relationColumn))
			return implode($separator, $relationColumn);

		$pk = $table->primaryKey;
		return $pk;
	}

	public function i18nRelation($column, $relation=true)
	{
		return preg_match('/(name|title)/', $column) ? 'title' : (preg_match('/(desc|description)/', $column) ? ($column != 'description' ? 'description' :  ($relation == true ? $column.'Rltn' : $column)) : ($relation == true ? $column.'Rltn' : $column));
	}

	public function getFileGenerate($generate)
	{
		$items = array();
		foreach($generate as $key=>$val) {
			if($val['generate'])
				$items = array_merge($items, array_map('trim', explode(',', $val['file'])));
		}
		asort($items);
		
		return array_unique($items);
	}

	public function shortLabel($modelClass)
	{
		$inflector = new Inflector;
		$names = $inflector->camel2id($modelClass, '_');
		$nameArray = explode('_', $names);

		if(count($nameArray) != 1) {
			array_shift($nameArray);
			if(is_array($nameArray))
				return implode(' ', $nameArray);
			else
				return $nameArray;

		} else {
			if(is_array($nameArray))
				return implode(' ', $nameArray);
			else
				return $nameArray;
		}
	}

	/**
	 * Exports a variable as a string representation.
	 *
	 * The string is a valid PHP expression that can be evaluated by PHP parser
	 * and the evaluation result will give back the variable value.
	 *
	 * This method is similar to `var_export()`. The main difference is that
	 * it generates more compact string representation using short array syntax.
	 *
	 * It also handles objects by using the PHP functions serialize() and unserialize().
	 *
	 * PHP 5.4 or above is required to parse the exported value.
	 *
	 * @param mixed $var the variable to be exported.
	 * @return string a string representation of the variable
	 */
	public static function export($var, $form=true)
	{
		self::$_output = '';
		self::exportInternal($var, 0, $form);
		return self::$_output;
	}

	/**
	 * @param mixed $var variable to be exported
	 * @param int $level depth level
	 */
	private static function exportInternal($var, $level, $form)
	{
		switch (gettype($var)) {
			case 'NULL':
				self::$_output .= 'null';
				break;
			case 'array':
				if (empty($var)) {
					self::$_output .= '[]';
				} else {
					$keys = array_keys($var);
					$outputKeys = ($keys !== range(0, count($var) - 1));
					$spaces = str_repeat(' ', $level * 4);
					self::$_output .= 'array(';
					foreach ($keys as $key) {
						if($form == true)
							self::$_output .= "\n\t\t\t\t" . $spaces . '	';
						else
							self::$_output .= "\n\t\t\t" . $spaces . '	';
						if ($outputKeys) {
							self::exportInternal($key, 0, $form);
							self::$_output .= ' => Yii::t(\'phrase\', ';
						}
						self::exportInternal($var[$key], $level + 1, $form);
						self::$_output .= '),';
					}
					if($form == true)
						self::$_output .= "\n\t\t\t\t" . $spaces . ')';
					else
						self::$_output .= "\n\t\t\t" . $spaces . ')';
				}
				break;
			case 'object':
				if ($var instanceof \Closure) {
					self::$_output .= self::exportClosure($var);
				} else {
					try {
						$output = 'unserialize(' . var_export(serialize($var), true) . ')';
					} catch (\Exception $e) {
						// serialize may fail, for example: if object contains a `\Closure` instance
						// so we use a fallback
						if ($var instanceof Arrayable) {
							self::exportInternal($var->toArray(), $level, $form);
							return;
						} elseif ($var instanceof \IteratorAggregate) {
							$varAsArray = [];
							foreach ($var as $key => $value) {
								$varAsArray[$key] = $value;
							}
							self::exportInternal($varAsArray, $level, $form);
							return;
						} elseif ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__toString')) {
							$output = var_export($var->__toString(), true);
						} else {
							$outputBackup = self::$_output;
							$output = var_export(self::dumpAsString($var), true);
							self::$_output = $outputBackup;
						}
					}
					self::$_output .= $output;
				}
				break;
			default:
				self::$_output .= var_export($var, true);
		}
	}

	/**
	 * Exports a [[Closure]] instance.
	 * @param \Closure $closure closure instance.
	 * @return string
	 */
	private static function exportClosure(\Closure $closure)
	{
		$reflection = new \ReflectionFunction($closure);

		$fileName = $reflection->getFileName();
		$start = $reflection->getStartLine();
		$end = $reflection->getEndLine();

		if ($fileName === false || $start === false || $end === false) {
			return 'function() {/* Error: unable to determine Closure source */}';
		}

		--$start;

		$source = implode("\n", array_slice(file($fileName), $start, $end - $start));
		$tokens = token_get_all('<?php ' . $source);
		array_shift($tokens);

		$closureTokens = [];
		$pendingParenthesisCount = 0;
		foreach ($tokens as $token) {
			if (isset($token[0]) && $token[0] === T_FUNCTION) {
				$closureTokens[] = $token[1];
				continue;
			}
			if ($closureTokens !== []) {
				$closureTokens[] = isset($token[1]) ? $token[1] : $token;
				if ($token === '}') {
					$pendingParenthesisCount--;
					if ($pendingParenthesisCount === 0) {
						break;
					}
				} elseif ($token === '{') {
					$pendingParenthesisCount++;
				}
			}
		}

		return implode('', $closureTokens);
	}

	public function getModuleName($modelClass, $feature=false)
	{
		$inflector = new Inflector;

		$data = $inflector->singularize($this->class2name($modelClass));
		$arrayData = explode(' ', $data);
		$key = $arrayData[0];

		if($feature == false)
			return $key;
		else {
			$shift = array_shift($arrayData);
			if($key == $shift)
				return implode(' ', $arrayData);
			else
				return $data;
		}
	}
}