<?php

class CrudCode extends CCodeModel
{
	public $model;
	public $controller;
	public $baseControllerClass='Controller';

	private $_modelClass;
	private $_table;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('model, controller', 'filter', 'filter'=>'trim'),
			array('model, controller, baseControllerClass', 'required'),
			array('model', 'match', 'pattern'=>'/^\w+[\w+\\.]*$/', 'message'=>'{attribute} should only contain word characters and dots.'),
			array('controller', 'match', 'pattern'=>'/^\w+[\w+\\/]*$/', 'message'=>'{attribute} should only contain word characters and slashes.'),
			array('baseControllerClass', 'match', 'pattern'=>'/^[a-zA-Z_\\\\][\w\\\\]*$/', 'message'=>'{attribute} should only contain word characters and backslashes.'),
			array('baseControllerClass', 'validateReservedWord', 'skipOnError'=>true),
			array('model', 'validateModel'),
			array('baseControllerClass', 'sticky'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'model'=>'Model Class',
			'controller'=>'Controller ID',
			'baseControllerClass'=>'Base Controller Class',
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

		$this->files[]=new CCodeFile(
			$this->controllerFile,
			$this->render($controllerTemplateFile)
		);

		$files=scandir($templatePath);
		foreach($files as $file)
		{
			if(is_file($templatePath.'/'.$file) && CFileHelper::getExtension($file)==='php' && $file!=='controller.php')
			{
				$this->files[]=new CCodeFile(
					$this->viewPath.DIRECTORY_SEPARATOR.$file,
					$this->render($templatePath.'/'.$file)
				);
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
		return $module->getControllerPath().'/'.$id.'Controller.php';
	}

	public function getViewPath()
	{
		return $this->getModule()->getViewPath().'/'.$this->getControllerID();
	}

	public function getTableSchema()
	{
		return $this->_table;
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

	public function generateActiveLabel($modelClass,$column)
	{
		return "\$form->labelEx(\$model,'{$column->name}')";
	}

	public function generateActiveField($modelClass,$column,$form=true)
	{
		if($column->type==='boolean' || $column->dbType == 'tinyint(1)') {
if($form == true)
			return "echo \$form->checkBox(\$model,'{$column->name}')";
else {
if($column->dbType == 'tinyint(1)' && $column->defaultValue === null)
			return "echo \$form->textField(\$model,'{$column->name}')";
else
			return "echo \$form->dropDownList(\$model,'{$column->name}', array('0'=>Yii::t('phrase', 'No'), '1'=>Yii::t('phrase', 'Yes')))";
}
		} elseif(stripos($column->dbType,'text')!==false) {
if($form == true) {
			$return = "//echo \$form->textArea(\$model,'{$column->name}',array('rows'=>6, 'cols'=>50));
			\$this->widget('application.vendor.yiiext.imperavi-redactor-widget.ImperaviRedactorWidget', array(
				'model'=>\$model,
				'attribute'=>'{$column->name}',
				'options'=>array(
					'buttons'=>array(
						'html', 'formatting', '|', 
						'bold', 'italic', 'deleted', '|',
						'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
						'link', '|',
					),
				),
				'plugins' => array(
					'fontcolor' => array('js' => array('fontcolor.js')),
					'table' => array('js' => array('table.js')),
					'fullscreen' => array('js' => array('fullscreen.js')),
				),
			));";
} else
			$return = "echo \$form->textField(\$model,'{$column->name}')";
			return $return;
		} elseif(in_array($column->dbType, array('timestamp','datetime','date'))) {
			if($form == true)
				$return = "\$model->{$column->name} = !\$model->isNewRecord ? (!in_array(\$model->{$column->name}, array('0000-00-00','1970-01-01')) ? date('d-m-Y', strtotime(\$model->{$column->name})) : '') : '';\n\t\t\t";
			$return .= "//echo \$form->textField(\$model,'{$column->name}');
			\$this->widget('application.components.system.CJuiDatePicker',array(
				'model'=>\$model,
				'attribute'=>'{$column->name}',
				//'mode'=>'datetime',
				'options'=>array(
					'dateFormat' => 'dd-mm-yy',
				),
				'htmlOptions'=>array(
					'class' => 'span-4',
				 ),
			));";
			return $return;
		} else {
			if(preg_match('/^(password|pass|passwd|passcode)$/i',$column->name))
				$inputField='passwordField';
			else
				$inputField='textField';

				$columnName = $column->name;
if($form == false) {
			if($column->isForeignKey == '1') {
				$relationName = $this->setRelationName($column->name, true);
				if($relationName == 'cat')
					$relationName = 'category';
				$columnName = $relationName.'_search';
			} else if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
				$relationArray = explode('_',$column->name);
				$relationName = $relationArray[0];
				$columnName = $relationName.'_search';
			}
}

			if($column->type!=='string' || $column->size===null)
				return "echo \$form->{$inputField}(\$model,'{$columnName}')";
			else
			{
				if(($size=$maxLength=$column->size)>60)
					$size=60;
if($form == true)
				return "echo \$form->{$inputField}(\$model,'{$columnName}',array('size'=>$size,'maxlength'=>$maxLength))";
else
				return "echo \$form->{$inputField}(\$model,'{$columnName}')";
			}
		}
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
 
	/* 
	* set name relation with underscore
	*/
	public function setRelationName($names, $column=false) {
		$patterns = array();
		$patterns[0] = '(_ommu)';
		$patterns[1] = '(_core)';
		
		if($column == false) {
			$char=range("A","Z");
			foreach($char as $val) {
				if(strpos($names, $val) !== false) {
					$names = str_replace($val, '_'.strtolower($val), $names);
				}
			}
		} else
			$names = rtrim($names, 'id');

		$return = trim(preg_replace($patterns, '', $names), '_');
		$return = array_map('strtolower', explode('_', $return));
		//print_r($return);

		if(count($return) != 1)
			return end($return);
		else {
			if(is_array($return))
				return implode('', $return);
			else
				return $return;
		}
	}
}