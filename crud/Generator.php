<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\libraries\gii\crud;

use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\web\Controller;

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
class Generator extends \app\libraries\gii\Generator
{
	public $modelClass;
	public $controllerClass;
	public $viewPath;
	public $baseControllerClass = '\app\components\Controller';
	public $indexWidgetType = 'grid';
	public $searchModelClass = '';
	public $useJuiDatePicker = false;
	public $attachRBACFilter = false;
	
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
			[['controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass'], 'filter', 'filter' => 'trim'],
			[['modelClass', 'controllerClass', 'baseControllerClass', 'indexWidgetType'], 'required'],
			[['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
			[['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
			[['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::className()]],
			[['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
			[['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
			[['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
			[['controllerClass', 'searchModelClass'], 'validateNewClass'],
			[['indexWidgetType'], 'in', 'range' => ['grid', 'list']],
			[['modelClass'], 'validateModelClass'],
			[['enableI18N', 'enablePjax'], 'boolean'],
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
			'attachRBACFilter' => 'Attach RBAC filter'
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
		return array_merge(parent::stickyAttributes(), ['baseControllerClass', 'indexWidgetType']);
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

	public function getNameAttribute($tableNameRelation=null)
	{
		if($tableNameRelation != null) {
			$primaryKey = [];
			$tableSchema = [];
			$db = $this->getDbConnection();
			$tableSchema = $db->getTableSchema($tableNameRelation);
			
			foreach ($tableSchema->columns as $key => $column) {
				if($column->isPrimaryKey || $column->autoIncrement)
					$primaryKey[] = $key;
				if(preg_match('/(name|title)/', $key))
					return $key;
			}

			$pk = $primaryKey;

		} else {
			foreach ($this->getColumnNames() as $name) {
				if(preg_match('/(name|title)/', $name))
					return $name;
			}
			
			/* @var $class \yii\db\ActiveRecord */
			$class = $this->modelClass;
			$pk = $class::primaryKey();
		}

		return $pk[0];
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
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-6 col-sm-6 col-xs-12\">{input}{error}</div>'])
\t->passwordInput()
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			} else {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-6 col-sm-6 col-xs-12\">{input}{error}</div>'])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			}
		}
		$column = $tableSchema->columns[$attribute];
		//print_r($column);

		if ($column->phpType === 'boolean' || $column->dbType == 'tinyint(1)') {
			return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-6 col-sm-6 col-xs-12 checkbox\">{input}{error}</div>'])
\t->checkbox(['label'=>''])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";

		} elseif ($column->name === 'email') {
			return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-6 col-sm-6 col-xs-12\">{input}{error}</div>'])
\t->textInput(['type' => 'email'])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";

		}elseif (in_array($column->dbType, array('timestamp','datetime','date'))) {
			$template  = "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-6 col-sm-6 col-xs-12\">";
			$template .= "{input}{error}</div>'])\n\t->textInput(['type' => 'date'])";
			$template .= "->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";

			// Jui datepicker lebih fleksibel terhadap dukungan browser dan dapat diformat tanggalnya
			// dari pada html5.
			if($this->useJuiDatePicker) {
				$template  = "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-6 col-sm-6 col-xs-12\">";
				$template .= "{input}{error}</div>'])\n\t->widget(DatePicker::classname(), ['dateFormat' => Yii::\$app->formatter->dateFormat, ";
				$template .= "'options' => ['type' => 'date', 'class' => 'form-control']])";
				$template .= "\n\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			}
			return $template;

		} elseif ($column->type === 'text') {
			return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-9 col-sm-9 col-xs-12\">{input}{error}</div>'])
\t->textarea(['rows'=>2,'rows'=>6])
\t->widget(Redactor::className(), ['clientOptions' => \$redactorOptions])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";

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
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-6 col-sm-6 col-xs-12\">{input}{error}</div>'])
\t->dropDownList(". preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)).", ['prompt' => ''])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			} elseif ($column->phpType !== 'string' || $column->size === null) {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-6 col-sm-6 col-xs-12\">{input}{error}</div>'])
\t->$input(['type' => 'number', 'min' => '1'])
\t->label(\$model->getAttributeLabel('$attribute'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12'])";
			} else {
				return "\$form->field(\$model, '$attribute', ['template' => '{label}<div class=\"col-md-6 col-sm-6 col-xs-12\">{input}{error}</div>'])
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
		if ($tableSchema === false) {
			return "\$form->field(\$model, '$attribute')";
		}
		$column = $tableSchema->columns[$attribute];
		if ($column->phpType === 'boolean') {
			return "\$form->field(\$model, '$attribute')->checkbox()";
		} else {
			return "\$form->field(\$model, '$attribute')";
		}
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
		//echo '<pre>';
		//print_r($this->getTableSchema());
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
				$columns[$column->name] = $column->type;
			}
		}
		
		$primaryKey = $tableSchema->primaryKey[0];

		$likeConditions = [];
		$hashConditions = [];
		$publishConditions = [];
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
					if(in_array($column, array('creation_id','modified_id','user_id','updated_id'))) {
						$relationArray = explode('_', $column);
						$relation = lcfirst($relationArray[0]);
						$hashConditions[] = "'t.{$column}' => isset(\$params['$relation']) ? \$params['$relation'] : \$this->{$column},";

					} else {
						if(!empty($foreignKeys) && in_array($column, $foreignKeys)) {
							$relationTable = array_search($column, $foreignKeys);
							$relationModel = preg_replace($patternClass, '', $this->generateClassName($relationTable));
							$relation = lcfirst(Inflector::singularize($this->setRelationName($relationModel)));
							$hashConditions[] = "'t.{$column}' => isset(\$params['$relation']) ? \$params['$relation'] : \$this->{$column},";

						} else {
							if(in_array($type, array('timestamp','datetime','date')))
								$hashConditions[] = "'cast(t.{$column} as date)' => \$this->{$column},";
							else {
								if($column == 'publish')
									$hashConditions[] = "'t.{$column}' => isset(\$params['publish']) ? 1 : \$this->{$column},";
								else {
									if($column == $primaryKey)
										$hashConditions[] = "'t.{$column}' => isset(\$params['id']) ? \$params['id'] : \$this->{$column},";
									else
										$hashConditions[] = "'t.{$column}' => \$this->{$column},";
								}
							}
						}
					}
					break;
				default:
					$likeConditions[] = "->andFilterWhere(['like', 't.{$column}', \$this->{$column}])";
					break;
			}
		}
		foreach ($tableSchema->columns as $column): 
		if($column->dbType == 'tinyint(1)' && $column->name == 'publish') {
			$publishConditions[] = "if(!isset(\$params['trash']))\n\t\t\t\$query->andFilterWhere(['IN', 't.publish', [0,1]]);\n\t\telse\n\t\t\t\$query->andFilterWhere(['NOT IN', 't.publish', [0,1]]);";
		}
		endforeach;
		foreach ($tableSchema->columns as $column): 
		if(!empty($foreignKeys) && in_array($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))) {
			$relationTableName = array_search($column->name, $foreignKeys);
			$relationModelName = preg_replace($patternClass, '', $this->generateClassName($relationTableName));
			$relationAttributeName = $this->getNameAttribute($relationTableName);
			$relationName = lcfirst(Inflector::singularize($this->setRelationName($relationModelName)));
			$relationSearchName = $relationName.'_search';
			$likeConditions[] = "->andFilterWhere(['like', '{$relationName}.{$relationAttributeName}', \$this->{$relationSearchName}])";
		}
		endforeach;
		foreach ($tableSchema->columns as $column): 
		if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))) {
			$relationNameArray = explode('_', $column->name);
			$relationName = lcfirst($relationNameArray[0]);
			$relationSearchName = $relationName.'_search';
			$likeConditions[] = "->andFilterWhere(['like', '{$relationName}.displayname', \$this->{$relationSearchName}])";
		}
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
