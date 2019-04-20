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
 * @property string $nameAttribute This property is read-only.
 * @property array $searchAttributes Searchable attributes. This property is read-only.
 * @property bool|\yii\db\TableSchema $tableSchema This property is read-only.
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
    public $attachRBACFilter = true;
	public $uploadPathSubfolder = false;
    public $link='http://opensource.ommu.co';
    public $useModified = false;
    
    /**
     * @var bool whether to wrap the `GridView` or `ListView` widget with the `yii\widgets\Pjax` widget
     * @since 2.0.5
     */
    public $enablePjax = false;


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'CRUD Generator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'This generator generates a controller and views that implement CRUD (Create, Read, Update, Delete)
            operations for the specified data model.';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass', 'link'], 'filter', 'filter' => 'trim'],
            [['modelClass', 'controllerClass', 'baseControllerClass', 'indexWidgetType', 'link'], 'required'],
            [['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
            [['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::className()]],
            [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
            [['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
            [['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
            [['controllerClass', 'searchModelClass'], 'validateNewClass'],
            [['indexWidgetType'], 'in', 'range' => ['grid', 'list']],
            [['modelClass'], 'validateModelClass'],
            [['enableI18N', 'enablePjax', 'attachRBACFilter', 'useModified'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
            [['viewPath', 'attachRBACFilter', 'uploadPathSubfolder'], 'safe'],
        ]);
    }

    /**
     * {@inheritdoc}
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
            'attachRBACFilter' => 'Attach RBAC filter',
			'uploadPathSubfolder'=>'Use Subfolder (PrimaryKey) in Upload Path',
            'link'=>'Link Repository',
            'useModified'=>'Use Modified Info',
        ]);
    }

    /**
     * {@inheritdoc}
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
            'attachRBACFilter' => 'Attach RBAC filter to controller. <code>default: true</code>',
			'uploadPathSubfolder' => '...',
            'link' => 'This is link (URL Address) your repository.',
            'useModified' => 'Use source-code modified info in generator. <code>default: false</code>',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        return ['controller.php'];
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function generate()
    {
		$tableSchema = $this->getTableSchema();
		$relation = new \ommu\gii\model\Generator();
		$relation->tableName = $tableSchema->name;

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
                $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file", array('relations'=>$relation->getRelations())));
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
        }

        return Yii::getAlias($this->viewPath);
    }

    /**
     * @return string
     */
	public function getNameAttributes($table, $separator='->')
	{
		$db = $this->getDbConnection();
		$foreignKeys = $this->getForeignKeys($table->foreignKeys);
		$titleCondition = 0;
		$foreignCondition = 0;

		foreach ($table->columns as $key => $column) {
			$relationColumn = [];
			$commentArray = explode(',', $column->comment);
			if(preg_match('/(name|title|body)/', $column->name)) {
				if(in_array('trigger[delete]', $commentArray)) {
					$relationColumn[$column->name] = $this->i18nRelation($column->name);
					$relationColumn[] = 'message';
				} else {
					if($column->name == 'username')
						$relationColumn[$column->name] = 'displayname';
					else
						$relationColumn[$column->name] = $column->name;
				}
				$titleCondition = 1;
			}
			if(!empty($relationColumn))
				return $relationColumn;
		}
		if(!$titleCondition) {
			foreach ($table->columns as $key => $column) {
				$relationColumn = [];
				if($column->name == 'tag_id') {
					$relationColumn[$column->name] = $this->setRelation($column->name);
					$relationColumn[] = 'body';
				}
				if(!empty($relationColumn))
					return $relationColumn;
			}
		}
		if(!$titleCondition) {
			foreach ($table->columns as $key => $column) {
				$relationColumn = [];
				if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
					$relationTableName = trim($foreignKeys[$column->name]);
					if(!$foreignCondition) {
						$relationColumn[$column->name] = $this->setRelation($column->name);
						$relationColumn = \yii\helpers\ArrayHelper::merge($relationColumn, $this->getNameAttributes($db->getTableSchema($relationTableName), $separator));
						$foreignCondition = 1;
					}
				}
				if(!empty($relationColumn))
					return $relationColumn;
			}
		}

		$primaryKey = $this->getPrimaryKey($table);
		$relationColumn[$primaryKey] = $primaryKey;
		return $relationColumn;
	}

	public function getNameAttribute($tableName=null, $separator='->')
	{
		$tableSchema = [];
		if($tableName != null) {
			$db = $this->getDbConnection();
			$tableSchema = $db->getTableSchema($tableName);
		} else
			$tableSchema = $this->getTableSchema();

		$relationColumn = $this->getNameAttributes($tableSchema, $separator);

		if(!empty($relationColumn))
			return implode($separator, $relationColumn);

		return $this->getPrimaryKey($tableSchema);
    }

    /**
     * Generates code for active field
     * @param string $attribute
     * @return string
     */
    public function generateActiveField($attribute)
    {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
				return "echo \$form->field(\$model, '$attribute')
\t->passwordInput()
\t->label(\$model->getAttributeLabel('$attribute'))";
			} else {
				return "echo \$form->field(\$model, '$attribute')
\t->label(\$model->getAttributeLabel('$attribute'))";
            }
        }
        $column = $tableSchema->columns[$attribute];
		$commentArray = explode(',', $column->comment);
		$modelClass = StringHelper::basename($this->modelClass);
		$primaryKey = $this->getPrimaryKey($tableSchema);
		$foreignKeys = $this->getForeignKeys($tableSchema->foreignKeys);
		$i18n = 0;
		$foreignCondition = 0;
		$smallintCondition = 0;
		if(in_array('trigger[delete]', $commentArray)) {
			$attribute = $column->name.'_i';
			$i18n = 1;
		}
		if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
			$foreignCondition = 1;
			if(preg_match('/(smallint)/', $column->type))
				$smallintCondition = 1;
		}
		$dropDownOptions = $this->dropDownOptions($tableSchema);

		if ($column->phpType === 'boolean' || $column->dbType == 'tinyint(1)') {	// 01 //oke
			if($attribute == 'permission') {
				$relationName = $this->setRelation($attribute);
				$functionName = ucfirst($relationName);
				$label = $this->generateString('Select whether or not you want to let the public (visitors that are not logged-in) to view the following sections of your social network. In some cases (such as Profiles, Blogs, and Albums), if you have given them the option, your users will be able to make their pages private even though you have made them publically viewable here. For more permissions settings, please visit the General Settings page.');
				return "\$$relationName = $modelClass::get$functionName();
echo \$form->field(\$model, '$attribute', ['template' => '{label}{beginWrapper}{hint}{input}{error}{endWrapper}'])
\t->radioList(\$$relationName)
\t->label(\$model->getAttributeLabel('$attribute'))
\t->hint($label)";

			} elseif($column->comment != '' && $column->comment[0] == '"') {
				$relationName = $this->setRelation($attribute);
				$functionName = ucfirst($relationName);
				return "\$$relationName = $modelClass::get$functionName();
echo \$form->field(\$model, '$attribute')
\t->dropDownList(\$$relationName, ['prompt'=>''])
\t->label(\$model->getAttributeLabel('$attribute'))";

			} else {
				return "echo \$form->field(\$model, '$attribute')
\t->checkbox()
\t->label(\$model->getAttributeLabel('$attribute'))";
			}
		}

		if ($column->name === 'email') {	// 02 //oke
			return "echo \$form->field(\$model, '$attribute')
\t->textInput(['type'=>'email'])
\t->label(\$model->getAttributeLabel('$attribute'))";
		}

		if (in_array($column->dbType, array('timestamp','datetime','date'))) {	// 03
			// Jui datepicker lebih fleksibel terhadap dukungan browser dan dapat diformat tanggalnya
			// dari pada html5.
// 			if($this->useJuiDatePicker) {
// 				return "echo \$form->field(\$model, '$attribute')
// \t->widget(\yii\jui\DatePicker::classname(), ['dateFormat' => Yii::\$app->formatter->dateFormat, 'options' => ['type'=>'date', 'class'=>'form-control']])
// \t->label(\$model->getAttributeLabel('$attribute'))";
// 			} else {
				return "echo \$form->field(\$model, '$attribute')
\t->textInput(['type' => 'date'])
\t->label(\$model->getAttributeLabel('$attribute'))";
			// }
		}
		
		if ($column->type === 'text' || $i18n) {	// 04
			if(in_array('file', $commentArray)) {	// 04.1
				$relationName = $this->setRelation($attribute);
				$uploadPath = $this->uploadPathSubfolder ? "join('/', [$modelClass::getUploadPath(false), \$model->$primaryKey])" : "$modelClass::getUploadPath(false)";
				return "\$uploadPath = $uploadPath;
\$$relationName = !\$model->isNewRecord && \$model->old_{$attribute} != '' ? Html::img(join('/', [Url::Base(), \$uploadPath, \$model->old_{$attribute}]), ['class'=>'mb-15', 'width'=>'100%']) : '';
echo \$form->field(\$model, '$attribute', ['template' => '{label}{beginWrapper}<div>'.\$$relationName.'</div>{input}{error}{endWrapper}'])
\t->fileInput()
\t->label(\$model->getAttributeLabel('$attribute'))";

			} else if(in_array('redactor', $commentArray)) {	// 04.2
				return "echo \$form->field(\$model, '$attribute')
\t->textarea(['rows'=>6, 'cols'=>50])
\t->widget(Redactor::className(), ['clientOptions' => \$redactorOptions])
\t->label(\$model->getAttributeLabel('$attribute'))";

			} else if(in_array('text', $commentArray)) {	// 04.3
				$maxlength = $i18n ? ', \'maxlength\'=>true' : '';
				return "echo \$form->field(\$model, '$attribute')
\t->textarea(['rows'=>6, 'cols'=>50$maxlength])
\t->label(\$model->getAttributeLabel('$attribute'))";

			} else {	// 04.4
				if($i18n) {	// 04.4.1
					return "echo \$form->field(\$model, '$attribute')
\t->textInput(['maxlength'=>true])
\t->label(\$model->getAttributeLabel('$attribute'))";
				} else {	// 04.4.2
					return "echo \$form->field(\$model, '$attribute')
\t->textarea(['rows'=>6, 'cols'=>50])
\t->label(\$model->getAttributeLabel('$attribute'))";
				}
			}
		}
			
		if (is_array($column->enumValues) && count($column->enumValues) > 0) {
			$dropDownOptionKey = $dropDownOptions[$column->dbType];
			$relationName = $this->setRelation($column->name);
			$functionName = ucfirst($this->setRelation($dropDownOptionKey));
			return "\$$relationName = $modelClass::get$functionName();
echo \$form->field(\$model, '$attribute')
\t->dropDownList(\$$relationName, ['prompt' => ''])
\t->label(\$model->getAttributeLabel('$attribute'))";
		}

		if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name))
			$input = 'passwordInput';
		else
			$input = 'textInput';

		if ($column->phpType !== 'string' || $column->size === null) {
			if($foreignCondition && $smallintCondition) {
				$relationName = $this->setRelation($column->name);
				$relationTableName = trim($foreignKeys[$column->name]);
				$relationClassName = $this->generateClassName($relationTableName);
				$functionName = Inflector::singularize($this->setRelation($relationClassName, true));
				return "\$$relationName = $relationClassName::get$functionName();
echo \$form->field(\$model, '$attribute')
\t->dropDownList(\$$relationName, ['prompt'=>''])
\t->label(\$model->getAttributeLabel('$attribute'))";

			} else {
				return "echo \$form->field(\$model, '$attribute')
\t->$input(['type'=>'number', 'min'=>'1'])
\t->label(\$model->getAttributeLabel('$attribute'))";
			}
		}

		if($attribute == 'license') {
			$label = $this->generateString('Enter the your license key that is provided to you when you purchased this plugin. If you do not know your license key, please contact support team.');
			$label2 = $this->generateString('Format: XXXX-XXXX-XXXX-XXXX');
			return "if(\$model->isNewRecord && !\$model->getErrors())
	\$model->$attribute = \$model->licenseCode();
echo \$form->field(\$model, '$attribute')
\t->$input(['maxlength'=>true])
\t->label(\$model->getAttributeLabel('$attribute'))
\t->hint($label.'<br/>'.$label2)";
		}

				return "echo \$form->field(\$model, '$attribute')
\t->$input(['maxlength'=>true])
\t->label(\$model->getAttributeLabel('$attribute'))";
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
            return "echo \$form->field(\$model, '$attribute')";
        }

        $column = $tableSchema->columns[$attribute];
		$modelClass = StringHelper::basename($this->modelClass);
		$foreignKeys = $this->getForeignKeys($tableSchema->foreignKeys);
		$commentArray = explode(',', $column->comment);

		$i18n = 0;
		$foreignCondition = 0;
		$smallintCondition = 0;
		if(in_array('trigger[delete]', $commentArray)) {
			$attribute = $column->name.'_i';
			$i18n = 1;
		}
		if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
			$foreignCondition = 1;
			if(preg_match('/(smallint)/', $column->type))
				$smallintCondition = 1;
		}
		$dropDownOptions = $this->dropDownOptions($tableSchema);

		if($foreignCondition || in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])) {
			$relationName = $this->setRelation($column->name);
			$relationAttribute = 'displayname';
			$attribute = $relationName.ucwords(Inflector::id2camel($relationAttribute, '_'));
			if($foreignCondition) {
				$relationTable = trim($foreignKeys[$column->name]);
				$relationSchema = $this->getTableSchemaWithTableName($relationTable);
				$relationAttribute = key($this->getNameAttributes($relationSchema));
				if(in_array($relationTable, ['ommu_users', 'ommu_members']))
					$relationAttribute = 'displayname';
				$attribute = $relationName.ucwords(Inflector::id2camel($relationAttribute, '_'));
				if(preg_match('/('.$relationName.')/', $relationAttribute))
					$attribute = lcfirst(Inflector::id2camel($relationAttribute, '_'));
			}
			if($column->name == 'tag_id')
				$attribute = $relationName.ucwords('body');
			if($smallintCondition) {
				$attribute = $column->name;
				$relationTable = trim($foreignKeys[$column->name]);
				$relationClassName = $this->generateClassName($relationTable);
				$functionName = Inflector::singularize($this->setRelation($relationClassName, true));
				return "\$$relationName = $relationClassName::get$functionName();
		echo \$form->field(\$model, '$attribute')
			->dropDownList(\$$relationName, ['prompt'=>''])";
			}
			return "echo \$form->field(\$model, '$attribute')";

		} elseif(in_array($column->dbType, ['timestamp','datetime','date'])) {
			// if($this->useJuiDatePicker)
			// 	return "echo \$form->field(\$model, '$attribute')\n\t\t\t->widget(\yii\jui\DatePicker::classname(), [\n\t\t\t\t'dateFormat' => Yii::\$app->formatter->dateFormat,\n\t\t\t\t'options' => ['class' => 'form-control']\n\t\t\t])";
			// else
				return "echo \$form->field(\$model, '$attribute')\n\t\t\t->input('date')";

		} elseif (is_array($column->enumValues) && count($column->enumValues) > 0) {
			$dropDownOptionKey = $dropDownOptions[$column->dbType];
			$relationName = $this->setRelation($column->name);
			$functionName = ucfirst($this->setRelation($dropDownOptionKey));
			return "\$$relationName = $modelClass::get$functionName();
			echo \$form->field(\$model, '$attribute')
			->dropDownList(\$$relationName, ['prompt'=>''])";

		} else {
			if($i18n) {
				$attributeName = $column->name.'_i';
				return "echo \$form->field(\$model, '$attributeName')";
			} else {
				if ($column->phpType === 'boolean' || $column->dbType == 'tinyint(1)') {
					if($attribute == 'permission' || ($column->comment != '' && $column->comment[0] == '"')) {
						$relationName = $this->setRelation($column->name);
						$functionName = ucfirst($relationName);
						return "\$$relationName = $modelClass::get$functionName();
			echo \$form->field(\$model, '$attribute')
			->dropDownList(\$$relationName, ['prompt'=>''])";
					} else
						return "echo \$form->field(\$model, '$attribute')
			->dropDownList(\$model->filterYesNo(), ['prompt'=>''])";
				} else
					return "echo \$form->field(\$model, '$attribute')";
			}
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
        }

        if ($column->type === 'text') {
            return 'ntext';
        }

        if (stripos($column->name, 'time') !== false && $column->phpType === 'integer') {
            return 'datetime';
        }

        if (stripos($column->name, 'email') !== false) {
            return 'email';
        }

        if (preg_match('/(\b|[_-])url(\b|[_-])/i', $column->name)) {
            return 'url';
        }

        return 'text';
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
                case Schema::TYPE_TINYINT:
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

        $likeConditions = [];
        $hashConditions = [];
        $publishConditions = [];

        $arrayHasColumn = [];
		$arrayLikeColumn = [];
        foreach ($columns as $column => $type) {
			$col = $tableSchema->columns[$column];
			$commentArray = explode(',', $col->comment);
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
                    if(!$col->isPrimaryKey && ((!empty($foreignKeys) && array_key_exists($column, $foreignKeys)) || in_array('user', $commentArray) || in_array($column, array('creation_id','modified_id','user_id','updated_id','tag_id','member_id')))) {
                        if(!in_array($column, $arrayHasColumn)) {
                            $arrayHasColumn[] = $column;
                            $relation = $this->setRelation($column);
                            $hashConditions[] = "'t.{$column}' => isset(\$params['$relation']) ? \$params['$relation'] : \$this->{$column},";
                        }
					}
					if(in_array($type, array('timestamp','datetime','date'))) {
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
					} else if(is_array($col->enumValues)) {
						$arrayHasColumn[] = $column;
						$hashConditions[] = "'t.{$column}' => \$this->{$column},";
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
        $publicVariables = [];

        foreach ($tableSchema->columns as $column):
            $commentArray = explode(',', $column->comment);
            if(in_array('trigger[delete]', $commentArray)):
                $relationName = $this->i18nRelation($column->name);;
                $publicVariable = $column->name.'_i';
                if(!in_array($publicVariable, $publicVariables)) {
                    $publicVariables[] = $publicVariable;
                    $likeConditions[] = "->andFilterWhere(['like', '{$relationName}.message', \$this->{$publicVariable}])";
                }
            endif;
        endforeach;
        foreach ($tableSchema->columns as $column): 
            if(in_array($column->name, ['tag_id'])):
                $relationName = $this->setRelation($column->name);
                $publicVariable = $relationName.ucwords('body');
                if(!in_array($publicVariable, $publicVariables)) {
                    $publicVariables[] = $publicVariable;
                    $likeConditions[] = "->andFilterWhere(['like', '{$relationName}.body', \$this->{$publicVariable}])";
                }
            endif;
        endforeach;
        foreach ($tableSchema->columns as $column): 
            if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, array('tag_id'))):
                $relationName = $this->setRelation($column->name);
				$relationFixedName = $this->setRelationFixed($relationName, $tableSchema->columns);
				$relationTable = trim($foreignKeys[$column->name]);
				$relationSchema = $this->getTableSchemaWithTableName($relationTable);
				$relationAttribute = key($this->getNameAttributes($relationSchema));
				if(in_array($relationTable, ['ommu_users', 'ommu_members']))
					$relationAttribute = 'displayname';
				$publicVariable = $relationName.ucwords(Inflector::id2camel($relationAttribute, '_'));
				if(preg_match('/('.$relationName.')/', $relationAttribute))
					$publicVariable = lcfirst(Inflector::id2camel($relationAttribute, '_'));
                if($relationTable != 'ommu_users')
                	$relationAttribute = $this->getName2ndAttribute($relationName, $this->getNameAttribute($relationTable, '.'));
                if(!in_array($publicVariable, $publicVariables)) {
                    $publicVariables[] = $publicVariable;
                    $likeConditions[] = "->andFilterWhere(['like', '{$relationFixedName}.{$relationAttribute}', \$this->{$publicVariable}])";
                }
            endif;
        endforeach;
        foreach ($tableSchema->columns as $column): 
			if($column->autoIncrement || $column->isPrimaryKey)
				continue;
			if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
				continue;

			$commentArray = explode(',', $column->comment);
            if(in_array('user', $commentArray) || in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id'))):
                $relationName = $this->setRelation($column->name);
				$relationFixedName = $this->setRelationFixed($relationName, $tableSchema->columns);
                $publicVariable = $relationName.ucwords('displayname');
                if(!in_array($publicVariable, $publicVariables)) {
                    $publicVariables[] = $publicVariable;
                    $likeConditions[] = "->andFilterWhere(['like', '{$relationFixedName}.displayname', \$this->{$publicVariable}])";
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
                return "'id'=>(string)\$model->{$pks[0]}";
            }

            return "'id'=>\$model->{$pks[0]}";
        }

        $params = [];
        foreach ($pks as $pk) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                $params[] = "'$pk'=>(string)\$model->$pk";
            } else {
                $params[] = "'$pk'=>\$model->$pk";
            }
        }

        return implode(', ', $params);
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
        }

        return '$' . implode(', $', $pks);
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
                $params[] = '@param ' . (strtolower(substr($pk, -2)) === 'id' ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
        }

        $params = [];
        foreach ($pks as $pk) {
            $params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
        }

        return $params;
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return bool|\yii\db\TableSchema
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
        }

        /* @var $model \yii\base\Model */
        $model = new $class();

        return $model->attributes();
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
		$className = Inflector::id2camel($schemaName.$className, '_');
		if(preg_match('/Swt/', $className))
			$className = preg_replace('(Swt)', '', $className);
		else
			$className = preg_replace('(Ommu)', '', $className);

		return $this->classNames[$fullTableName] = $className;
	}
	
	public function replaceModel($model)
	{
		$modelPath = ltrim($this->modelClass, '\\');
		$modelArray = explode('\\', $modelPath);
		array_pop($modelArray);
		$modelArray[] = $model;

		return implode('\\', $modelArray);
	}

	public function modelLabel($modelClass)
	{
		$patternLabel = [];
		$patternLabel[0] = '(Core)';
		$patternLabel[1] = '(Zone)';
		$patternLabel[2] = '(Ommu)';

		$label = Inflector::camel2words(Inflector::singularize(preg_replace($patternLabel, '', $modelClass)));

		return $label;
	}

	public function shortLabel($modelClass)
	{
		$label = $this->modelLabel($modelClass);
		$labels = explode(' ', $label);

		if(count($labels) != 1) {
			array_shift($labels);
			if(is_array($labels))
				return implode(' ', $labels);
			else
				return $labels;

		} else {
			if(is_array($labels))
				return implode(' ', $labels);
			else
				return $labels;
		}
	}

	public function i18nRelation($column, $relation=true)
	{
		return preg_match('/(name|title)/', $column) ? 'title' : (preg_match('/(desc|description)/', $column) ? ($column != 'description' ? 'description' :  ($relation == true ? $column.'Rltn' : $column)) : ($relation == true ? $column.'Rltn' : $column));
	}

	public function getPrimaryKey($table)
	{
		if(!empty($table->primaryKey))
			$primaryKey = $table->primaryKey['0'];
		else
			$primaryKey = key($table->columns);

		return $primaryKey;
	}

	public function dropDownOptions($table)
	{
		$dropDownOptions = [];
		foreach ($table->columns as $column) {
			if(is_array($column->enumValues) && !in_array($column->dbType, $dropDownOptions))
				$dropDownOptions[$column->name] = $column->dbType;
		}

		return array_flip($dropDownOptions);
	}

	public function getTableSchemaWithTableName($tableName) 
	{
		$db = $this->getDbConnection();
		$tableSchema = $db->getTableSchema($tableName);

		return $tableSchema; 
	}

	public function getModuleName() 
	{
		$modelClass = explode('models', $this->modelClass);
		$moduleArray = explode('\\', trim($modelClass[0], '\\'));

		return end($moduleArray);
	}

	public function getUseModel($module, $modelClass) 
	{
		return str_replace([$this->getModuleName(), StringHelper::basename($this->modelClass)], [$module, $modelClass], $this->modelClass);
	}
}
