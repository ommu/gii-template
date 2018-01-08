<?php

class CrudCode extends CCodeModel
{
	public $model;
	public $controller;
	public $baseControllerClass='Controller';

	private $_modelClass;
	private $_table;
	private static $_output;

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

	public function generateActiveLabel($modelClass,$column, $type=false)
	{
		$columnName = $column->name;
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray))
			$columnName = $columnName.'_i';
		if($type == false)
			return "\$form->labelEx(\$model, '{$columnName}')";
		else
			return "\$form->labelEx(\$model, '{$columnName}', array('class'=>'col-form-label col-lg-4 col-md-3 col-sm-12'))";
	}

	public function generateActiveField($modelClass,$column,$form=true)
	{
		//print_r($column);
		if($column->type==='boolean' || $column->dbType == 'tinyint(1)') {
if($form == true):
if($column->dbType == 'tinyint(1)' && $column->defaultValue === null)
	return "echo \$form->textField(\$model, '{$column->name}', array('class'=>'form-control'))";
else
	return "echo \$form->checkBox(\$model, '{$column->name}', array('class'=>'form-control'))";
else:
if($column->dbType == 'tinyint(1)' && $column->defaultValue === null)
			return "echo \$form->textField(\$model, '{$column->name}', array('class'=>'form-control'))";
else
			return "echo \$form->dropDownList(\$model, '{$column->name}', array('0'=>Yii::t('phrase', 'No'), '1'=>Yii::t('phrase', 'Yes')), array('class'=>'form-control'))";
endif;
		} elseif(stripos($column->dbType,'text')!==false) {
if($form == true) {
			$return = "//echo \$form->textArea(\$model, '{$column->name}', array('rows'=>6, 'cols'=>50, 'class'=>'form-control'));
			\$this->widget('yiiext.imperavi-redactor-widget.ImperaviRedactorWidget', array(
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
				'htmlOptions'=>array(
					'class' => 'form-control',
				 ),
			));";
} else
			$return = "echo \$form->textField(\$model, '{$column->name}')";
			return $return;
		} elseif(in_array($column->dbType, array('timestamp','datetime','date'))) {
			if($form == true)
				$return = "\$model->{$column->name} = !\$model->isNewRecord ? (!in_array(\$model->{$column->name}, array('0000-00-00','1970-01-01')) ? date('d-m-Y', strtotime(\$model->{$column->name})) : '') : '';\n\t\t\t";
			$return .= "//echo \$form->textField(\$model, '{$column->name}', array('class'=>'form-control'));
			\$this->widget('application.libraries.core.components.system.CJuiDatePicker',array(
				'model'=>\$model,
				'attribute'=>'{$column->name}',
				//'mode'=>'datetime',
				'options'=>array(
					'dateFormat' => 'dd-mm-yy',
				),
				'htmlOptions'=>array(
					'class' => 'form-control',
				 ),
			));";
			return $return;
		} else {
			if(preg_match('/^(password|pass|passwd|passcode)$/i',$column->name))
				$inputField='passwordField';
			else
				$inputField='textField';

				$i18n = 0;
				$columnName = $column->name;
				$commentArray = explode(',', $column->comment);
				if(in_array('trigger[delete]', $commentArray)) {
					$publicRelation = preg_match('/(name|title)/', $columnName) ? 'title' : 'description';
					$columnName = $columnName.'_i';
					$i18n = 1;
				}
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
} else
$enumCondition = 0;
if(preg_match('/(enum)/', $column->dbType)) {
	$enumCondition = 1;
	$patterns = array();
	$patterns[0] = '(enum)';
	$patterns[1] = '(\')';
	$enumvalue = preg_replace($patterns, '', $column->dbType);
	$enumvalue = trim($enumvalue, ')|(');
	$enumArrays = explode(',', $enumvalue);
	$dropDownOptions = array();
	foreach ($enumArrays as $enumArray) {
		$dropDownOptions[$enumArray] = $enumArray;
	}
}
			if ($enumCondition && is_array($enumArrays) && count($enumArrays) > 0) {
				$dropDownOption = self::export($dropDownOptions);
				$return = "$$columnName = $dropDownOption;\n\t\t\t";
				$return .= "echo \$form->dropDownList(\$model, '{$columnName}', $$columnName, array('prompt'=>'', 'class'=>'form-control'))";
				return $return;
			} else if($column->size===null)
				return "echo \$form->{$inputField}(\$model, '{$columnName}', array('class'=>'form-control'))";
			else {
				$maxLength=$column->size;
if($form == true) {
if($i18n) {
	$inputField = $publicRelation == 'title' ? 'textField' : 'textArea';
	if($publicRelation == 'title')
				return "echo \$form->{$inputField}(\$model, '{$columnName}', array('maxlength'=>32, 'class'=>'form-control'))";
	else
				return "echo \$form->{$inputField}(\$model, '{$columnName}', array('rows'=>6, 'cols'=>50, 'maxlength'=>128, 'class'=>'form-control'))";
} else
				return "echo \$form->{$inputField}(\$model, '{$columnName}', array('maxlength'=>$maxLength, 'class'=>'form-control'))";
} else
				return "echo \$form->{$inputField}(\$model, '{$columnName}', array('class'=>'form-control'))";
			}
		}
	}

	public function guessNameColumn($columns)
	{
		//echo '<pre>';
		//print_r($columns);
		$primaryKey = array();
		foreach ($columns as $key => $column) {
			if($column->isPrimaryKey || $column->autoIncrement)
				$primaryKey[] = $key;
			if(preg_match('/(name|title)/', $key))
				return $key;
		}
		$pk = $primaryKey;
	
		if(!empty($primaryKey))
			return $pk[0];
		else
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
	public static function export($var)
	{
		self::$_output = '';
		self::exportInternal($var, 0);
		return self::$_output;
	}

	/**
	 * @param mixed $var variable to be exported
	 * @param int $level depth level
	 */
	private static function exportInternal($var, $level)
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
						self::$_output .= "\n" . $spaces . '	';
						if ($outputKeys) {
							self::exportInternal($key, 0);
							self::$_output .= ' => Yii::t(\'phrase\', ';
						}
						self::exportInternal($var[$key], $level + 1);
						self::$_output .= '),';
					}
					self::$_output .= "\n" . $spaces . ')';
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
							self::exportInternal($var->toArray(), $level);
							return;
						} elseif ($var instanceof \IteratorAggregate) {
							$varAsArray = [];
							foreach ($var as $key => $value) {
								$varAsArray[$key] = $value;
							}
							self::exportInternal($varAsArray, $level);
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
}