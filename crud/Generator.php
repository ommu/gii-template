<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ommu\gii\crud;

use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\helpers\StringHelper;

/**
 * Generates CRUD
 *
 * @property array $columnNames Model column names. This property is read-only.
 * @property string $controllerID The controller ID (without the module ID prefix). This property is
 * read-only.
 * @property array $searchAttributes Searchable attributes. This property is read-only.
 * @property boolean|\yii\db\TableSchema $tableSchema This property is read-only.
 * @property string $viewPath The controller view path. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \ommu\gii\Generator
{
	public $modelClass;
	public $controllerClass;
	public $viewPath;
	public $baseControllerClass = '\app\components\Controller';
	public $indexWidgetType = 'grid';
	public $searchModelClass = '';
	public $useJuiDatePicker = false;
	public $attachRBACFilter = false;
	public $link='http://opensource.ommu.co';
	public $useModified = false;
	
	/**
	 * @var boolean whether to wrap the `GridView` or `ListView` widget with the `yii\widgets\Pjax` widget
	 * @since 2.0.5
	 */
	public $enablePjax = false;


	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'CRUD Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator generates a controller and views that implement CRUD (Create, Read, Update, Delete)
			operations for the specified data model.';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), [
			[['controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass',
				'link'], 'filter', 'filter' => 'trim'],
			[['modelClass', 'controllerClass', 'baseControllerClass', 'indexWidgetType',
				'link'], 'required'],
			[['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
			[['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
			[['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::className()]],
			[['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
			[['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
			[['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
			[['controllerClass', 'searchModelClass'], 'validateNewClass'],
			[['indexWidgetType'], 'in', 'range' => ['grid', 'list']],
			[['modelClass'], 'validateModelClass'],
			[['enableI18N', 'enablePjax',
				'useModified'], 'boolean'],
			[['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
			[['viewPath', 'useJuiDatePicker', 'attachRBACFilter'], 'safe'],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), [
			'modelClass' => 'Model Class',
			'controllerClass' => 'Controller Class',
			'viewPath' => 'View Path',
			'baseControllerClass' => 'Base Controller Class',
			'indexWidgetType' => 'Widget Used in Index Page',
			'searchModelClass' => 'Search Model Class',
			'enablePjax' => 'Enable Pjax',
			'useJuiDatePicker' => 'Use JQuery DatePicker',
			'attachRBACFilter' => 'Attach RBAC filter',
			'link'=>'Link Repository',
			'useModified'=>'Use Modified Info',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return array_merge(parent::hints(), [
			'modelClass' => 'This is the ActiveRecord class associated with the table that CRUD will be built upon.
				You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
			'controllerClass' => 'This is the name of the controller class to be generated. You should
				provide a fully qualified namespaced class (e.g. <code>app\controllers\PostController</code>),
				and class name should be in CamelCase with an uppercase first letter. Make sure the class
				is using the same namespace as specified by your application\'s controllerNamespace property.',
			'viewPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
				<code>/var/www/basic/controllers/views/post</code>, <code>@app/views/post</code>. If not set, it will default
				to <code>@app/views/ControllerID</code>',
			'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
				You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
			'indexWidgetType' => 'This is the widget type to be used in the index page to display list of the models.
				You may choose either <code>GridView</code> or <code>ListView</code>',
			'searchModelClass' => 'This is the name of the search model class to be generated. You should provide a fully
				qualified namespaced class name, e.g., <code>app\models\PostSearch</code>.',
			'enablePjax' => 'This indicates whether the generator should wrap the <code>GridView</code> or <code>ListView</code>
				widget on the index page with <code>yii\widgets\Pjax</code> widget. Set this to <code>true</code> if you want to get
				sorting, filtering and pagination without page refreshing.',
			'useJuiDatePicker' => 'Use JUI DatePicker or use html5 date picker. <code>default: false</code>',
			'attachRBACFilter' => 'Attach RBAC filter to controller. <code>default: false</code>',
			'useModified' => 'Use generate-source modified info. <code>default: false</code>',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function requiredTemplates()
	{
		return ['controller.php'];
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return array_merge(parent::stickyAttributes(), ['baseControllerClass', 'indexWidgetType', 'link']);
	}

	/**
	 * Checks if model class is valid
	 */
	public function validateModelClass()
	{
		/* @var $class ActiveRecord */
		$class = $this->modelClass;
		$pk = $class::primaryKey();
		if (empty($pk)) {
			$this->addError('modelClass', "The table associated with $class must have primary key(s).");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		$controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

		$files = [
			new CodeFile($controllerFile, $this->render('controller.php')),
		];

		if (!empty($this->searchModelClass)) {
			$searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
			$files[] = new CodeFile($searchModel, $this->render('search.php'));
		}

		$viewPath = $this->getViewPath();
		$templatePath = $this->getTemplatePath() . '/views';
		foreach (scandir($templatePath) as $file) {
			if (empty($this->searchModelClass) && $file === '_search.php') {
				continue;
			}
			if (empty($this->searchModelClass) && $file === '_option_form.php') {
				continue;
			}
			if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
				$files[] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
			}
		}

		return $files;
	}

	/**
	 * @return string the controller ID (without the module ID prefix)
	 */
	public function getControllerID()
	{
		$pos = strrpos($this->controllerClass, '\\');
		$class = substr(substr($this->controllerClass, $pos + 1), 0, -10);

		return Inflector::camel2id($class);
	}

	/**
	 * @return string the controller view path
	 */
	public function getViewPath()
	{
		if (empty($this->viewPath)) {
			return Yii::getAlias('@app/views/' . $this->getControllerID());
		} else {
			return Yii::getAlias($this->viewPath);
		}
	}

	public function getNameAttribute($tableName=null)
	{
		if($tableName != null) {
			$db = $this->getDbConnection();
			$tableSchema = $db->getTableSchema($tableName);

		} else {
			$tableSchema = $this->getTableSchema();
			//$columnNames = $this->getColumnNames();
		}
		
		$foreignKeys = $this->getForeignKeys($tableSchema->foreignKeys);
		$foreignCondition = 0;

		/* @var $class \yii\db\ActiveRecord */
		foreach ($tableSchema->columns as $column) {
			if(preg_match('/(name|title)/', $column->name))
				return $column->name;
		}
		foreach ($tableSchema->columns as $column) {
			if($column->name == 'tag_id')
				return $column->name;
		}
		foreach ($tableSchema->columns as $column) {
			if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
				if(!$foreignCondition) {
					return $column->name;
					$foreignCondition = 1;
				}
			}
		}
		$pk = $tableSchema->primaryKey;

		return Inflector::camel2id($pk[0]);
	}

	public function getNameRelationAttribute($tableName=null, $separator='->')
	{
		if($tableName != null) {
			$db = $this->getDbConnection();
			$tableSchema = $db->getTableSchema($tableName);

		} else {
			$tableSchema = $this->getTableSchema();
			//$columnNames = $this->getColumnNames();
		}

		$foreignKeys = $this->getForeignKeys($tableSchema->foreignKeys);
		$titleCondition = 0;
		$foreignCondition = 0;

		/* @var $class \yii\db\ActiveRecord */
		foreach ($tableSchema->columns as $column) {
			$relationColumn = [];
			if(preg_match('/(name|title)/', $column->name)) {
				$commentArray = explode(',', $column->comment);
				if(in_array('trigger[delete]', $commentArray)) {
					$relationColumn[] = preg_match('/(name|title)/', $column->name) ? 'title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? 'description' : $name.'Rltn') : $column->name.'Rltn');
					$relationColumn[] = 'message';
				} else
					$relationColumn[] = $column->name;
				$titleCondition = 1;
			}
			if(!empty($relationColumn))
				return implode($separator, $relationColumn);
		}
		if(!$titleCondition) {
			foreach ($tableSchema->columns as $column) {
				$relationColumn = [];
				if($column->name == 'tag_id') {
					$relationColumn[] = $this->setRelationName($column->name);
					$relationColumn[] = 'body';
				}
				if(!empty($relationColumn))
					return implode($separator, $relationColumn);
			}
		}
		if(!$titleCondition) {
			foreach ($tableSchema->columns as $column) {
				$relationColumn = [];
				if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
					$relationTableName = trim($foreignKeys[$column->name]);
					if(!$foreignCondition) {
						$relationColumn[] = $this->setRelationName($column->name);
						$relationColumn[] = $this->getNameRelationAttribute($relationTableName, $separator);
						$foreignCondition = 1;
					}
				}
				if(!empty($relationColumn))
					return implode($separator, $relationColumn);
			}
		}
		$pk = $tableSchema->primaryKey;

		return $pk[0];
	}

	public function getName2ndRelation($st, $nd='')
	{
		$relations = [];
		$relations[] = $st;
		if($nd != '') {
			$relations = \yii\helpers\ArrayHelper::merge($relations, explode('.', $nd));
			array_pop($relations);
		}

		return implode('.', $relations);
	}

	public function getName2ndAttribute($st, $nd='')
	{
		$relations = [];
		$relations[] = $st;
		if($nd != '') {
			$relations = \yii\helpers\ArrayHelper::merge($relations, explode('.', $nd));
		}

		return array_pop($relations);
	}

	/**
	 * Generates code for active field
	 * @param string $attribute
	 * @return string
	 */
	public function generateActiveField($attribute)
	{
		$tableSchema = $this->getTableSchema();
		//echo Yii::$app->formatter->dateFormat;
		//print_r($tableSchema->columns);
		if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
			if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->passwordInput()
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			} else {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			}
		}
		$column = $tableSchema->columns[$attribute];
		//print_r($column);
		$translateCondition = 0;
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)) {
			$attribute = $column->name.'_i';
			$translateCondition = 1;
		}

		if ($column->phpType === 'boolean' || $column->dbType == 'tinyint(1)') {
			return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12 checkbox\">{input}{error}</div>'])
\t->checkbox(['label'=>''])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";

		} elseif ($column->name === 'email') {
			return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->textInput(['type' => 'email'])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";

		} elseif (in_array($column->dbType, array('timestamp','datetime','date'))) {
			// Jui datepicker lebih fleksibel terhadap dukungan browser dan dapat diformat tanggalnya
			// dari pada html5.
			if($this->useJuiDatePicker) {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->widget(\yii\jui\DatePicker::classname(), ['dateFormat' => Yii::\$app->formatter->dateFormat, 'options' => ['type' => 'date', 'class' => 'form-control']])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			} else {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->textInput(['type' => 'date'])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			}
		} elseif ($column->type === 'text' || $translateCondition) {
			if($column->comment == 'file' || in_array('file', $commentArray)) {
				$modelClass = StringHelper::basename($this->modelClass);
				return "<div class=\"form-group field-$attribute\">
\t<?php echo \$form->field(\$model, '$attribute', ['template' => '{label}', 'options' => ['tag' => null]])
\t\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']); ?>
\t<div class=\"col-md-9 col-sm-9 col-xs-12\">
\t\t<?php echo !\$model->isNewRecord && \$model->old_{$attribute}_i != '' ? Html::img(join('/', [Url::Base(), $modelClass::getUploadPath(false), \$model->old_{$attribute}_i]), ['class'=>'mb-15', 'width'=>'100%']) : '';?>
\t\t<?php echo \$form->field(\$model, '$attribute', ['template' => '{input}{error}'])
\t\t\t->fileInput()
\t\t\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']); ?>
\t</div>\n</div>";
/*
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->fileInput()
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
*/
			} else if($column->comment == 'redactor' || in_array('redactor', $commentArray)) {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->textarea(['rows'=>2,'rows'=>6])
\t->widget(Redactor::className(), ['clientOptions' => \$redactorOptions])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			} else if($column->comment == 'text' || in_array('text', $commentArray)) {
				$maxlength = $translateCondition ? ',\'maxlength\' => true' : '';
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->textarea(['rows'=>2,'rows'=>6$maxlength])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			} else {
				if($translateCondition) {
					return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->textInput(['maxlength' => true])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
				} else {
					return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->textarea(['rows'=>2,'rows'=>6])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
				}
			}

		} else {
			if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
				$input = 'passwordInput';
			} else {
				$input = 'textInput';
			}
			if (is_array($column->enumValues) && count($column->enumValues) > 0) {
				$dropDownOptions = [];
				foreach ($column->enumValues as $enumValue) {
					$dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
				}
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->dropDownList(". preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)).", ['prompt' => ''])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			} elseif ($column->phpType !== 'string' || $column->size === null) {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->$input(['type' => 'number', 'min' => '1'])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			} else {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->$input(['maxlength' => true])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			}
		}
	}

	/**
	 * Generates code for active search field
	 * @param string $attribute
	 * @return string
	 */
	public function generateActiveSearchField($attribute)
	{
		$tableSchema = $this->getTableSchema();
		$foreignKeys = $this->getForeignKeys($tableSchema->foreignKeys);
		if ($tableSchema === false) {
			return "\$form->field(\$model, '$attribute')";
		}

		$column = $tableSchema->columns[$attribute];
		$i18n = 0;
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray))
			$i18n = 1;

		if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id'])):
			$attributeName = $this->setRelationName($column->name).'_search';
			return "\$form->field(\$model, '$attributeName')";

		elseif(in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id'])):
			$relationName = $this->setRelationName($column->name);
			$attributeName = $relationName.'_search';
			if($column->name == 'tag_id')
				$attributeName = $relationName.'_i';
			return "\$form->field(\$model, '$attributeName')";

		elseif(in_array($column->dbType, ['timestamp','datetime','date'])):
			if($this->useJuiDatePicker):
				return "\$form->field(\$model, '$attribute')\n\t\t\t->widget(\yii\jui\DatePicker::classname(), [\n\t\t\t\t'dateFormat' => Yii::\$app->formatter->dateFormat,\n\t\t\t\t'options' => ['class' => 'form-control']\n\t\t\t])";
			else:
				return "\$form->field(\$model, '$attribute')\n\t\t\t->input('date')";
			endif;

		else:
			if($i18n):
				$attributeName = $column->name.'_i';
				return "\$form->field(\$model, '$attributeName')";
			else:
				if ($column->phpType === 'boolean' || $column->dbType == 'tinyint(1)')
					return "\$form->field(\$model, '$attribute')\n\t\t\t->checkbox()";
				else
					return "\$form->field(\$model, '$attribute')";
			endif;
		endif;
	}

	/**
	 * Generates column format
	 * @param \yii\db\ColumnSchema $column
	 * @return string
	 */
	public function generateColumnFormat($column)
	{
		if ($column->phpType === 'boolean') {
			return 'boolean';
		} elseif ($column->type === 'text') {
			return 'ntext';
		} elseif (stripos($column->name, 'time') !== false && $column->phpType === 'integer') {
			return 'datetime';
		} elseif (stripos($column->name, 'email') !== false) {
			return 'email';
		} elseif (stripos($column->name, 'url') !== false) {
			return 'url';
		} else {
			return 'text';
		}
	}

	/**
	 * Generates validation rules for the search model.
	 * @return array the generated validation rules
	 */
	public function generateSearchRules()
	{
		if (($table = $this->getTableSchema()) === false) {
			return new SearchRules($this->getColumnNames(), 'safe');
			// return ["[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"];
		}

		$types = [];
		foreach ($table->columns as $column) {
			if($column->name[0] == '_')
				continue;
				
			switch ($column->type) {
				case Schema::TYPE_SMALLINT:
				case Schema::TYPE_INTEGER:
				case Schema::TYPE_BIGINT:
					$types['integer'][] = $column->name;
					break;
				case Schema::TYPE_BOOLEAN:
					$types['boolean'][] = $column->name;
					break;
				case Schema::TYPE_FLOAT:
				case Schema::TYPE_DOUBLE:
				case Schema::TYPE_DECIMAL:
				case Schema::TYPE_MONEY:
					$types['number'][] = $column->name;
					break;
				case Schema::TYPE_DATE:
				case Schema::TYPE_TIME:
				case Schema::TYPE_DATETIME:
				case Schema::TYPE_TIMESTAMP:
				default:
					if($column->dbType == 'tinyint(1)')
						$types['integer'][] = $column->name;
					else
						$types['safe'][] = $column->name;
					break;
			}
		}

		// TODO: remove $rules if it's safe
		$rules = [];
		$objRules = [];
		foreach ($types as $type => $columns) {
			$rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
			$objRules[] = new SearchRules($columns, $type);
		}

		return $objRules;
	}

	/**
	 * @return array searchable attributes
	 */
	public function getSearchAttributes()
	{
		return $this->getColumnNames();
	}

	/**
	 * Generates the attribute labels for the search model.
	 * @return array the generated attribute labels (name => label)
	 */
	public function generateSearchLabels()
	{
		/* @var $model \yii\base\Model */
		$model = new $this->modelClass();
		$attributeLabels = $model->attributeLabels();
		$labels = [];
		foreach ($this->getColumnNames() as $name) {
			if (isset($attributeLabels[$name])) {
				$labels[$name] = $attributeLabels[$name];
			} else {
				if (!strcasecmp($name, 'id')) {
					$labels[$name] = 'ID';
				} else {
					$label = Inflector::camel2words($name);
					if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
						$label = substr($label, 0, -3) . ' ID';
					}
					$labels[$name] = $label;
				}
			}
		}

		return $labels;
	}

	/**
	 * Generates search conditions
	 * @return array
	 */
	public function generateSearchConditions()
	{
		$patternClass = $patternLabel = array();
		$patternClass[0] = '(Ommu)';
		$patternClass[1] = '(Swt)';

		$tableSchema = $this->getTableSchema();
		$foreignKeys = $this->getForeignKeys($tableSchema->foreignKeys);
		$columns = [];
		if (($table = $tableSchema) === false) {
			$class = $this->modelClass;
			/* @var $model \yii\base\Model */
			$model = new $class();
			foreach ($model->attributes() as $attribute) {
				$columns[$attribute] = 'unknown';
			}
		} else {
			foreach ($table->columns as $column) {
				if($column->name[0] == '_')
					continue;
				$columns[$column->name] = $column->type;
			}
		}
		
		if(!empty($tableSchema->primaryKey))
			$primaryKey = $tableSchema->primaryKey[0];
		else
			$primaryKey = key($tableSchema->columns);

		$likeConditions = [];
		$hashConditions = [];
		$publishConditions = [];

		$arrayHasColumn = [];
		$arrayLikeColumn = [];
		foreach ($columns as $column => $type) {
			switch ($type) {
				case Schema::TYPE_SMALLINT:
				case Schema::TYPE_INTEGER:
				case Schema::TYPE_BIGINT:
				case Schema::TYPE_BOOLEAN:
				case Schema::TYPE_FLOAT:
				case Schema::TYPE_DOUBLE:
				case Schema::TYPE_DECIMAL:
				case Schema::TYPE_MONEY:
				case Schema::TYPE_DATE:
				case Schema::TYPE_TIME:
				case Schema::TYPE_DATETIME:
				case Schema::TYPE_TIMESTAMP:
					//echo $column."\n";
					if(in_array($column, array('creation_id','modified_id','user_id','updated_id','tag_id'))) {
						if(!in_array($column, $arrayHasColumn)) {
							$arrayHasColumn[] = $column;
							$relation = $this->setRelationName($column);
							$hashConditions[] = "'t.{$column}' => isset(\$params['$relation']) ? \$params['$relation'] : \$this->{$column},";
						}

					} else if(!empty($foreignKeys) && array_key_exists($column, $foreignKeys)) {
						if(!in_array($column, $arrayHasColumn)) {
							$arrayHasColumn[] = $column;
							$relation = $this->setRelationName($column);
							$hashConditions[] = "'t.{$column}' => isset(\$params['$relation']) ? \$params['$relation'] : \$this->{$column},";
						}

					} if(in_array($type, array('timestamp','datetime','date'))) {
						if(!in_array($column, $arrayHasColumn)) {
							$arrayHasColumn[] = $column;
							$hashConditions[] = "'cast(t.{$column} as date)' => \$this->{$column},";
						}
						
					} else {
						if(!in_array($column, $arrayHasColumn)) {
							$arrayHasColumn[] = $column;
							$hashConditions[] = "'t.{$column}' => \$this->{$column},";
						}
					}
					break;
				default:
					if($type == 'tinyint') {
						if($column != 'publish') {
							if(!in_array($column, $arrayHasColumn)) {
								$arrayHasColumn[] = $column;
								$hashConditions[] = "'t.{$column}' => \$this->{$column},";
							}
						}
					} else
						if(!in_array($column, $arrayLikeColumn)) {
							$arrayLikeColumn[] = $column;
							$likeConditions[] = "->andFilterWhere(['like', 't.{$column}', \$this->{$column}])";
						}
					break;
			}
		}
		foreach ($tableSchema->columns as $column): 
		if($column->dbType == 'tinyint(1)' && $column->name == 'publish') {
			$publishConditions[] = "if(isset(\$params['trash']))\n\t\t\t\$query->andFilterWhere(['NOT IN', 't.$column->name', [0,1]]);\n\t\telse {\n\t\t\tif(!isset(\$params['$column->name']) || (isset(\$params['$column->name']) && \$params['$column->name'] == ''))\n\t\t\t\t\$query->andFilterWhere(['IN', 't.$column->name', [0,1]]);\n\t\t\telse\n\t\t\t\t\$query->andFilterWhere(['t.$column->name' => \$this->$column->name]);\n\t\t}";
		}
		endforeach;
		$arrayPublicVariable = [];

		foreach ($tableSchema->columns as $column):
			$commentArray = explode(',', $column->comment);
			if(in_array('trigger[delete]', $commentArray)):
				$relationName = preg_match('/(name|title)/', $column->name) ? 'title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? 'description' : $name.'Rltn') : $column->name.'Rltn');
				$publicVariable = $column->name.'_i';
				if(!in_array($publicVariable, $arrayPublicVariable)) {
					$arrayPublicVariable[] = $publicVariable;
					$likeConditions[] = "->andFilterWhere(['like', '{$relationName}.message', \$this->{$publicVariable}])";
				}
			endif;
		endforeach;
		foreach ($tableSchema->columns as $column): 
			if(in_array($column->name, ['tag_id'])):
				$relationName = $this->setRelationName($column->name);
				$publicVariable = $relationName.'_i';
				if(!in_array($publicVariable, $arrayPublicVariable)) {
					$arrayPublicVariable[] = $publicVariable;
					$likeConditions[] = "->andFilterWhere(['like', '{$relationName}.body', \$this->{$publicVariable}])";
				}
			endif;
		endforeach;
		foreach ($tableSchema->columns as $column): 
			if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id','tag_id'))):
				$relationTableName = trim($foreignKeys[$column->name]);
				$relationName = $this->setRelationName($column->name);
				$relationAttributeName = $this->getName2ndAttribute($relationName, $this->getNameRelationAttribute($relationTableName, '.'));
				if(trim($foreignKeys[$column->name]) == 'ommu_users')
					$relationAttributeName = 'displayname';
				$publicVariable = $relationName.'_search';
				if(!in_array($publicVariable, $arrayPublicVariable)) {
					$arrayPublicVariable[] = $publicVariable;
					$likeConditions[] = "->andFilterWhere(['like', '{$relationName}.{$relationAttributeName}', \$this->{$publicVariable}])";
				}
			endif;
		endforeach;
		foreach ($tableSchema->columns as $column): 
			if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
				$relationName = $this->setRelationName($column->name);
				$publicVariable = $relationName.'_search';
				if(!in_array($publicVariable, $arrayPublicVariable)) {
					$arrayPublicVariable[] = $publicVariable;
					$likeConditions[] = "->andFilterWhere(['like', '{$relationName}.displayname', \$this->{$publicVariable}])";
				}
			endif;
		endforeach;

		$conditions = [];
		if (!empty($hashConditions)) {
			$conditions[] = "\$query->andFilterWhere([\n\t\t\t"
				. implode("\n\t\t\t", $hashConditions)
				. "\n\t\t]);\n";
		}
		if (!empty($publishConditions)) {
			$conditions[] = implode("\n\t\t", $publishConditions) . "\n";
		}
		if (!empty($likeConditions)) {
			$conditions[] = "\$query" . implode("\n\t\t\t", $likeConditions) . ";\n";
		}

		return $conditions;
	}

	/**
	 * Generates URL parameters
	 * @return string
	 */
	public function generateUrlParams()
	{
		/* @var $class ActiveRecord */
		$class = $this->modelClass;
		$pks = $class::primaryKey();
		if (count($pks) === 1) {
			if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
				return "'id' => (string)\$model->{$pks[0]}";
			} else {
				return "'id' => \$model->{$pks[0]}";
			}
		} else {
			$params = [];
			foreach ($pks as $pk) {
				if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
					$params[] = "'$pk' => (string)\$model->$pk";
				} else {
					$params[] = "'$pk' => \$model->$pk";
				}
			}

			return implode(', ', $params);
		}
	}

	/**
	 * Generates action parameters
	 * @return string
	 */
	public function generateActionParams()
	{
		/* @var $class ActiveRecord */
		$class = $this->modelClass;
		$pks = $class::primaryKey();
		if (count($pks) === 1) {
			return '$id';
		} else {
			return '$' . implode(', $', $pks);
		}
	}

	/**
	 * Generates parameter tags for phpdoc
	 * @return array parameter tags for phpdoc
	 */
	public function generateActionParamComments()
	{
		/* @var $class ActiveRecord */
		$class = $this->modelClass;
		$pks = $class::primaryKey();
		if (($table = $this->getTableSchema()) === false) {
			$params = [];
			foreach ($pks as $pk) {
				$params[] = '@param ' . (substr(strtolower($pk), -2) == 'id' ? 'integer' : 'string') . ' $' . $pk;
			}

			return $params;
		}
		if (count($pks) === 1) {
			return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
		} else {
			$params = [];
			foreach ($pks as $pk) {
				$params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
			}

			return $params;
		}
	}

	/**
	 * Returns table schema for current model class or false if it is not an active record
	 * @return boolean|\yii\db\TableSchema
	 */
	public function getTableSchema()
	{
		/* @var $class ActiveRecord */
		$class = $this->modelClass;
		if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
			return $class::getTableSchema();
		} else {
			return false;
		}
	}

	/**
	 * @return array model column names
	 */
	public function getColumnNames()
	{
		/* @var $class ActiveRecord */
		$class = $this->modelClass;
		if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
			return $class::getTableSchema()->getColumnNames();
		} else {
			/* @var $model \yii\base\Model */
			$model = new $class();

			return $model->attributes();
		}
	}
	
	public $tableName;
	public $useSchemaName = true;
	protected $classNames;

	/**
	 * @return Connection the DB connection as specified by [[db]].
	 */
	protected function getDbConnection()
	{
		$class = $this->modelClass;

		return $class::getDb();
	}

	/**
	 * Generates a class name from the specified table name.
	 * @param string $tableName the table name (which may contain schema prefix)
	 * @param boolean $useSchemaName should schema name be included in the class name, if present
	 * @return string the generated class name
	 */
	public function generateClassName($tableName, $useSchemaName = null)
	{
		if (isset($this->classNames[$tableName])) {
			return $this->classNames[$tableName];
		}

		$schemaName = '';
		$fullTableName = $tableName;
		if (($pos = strrpos($tableName, '.')) !== false) {
			if (($useSchemaName === null && $this->useSchemaName) || $useSchemaName) {
				$schemaName = substr($tableName, 0, $pos) . '_';
			}
			$tableName = substr($tableName, $pos + 1);
		}

		$db = $this->getDbConnection();
		$patterns = [];
		$patterns[] = "/^{$db->tablePrefix}(.*?)$/";
		$patterns[] = "/^(.*?){$db->tablePrefix}$/";
		if (strpos($this->tableName, '*') !== false) {
			$pattern = $this->tableName;
			if (($pos = strrpos($pattern, '.')) !== false) {
				$pattern = substr($pattern, $pos + 1);
			}
			$patterns[] = '/^' . str_replace('*', '(\w+)', $pattern) . '$/';
		}
		$className = $tableName;
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $tableName, $matches)) {
				$className = $matches[1];
				break;
			}
		}

		return $this->classNames[$fullTableName] = Inflector::id2camel($schemaName.$className, '_');
	}
}
