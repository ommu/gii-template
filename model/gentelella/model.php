<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

/**
 * Variable
 */
use ommu\gii\model\Generator;
use yii\helpers\Inflector;

$patternClass = [];
$patternClass[0] = '(Ommu)';
$patternClass[1] = '(Swt)';

/**
 * Condition
 */
$tinyCondition = 0;
$publishCondition = 0;
$permissionCondition = 0;
$slugCondition = 0;
$tagCondition = 0;
$memberCondition = 0;
$userCondition = 0;
$uploadCondition = 0;
$serializeCondition = 0;
$i18n = 0;
$dateCondition = 0;
$useGetFunctionCondition = 0;
$relationCondition = 0;

$relationArray = [];
$inputPublicVariables = [];
$searchPublicVariables = [];
$arrayAttributeName = [];

if($tableType == Generator::TYPE_VIEW)
	$primaryKey = $viewPrimaryKey;
else
	$primaryKey = $generator->getPrimaryKey($tableSchema);

/**
 * foreignKeys Column
 */
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?= $className."\n" ?>
 * 
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($generator->useModified):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @modified by <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
<?php endif; ?>
 * @link <?php echo $generator->link."\n";?>
 *
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
 * The followings are the available columns in table "<?= $generator->generateTableName($tableName) ?>":
<?php /* foreach ($properties as $property => $data): ?>
 * @property <?= "{$data['type']} \${$property}"  . ($data['comment'] ? ' ' . strtr($data['comment'], ["\n" => ' ']) : '') . "\n" ?>
<?php endforeach; */?>
<?php 
foreach ($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray) || in_array('user', $commentArray) || (!in_array($primaryKey, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id']) && in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])))
		$relationCondition = 1;?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php }

if (!empty($relations) || $relationCondition) {?>
 *
 * The followings are the available model relations:
<?php foreach ($relations as $name => $relation) {
	$relationModel = preg_replace($patternClass, '', $relation[1]);
	$relationArray[] = $relationName = ($relation[2] ? lcfirst($generator->setRelation($name, true)) : $generator->setRelation($name));?>
 * @property <?= $relationModel . ($relation[2] ? '[]' : '') . ' $' . $relationName ."\n" ?>
<?php }

foreach ($tableSchema->columns as $column) {
	if($column->autoIncrement || $column->isPrimaryKey)
		continue;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		continue;

	$commentArray = explode(',', $column->comment);
	if($column->dbType == 'tinyint(1)') {
		$tinyCondition = 1;
		if(in_array($column->name, ['publish','headline']))
			$publishCondition = 1;
		if($column->name == 'permission')
			$permissionCondition = 1;
	} elseif($column->name == 'slug') 
		$slugCondition = 1;
	elseif(in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])) {
		$relationName = $generator->setRelation($column->name);
		$relationName = $generator->setRelationFixed($relationName, $tableSchema->columns);
		if(!in_array($relationName, $relationArray)) {
			$relationArray[] = $relationName;
			if($column->name == 'tag_id')
				echo " * @property CoreTags \${$relationName}\n";
			else if($column->name == 'member_id')
				echo " * @property Members \${$relationName}\n";
			else if(in_array($column->name, ['creation_id','modified_id','user_id','updated_id']))
				echo " * @property Users \${$relationName}\n";
			else if(in_array('user', $commentArray))
				echo " * @property Users \${$relationName}\n";
		}
		if($column->name == 'tag_id')
			$tagCondition = 1;
		else if($column->name == 'member_id')
			$memberCondition = 1;
		else if(in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id']))
			$userCondition = 1;
	} elseif(in_array($column->dbType, ['timestamp','datetime','date'])) {
		$dateCondition = 1;
	} else {
		if($tableType != Generator::TYPE_VIEW && $column->type == 'text' && in_array('file', $commentArray))
			$uploadCondition = 1;
		else if($tableType != Generator::TYPE_VIEW && $column->type == 'text' && in_array('serialize', $commentArray))
			$serializeCondition = 1;
		else {
			if(in_array('trigger[delete]', $commentArray)) {
				$i18n = 1;
				$relationName = $generator->i18nRelation($column->name);
				echo " * @property SourceMessage \${$relationName}\n";
			}
		}
	}
}

$primaryKeyColumn = $tableSchema->columns[$primaryKey];
if($primaryKeyColumn->type == 'smallint' || ($primaryKeyColumn->type == 'tinyint' && $primaryKeyColumn->dbType != 'tinyint(1)' && !$permissionCondition))
	$useGetFunctionCondition = 1;
}?>
 *
 */

namespace <?= $generator->ns ?>;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
<?php 
echo $uploadCondition ? "use ".ltrim('yii\web\UploadedFile', '\\').";\n" : '';
echo $slugCondition ? "use ".ltrim('yii\behaviors\SluggableBehavior', '\\').";\n" : '';
echo $tagCondition ? "use ".ltrim('app\models\CoreTags', '\\').";\n" : '';
echo $i18n ? "use ".ltrim('app\models\SourceMessage', '\\').";\n" : '';
echo $userCondition ? "use ".ltrim('ommu\users\models\Users', '\\').";\n" : '';
echo $memberCondition ? "use ".ltrim('ommu\member\models\Members', '\\').";\n" : '';
?>

class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
<?php echo $tinyCondition || $tagCondition || $i18n || $dateCondition ? "\tuse \\".ltrim('\ommu\traits\UtilityTrait', '\\').";\n" : '';?>
<?php echo $uploadCondition ? "\tuse \\".ltrim('\ommu\traits\FileTrait', '\\').";\n" : '';?>
<?php echo $tinyCondition || $tagCondition || $uploadCondition || $i18n || $dateCondition ? "\n" : '';?>
	public $gridForbiddenColumn = [];
<?php 
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($tableType != Generator::TYPE_VIEW && in_array('trigger[delete]', $commentArray)) {
		$inputPublicVariable = $column->name.'_i';
		if(!in_array($inputPublicVariable, $inputPublicVariables))
			$inputPublicVariables[$inputPublicVariable] = Inflector::camel2words(Inflector::id2camel($column->name));
	}
}
foreach ($tableSchema->columns as $column) {
	if($tableType != Generator::TYPE_VIEW && in_array($column->name, ['tag_id'])) {
		$relationName = $generator->setRelation($column->name);
		$inputPublicVariable = $relationName.'_i';
		if(!in_array($inputPublicVariable, $inputPublicVariables))
			$inputPublicVariables[$inputPublicVariable] = ucwords(strtolower($relationName));
	}
}
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($tableType != Generator::TYPE_VIEW && $column->type == 'text' && in_array('file', $commentArray)) {
		$inputPublicVariable = 'old_'.$column->name.'_i';
		if(!in_array($inputPublicVariable, $inputPublicVariables))
			$inputPublicVariables[$inputPublicVariable] = Inflector::camel2words('Old'.Inflector::id2camel($column->name));
	}
}

foreach ($tableSchema->columns as $column) {
	if($tableType != Generator::TYPE_VIEW && !empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['tag_id'])) {
		$smallintCondition = 0;
		if(preg_match('/(smallint)/', $column->type))
			$smallintCondition = 1;
		$relationName = $generator->setRelation($column->name);
		$searchPublicVariable = $relationName.'_search';
		if(!$smallintCondition && !in_array($searchPublicVariable, $searchPublicVariables))
			$searchPublicVariables[$searchPublicVariable] = ucwords(strtolower($relationName));
	}
}
foreach ($tableSchema->columns as $column) {
	if($column->autoIncrement || $column->isPrimaryKey)
		continue;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		continue;

	$commentArray = explode(',', $column->comment);
	if($tableType != Generator::TYPE_VIEW && (in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','member_id']))) {
		$relationName = $generator->setRelation($column->name);
		$searchPublicVariable = $relationName.'_search';
		if(!in_array($searchPublicVariable, $searchPublicVariables))
			$searchPublicVariables[$searchPublicVariable] = ucwords(strtolower($relationName));
	}
}

if(!empty($inputPublicVariables)) {
	foreach ($inputPublicVariables as $key=>$val) {
		echo "\tpublic $$key;\n";
	}
}
if(!empty($searchPublicVariables)) {
	echo "\n\t// Search Variable\n"; 
	foreach ($searchPublicVariables as $key=>$val) {
		echo "\tpublic $$key;\n";
	}
}?>

	/**
	 * @return string the associated database table name
	 */
	public static function tableName()
	{
		return '<?= $generator->generateTableName($tableName) ?>';
	}
<?php if($tableType == Generator::TYPE_VIEW) {?>

	/**
	 * @return string the primarykey column
	 */
	public static function primaryKey()
	{
		return ['<?=$viewPrimaryKey?>'];
	}
<?php }

if ($generator->db !== 'db') {?>

	/**
	 * @return \yii\db\Connection the database connection used by this AR class.
	 */
	public static function getDb()
	{
		return Yii::$app->get('<?= $generator->db ?>');
	}
<?php }

if ($slugCondition) {
	$tableAttribute = $generator->getNameAttribute(null, '.'); ?>

	/**
	 * behaviors model class.
	 */
	public function behaviors() {
		return [
			[
				'class' => SluggableBehavior::className(),
				'attribute' => '<?php echo $tableAttribute;?>',
				'immutable' => true,
				'ensureUnique' => true,
			],
		];
	}
<?php }?>

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return [<?= empty($rules) ? '' : ("\n			" . implode(",\n			", preg_replace($patternClass, '', $rules)) . ",\n		") ?>];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return [
<?php 
foreach ($labels as $name => $label) {
	if($name[0] == '_')
		continue;
	echo "\t\t\t'$name' => " . $generator->generateString($label) . ",\n";
}

if(!empty($inputPublicVariables)) {
	foreach ($inputPublicVariables as $key=>$val) {
		echo "\t\t\t'$key' => " . $generator->generateString($val) . ",\n";
	}
}

if(!empty($searchPublicVariables)) {
	foreach ($searchPublicVariables as $key=>$val) {
		echo "\t\t\t'$key' => " . $generator->generateString($val) . ",\n";
	}
} ?>
		];
	}
<?php 
$relationArray = [];
foreach ($relations as $name => $relation) {
	$relationArray[] = $relationName = ($relation[2] ? $generator->setRelation($name, true) : $generator->setRelation($name));?>

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function get<?php echo ucfirst($relationName);?>()
	{
		<?= preg_replace($patternClass, '', $relation[0]) . "\n" ?>
	}
<?php }

if($i18n) {
	foreach ($tableSchema->columns as $column) {
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)) {
			$relationName = $generator->i18nRelation($column->name);
			if(!in_array($relationName, $relationArray)) {
				$relationArray[] = $relationName;?>

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function get<?php echo ucfirst($relationName);?>()
	{
		return $this->hasOne(SourceMessage::className(), ['id' => '<?php echo $column->name;?>']);
	}
<?php		}
		}
	}
}

foreach ($tableSchema->columns as $column) {
	if($column->autoIncrement || $column->isPrimaryKey)
		continue;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		continue;

	$commentArray = explode(',', $column->comment);
	if(in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])) {
		$relationName = $generator->setRelation($column->name);
		$relationName = $generator->setRelationFixed($relationName, $tableSchema->columns);
		if(!in_array($relationName, $relationArray)) {
			$relationArray[] = $relationName;
			$relationModelClass = 'Users';
			$relationAttribute = 'user_id';
			if($column->name == 'tag_id') {
				$relationModelClass = 'CoreTags';
				$relationAttribute = 'tag_id';
			}
			if($column->name == 'member_id') {
				$relationModelClass = 'Members';
				$relationAttribute = 'member_id';
			}?>

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function get<?php echo ucfirst($relationName);?>()
	{
		return $this->hasOne(<?php echo $relationModelClass;?>::className(), ['<?php echo $relationAttribute;?>' => '<?php echo $column->name;?>']);
	}
<?php 	}
	}
}

if($queryClassName): 
	$queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
	echo "\n";
?>
	/**
	 * {@inheritdoc}
	 * @return <?= $queryClassFullName ?> the active query used by this AR class.
	 */
	public static function find()
	{
		return new <?= $queryClassFullName ?>(get_called_class());
	}
<?php endif; ?>

	/**
	 * Set default columns to display
	 */
	public function init() 
	{
		parent::init();

		$this->templateColumns['_no'] = [
			'header' => <?php echo $generator->generateString('No');?>,
			'class'  => 'yii\grid\SerialColumn',
			'contentOptions' => ['class'=>'center'],
		];
<?php 
$publicAttributes = [];
$enumArray = [];
foreach ($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;
	if($column->autoIncrement || $column->isPrimaryKey || $column->phpType === 'boolean' || ($column->dbType == 'tinyint(1)' && $column->name != 'permission'))
		continue;

	$commentArray = explode(',', $column->comment);
	$foreignCondition = 0;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		$foreignCondition = 1;

	if($foreignCondition || in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])) {
		$smallintCondition = 0;
		if(preg_match('/(smallint)/', $column->type))
			$smallintCondition = 1;
		$relationName = $generator->setRelation($column->name);
		$relationFixedName = $generator->setRelationFixed($relationName, $tableSchema->columns);
		$publicAttribute = $relationName.'_search';
		$relationAttribute = 'displayname';
		if(array_key_exists($column->name, $foreignKeys)) {
			$relationTableName = trim($foreignKeys[$column->name]);
			$relationAttribute = $generator->getNameAttribute($relationTableName);
			if($relationTableName == 'ommu_users')
				$relationAttribute = 'displayname';
		}
		if($column->name == 'tag_id') {
			$publicAttribute = $relationName.'_i';
			$relationAttribute = 'body';
		}
		if($smallintCondition)
			$publicAttribute = $column->name;

		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;?>
		if(!Yii::$app->request->get('<?php echo $relationName;?>')) {
			$this->templateColumns['<?php echo $publicAttribute;?>'] = [
				'attribute' => '<?php echo $publicAttribute;?>',
				'value' => function($model, $key, $index, $column) {
					return isset($model-><?php echo $relationFixedName;?>) ? $model-><?php echo $relationFixedName;?>-><?php echo $relationAttribute;?> : '-';
				},
<?php if($foreignCondition && $smallintCondition) {
	$relationClassName = $generator->generateClassName($relationTableName);
	$relationFunctionName = Inflector::singularize($generator->setRelation($relationClassName, true));?>
				'filter' => <?php echo $relationClassName;?>::get<?php echo $relationFunctionName;?>(),
<?php }?>
			];
		}
<?php 	}
	} elseif(in_array($column->dbType, ['timestamp','datetime','date'])) {
		$publicAttribute = $column->name;
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;?>
		$this->templateColumns['<?php echo $publicAttribute;?>'] = [
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => function($model, $key, $index, $column) {
				return !in_array($model-><?php echo $column->name;?>, <?php echo $column->type == 'date' ? '[\'0000-00-00\',\'1970-01-01\',\'0002-12-02\',\'-0001-11-30\']' : '[\'0000-00-00 00:00:00\',\'1970-01-01 00:00:00\',\'0002-12-02 07:07:12\',\'-0001-11-30 00:00:00\']';?>) ? Yii::$app->formatter->format($model-><?php echo $column->name;?>, '<?php echo $column->dbType == 'date' ? $column->dbType : 'datetime';?>') : '-';
			},
			'filter' => $this->filterDatepicker($this, '<?php echo $column->name;?>'),
			'format' => 'html',
		];
<?php 	}
	} else if($column->type == 'text' && in_array('file', $commentArray)) {
		$publicAttribute = $column->name;
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;?>
		$this->templateColumns['<?php echo $publicAttribute;?>'] = [
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => function($model, $key, $index, $column) {
<?php if($generator->uploadPath['subfolder']) {?>
				$uploadPath = join('/', [self::getUploadPath(false), $model-><?php echo $primaryKey;?>]);
<?php } else {?>
				$uploadPath = self::getUploadPath(false);
<?php }?>
				return $model-><?php echo $publicAttribute;?> ? Html::img(join('/', [$uploadPath, $model-><?php echo $publicAttribute;?>]), ['alt' => $model-><?php echo $publicAttribute;?>]) : '-';
			},
			'format' => 'html',
		];
<?php 	}
	} else if(is_array($column->enumValues)) {
		$publicAttribute = $column->name;
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;
			if(!in_array($column->dbType, $enumArray)) {
				$enumArray[$column->name] = $column->dbType;
				$functionName = ucfirst($generator->setRelation($column->name));
			} else {
				$enumKey = array_flip($enumArray)[$column->dbType];
				$functionName = ucfirst($generator->setRelation($enumKey));
			}?>
		$this->templateColumns['<?php echo $publicAttribute;?>'] = [
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => function($model, $key, $index, $column) {
				return self::get<?php echo $functionName;?>($model-><?php echo $publicAttribute;?>);
			},
		];
<?php 	}
	} else {
		$publicAttribute = $column->name;
		$translateCondition = 0;
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $column->name.'_i';
			$publicAttributeRelation = $generator->i18nRelation($column->name);
			$translateCondition = 1;
		}
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;?>
		$this->templateColumns['<?php echo $publicAttribute;?>'] = [
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => function($model, $key, $index, $column) {
<?php if($translateCondition) {?>
				return $model-><?php echo $publicAttribute;?>;
<?php } else {
	if($column->type == 'text' && in_array('serialize', $commentArray)) {?>
				return serialize($model-><?php echo $publicAttribute;?>);
<?php } else if($column->name == 'permission') {
		$functionName = ucfirst($generator->setRelation($column->name));?>
				return self::get<?php echo $functionName;?>($model-><?php echo $publicAttribute;?>);
<?php } else {?>
				return $model-><?php echo $publicAttribute;?>;
<?php }
}?>
			},
<?php if(in_array('redactor', $commentArray)) {?>
			'format' => 'html',
<?php }?>
		];
<?php 		}
	}
}

foreach ($tableSchema->columns as $column) {
	$comment = $column->comment;
	if($column->name[0] == '_')
		continue;
	if($column->dbType == 'tinyint(1)' && (in_array($column->name, ['publish','headline','permission']) || ($comment != '' && $comment[7] != '[')))
		continue;
		
	if ($column->phpType === 'boolean' || $column->dbType == 'tinyint(1)') {?>
		$this->templateColumns['<?php echo $column->name;?>'] = [
			'attribute' => '<?php echo $column->name;?>',
			'filter' => $this->filterYesNo(),
			'value' => function($model, $key, $index, $column) {
				return $this->filterYesNo($model-><?php echo $column->name;?>);
			},
			'contentOptions' => ['class'=>'center'],
		];
<?php }
}

foreach ($tableSchema->columns as $column) {
	$comment = $column->comment;
	if($column->name[0] == '_')
		continue;
	if($column->phpType === 'boolean' || ($column->dbType == 'tinyint(1)' && in_array($column->name, ['publish','permission'])))
		continue;

	if($column->dbType == 'tinyint(1)' && ($column->name == 'headline' || ($comment != '' && $comment[7] != '['))) {
		if($column->name == 'headline' && $comment == '')
			$comment = 'Headline,Unheadline';?>
		$this->templateColumns['<?php echo $column->name;?>'] = [
			'attribute' => '<?php echo $column->name;?>',
<?php if($comment != '' && $comment[0] == '"') {
	$functionName = ucfirst($generator->setRelation($column->name));?>
			'filter' => self::get<?php echo $functionName;?>(),
<?php } else {?>
			'filter' => $this->filterYesNo(),
<?php }?>
			'value' => function($model, $key, $index, $column) {
<?php if($comment != '' && $comment[0] == '"') {
	$functionName = ucfirst($generator->setRelation($column->name));?>
				return self::get<?php echo $functionName;?>($model-><?php echo $column->name;?>);
<?php } else {?>
				$url = Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]);
				return $this->quickAction($url, $model-><?php echo $column->name;?>, '<?php echo $comment;?>'<?php echo $column->name == 'headline' ? ', true' : '';?>);
<?php }?>
			},
			'contentOptions' => ['class'=>'center'],
			'format' => 'raw',
		];
<?php }
}

foreach ($tableSchema->columns as $column) {
	$comment = $column->comment;
	if($column->dbType == 'tinyint(1)' && $column->name == 'publish') {?>
		if(!Yii::$app->request->get('trash')) {
			$this->templateColumns['<?php echo $column->name;?>'] = [
				'attribute' => '<?php echo $column->name;?>',
				'filter' => $this->filterYesNo(),
				'value' => function($model, $key, $index, $column) {
					$url = Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]);
					return $this->quickAction($url, $model-><?php echo $column->name;?><?php echo $comment != '' ? ", '$comment'" : '';?>);
				},
				'contentOptions' => ['class'=>'center'],
				'format' => 'raw',
			];
		}
<?php }
} ?>
<?php /*
		if(count($this->defaultColumns) == 0) {
foreach ($tableSchema->columns as $column):
	if(!$column->isPrimaryKey) {
		if(in_array($column->dbType, ['timestamp','datetime','date'])) {?>
			$this->defaultColumns[] = [
				'attribute' => '<?php echo $column->name;?>',
				'filter'	=> \yii\jui\DatePicker::widget(['dateFormat' => Yii::$app->formatter->dateFormat,
					'attribute' => '<?php echo $column->name;?>',
					'model'  => $this,
				]),
				'format'	=> 'html',
			];
<?php } else { ?>
			$this->defaultColumns[] = [
				'attribute' => '<?php echo $column->name;?>',
				'class'  => 'yii\grid\DataColumn',
			];
<?php   }
	}
endforeach; 
		}
*/ ?>
	}

	/**
	 * User get information
	 */
	public static function getInfo($id, $column=null)
	{
		if($column != null) {
			$model = self::find()
				->select([$column])
				->where(['<?php echo $primaryKey;?>' => $id])
				->one();
			return $model->$column;
			
		} else {
			$model = self::findOne($id);
			return $model;
		}
	}
<?php
if($tableType != Generator::TYPE_VIEW && ($generator->useGetFunction || $useGetFunctionCondition)) {
	$functionName = Inflector::singularize($generator->setRelation($className, true));
	$attributeName = key($generator->getNameAttributes($tableSchema));?>

	/**
	 * function get<?php echo $functionName."\n"; ?>
	 */
	public static function get<?php echo $functionName; ?>(<?php echo $publishCondition ? '$publish=null, $array=true' : '$array=true';?>) 
	{
		$model = self::find()->alias('t');
<?php 
$i18nRelation = $i18n && preg_match('/(name|title)/', $attributeName) ? 'title' : '';
if($i18nRelation)
	echo "\t\t\$model->leftJoin(sprintf('%s $i18nRelation', SourceMessage::tableName()), 't.$attributeName=$i18nRelation.id');\n";
	
if($publishCondition) {?>
		if($publish != null)
			$model->andWhere(['t.publish' => $publish]);

<?php }?>
		$model = $model->orderBy('<?php echo $i18nRelation ? $i18nRelation.'.message' : 't.'.$attributeName;?> ASC')->all();

		if($array == true)
			return \yii\helpers\ArrayHelper::map($model, '<?php echo $primaryKey;?>', '<?php echo $i18n && preg_match('/(name|title)/', $attributeName) ? $attributeName.'_i' : $attributeName;?>');

		return $model;
	}
<?php }

foreach ($tableSchema->columns as $column) {
	if(!($column->dbType == 'tinyint(1)' && $column->name == 'permission'))
		continue;

	$functionName = ucfirst($generator->setRelation($column->name));?>

	/**
	 * function get<?php echo $functionName."\n"; ?>
	 */
	public static function get<?php echo $functionName; ?>($value=null)
	{
		$items = array(
			1 => Yii::t('app', 'Yes, the public can view "module name" unless they are made private.'),
			0 => Yii::t('app', 'No, the public cannot view "module name".'),
		);

		if($value !== null)
			return $items[$value];
		else
			return $items;
	}
<?php }

$enumArray = [];
foreach ($tableSchema->columns as $column) {
	if((is_array($column->enumValues) && !in_array($column->dbType, $enumArray)) || ($column->dbType == 'tinyint(1)' && $column->comment != '' && $column->comment[0] == '"')) {
		$functionName = ucfirst($generator->setRelation($column->name));
		if(is_array($column->enumValues))
			$enumArray[$column->name] = $column->dbType;?>

	/**
	 * function get<?php echo $functionName."\n"; ?>
	 */
	public static function get<?php echo $functionName; ?>($value=null)
	{
		$items = array(
<?php $comment = trim($column->comment, '"');
if($comment != '')
	$dropDownOptions = $generator->comment2Array($comment);

if(is_array($column->enumValues)) {
	foreach($column->enumValues as $enumValue) {?>
			'<?php echo $enumValue;?>' => <?php echo $generator->generateString(ucfirst(strtolower($comment != '' ? $dropDownOptions[$enumValue] : $enumValue)));?>,
<?php }
} else {
	foreach($dropDownOptions as $key=>$val) {?>
			'<?php echo $key;?>' => <?php echo $generator->generateString(ucfirst(strtolower($val)));?>,
<?php }
}?>
		);

		if($value !== null)
			return $items[$value];
		else
			return $items;
	}
<?php }
}

if($uploadCondition) {
	$directoryPath = $generator->uploadPath['directory'];
	$returnAlias = join('/', ['@webroot', $directoryPath]);?>

	/**
	 * @param returnAlias set true jika ingin kembaliannya path alias atau false jika ingin string
	 * relative path. default true.
	 */
	public static function getUploadPath($returnAlias=true) 
	{
		return ($returnAlias ? Yii::getAlias('<?php echo $returnAlias;?>') : '<?php echo $directoryPath;?>');
	}
<?php }

$afEvents = 0;
if($tagCondition || $uploadCondition || $serializeCondition || $i18n)
	$afEvents = 1;
if($tableType != Generator::TYPE_VIEW && $afEvents) {?>

	/**
	 * after find attributes
	 */
	public function afterFind()
	{
<?php foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	
	if(in_array($column->name, ['tag_id'])) {
		$relationName = $generator->setRelation($column->name);
		$publicAttribute = $relationName.'_i';
		echo "\t\t\$this->$publicAttribute = isset(\$this->{$relationName}) ? \$this->{$relationName}->body : '';\n";

	} else if($column->type == 'text' && in_array('file', $commentArray)) {
		$inputPublicVariable = 'old_'.$column->name.'_i';
		echo "\t\t\$this->$inputPublicVariable = \$this->$column->name;\n";

	} else if($column->type == 'text' && in_array('serialize', $commentArray)) {
		echo "\t\t\$this->$column->name = unserialize(\$this->$column->name);\n";

	} else {
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $column->name.'_i';
			$publicAttributeRelation = $generator->i18nRelation($column->name);
			echo "\t\t\$this->$publicAttribute = isset(\$this->{$publicAttributeRelation}) ? \$this->{$publicAttributeRelation}->message : '';\n";
		}
	}
}?>
	}
<?php }

$bvEvents = 0;
$beforeValidate = 0;
$creationCondition = 0;
if($uploadCondition)
	$bvEvents = 1;
foreach($tableSchema->columns as $column)
{
	$nameArray = explode('_', $column->name);
	if(in_array($column->name, ['creation_id','modified_id','user_id','updated_id']) && $column->comment != 'trigger')
		$bvEvents = 1;
	if(in_array('ip', $nameArray))
		$bvEvents = 1;
}
if($tableType != Generator::TYPE_VIEW && ($generator->generateEvents || $bvEvents)) {?>

	/**
	 * before validate attributes
	 */
	public function beforeValidate()
	{
		if(parent::beforeValidate()) {
<?php if($uploadCondition) {
	$beforeValidate = 1;
	foreach($tableSchema->columns as $column) {
		$commentArray = explode(',', $column->comment);
		if($column->type == 'text' && in_array('file', $commentArray)) {
			$fileType = lcfirst(Inflector::singularize(Inflector::id2camel($column->name, '_')).'FileType');?>
			$<?php echo $fileType;?> = ['bmp','gif','jpg','png'];
			$<?php echo $column->name;?> = UploadedFile::getInstance($this, '<?php echo $column->name;?>');

			if($<?php echo $column->name;?> instanceof UploadedFile && !$<?php echo $column->name;?>->getHasError()) {
				if(!in_array(strtolower($<?php echo $column->name;?>->getExtension()), $<?php echo $fileType;?>)) {
					$this->addError('<?php echo $column->name;?>', Yii::t('app', 'The file {name} cannot be uploaded. Only files with these extensions are allowed: {extensions}', array(
						'{name}'=>$<?php echo $column->name;?>->name,
						'{extensions}'=>$this->formatFileType($<?php echo $fileType;?>, false),
					)));
				}
			} /* else {
				if($this->isNewRecord || (!$this->isNewRecord && $this->old_<?php echo $column->name;?>_i == ''))
					$this->addError('<?php echo $column->name;?>', Yii::t('app', '{attribute} cannot be blank.', array('{attribute}'=>$this->getAttributeLabel('<?php echo $column->name;?>'))));
			} */

<?php 	}
	}
}

foreach($tableSchema->columns as $column) {
	if(in_array($column->name, ['creation_id','modified_id','updated_id','user_id']) && $column->comment != 'trigger') {
		$beforeValidate = 1;
		if(in_array($column->name, array('creation_id','user_id'))) {
			$creationCondition = 1;
			echo "\t\t\tif(\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->{$column->name} = !Yii::\$app->user->isGuest ? Yii::\$app->user->id : null;\n";
		} else {
			if($creationCondition)
				echo "\t\t\telse\n";
			else
				echo "\t\t\tif(!\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->{$column->name} = !Yii::\$app->user->isGuest ? Yii::\$app->user->id : null;\n";
		}
	}
}

foreach($tableSchema->columns as $column) {
	$nameArray = explode('_', $column->name);
	if(in_array('ip', $nameArray)) {
		$beforeValidate = 1;?>
			$this-><?php echo $column->name;?> = $_SERVER['REMOTE_ADDR'];
<?php }
}
echo !$beforeValidate ? "\t\t\t// Create action\n" : '';?>
		}
		return true;
	}
<?php }

$avEvents = 0;
$afterValidate = 0;
if($tableType != Generator::TYPE_VIEW && ($generator->generateEvents || $avEvents)): ?>

	/**
	 * after validate attributes
	 */
	public function afterValidate()
	{
		parent::afterValidate();

		// Create action
		
		return true;
	}
<?php 
endif;

$bsEvents = 0;
$beforeSave = 0;
if($tagCondition || $uploadCondition || $serializeCondition || $i18n)
	$bsEvents = 1;
foreach($tableSchema->columns as $column) {
	if((in_array($column->type, ['date','datetime']) && $column->comment != 'trigger'))
		$bsEvents = 1;
}
if($tableType != Generator::TYPE_VIEW && ($generator->generateEvents || $bsEvents)): ?>

	/**
	 * before save attributes
	 */
	public function beforeSave($insert)
	{
<?php if($i18n) {
$beforeSave = 1;?>
		$module = strtolower(Yii::$app->controller->module->id);
		$controller = strtolower(Yii::$app->controller->id);
		$action = strtolower(Yii::$app->controller->action->id);

		$location = $this->urlTitle($module.' '.$controller);

<?php }?>
		if(parent::beforeSave($insert)) {
<?php if($uploadCondition) {
$beforeSave = 1;?>
			if(!$insert) {
<?php if($generator->uploadPath['subfolder']) {?>
				$uploadPath = join('/', [self::getUploadPath(), $this-><?php echo $primaryKey;?>]);
<?php } else {?>
				$uploadPath = self::getUploadPath();
<?php }?>
				$verwijderenPath = join('/', [self::getUploadPath(), 'verwijderen']);
				$this->createUploadDirectory(self::getUploadPath()<?php echo $generator->uploadPath['subfolder'] ? ', $this->'.$primaryKey : '';?>);

<?php foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->type == 'text' && in_array('file', $commentArray)) {?>
				$this-><?php echo $column->name;?> = UploadedFile::getInstance($this, '<?php echo $column->name;?>');
				if($this-><?php echo $column->name;?> instanceof UploadedFile && !$this-><?php echo $column->name;?>->getHasError()) {
					$fileName = time().'_'.$this-><?php echo $primaryKey;?>.'.'.strtolower($this-><?php echo $column->name;?>->getExtension()); 
					if($this-><?php echo $column->name;?>->saveAs(join('/', [$uploadPath, $fileName]))) {
						if($this->old_<?php echo $column->name;?>_i != '' && file_exists(join('/', [$uploadPath, $this->old_<?php echo $column->name;?>_i])))
							rename(join('/', [$uploadPath, $this->old_<?php echo $column->name;?>_i]), join('/', [$verwijderenPath, time().'_change_'.$this->old_<?php echo $column->name;?>_i]));
						$this-><?php echo $column->name;?> = $fileName;
					}
				} else {
					if($this-><?php echo $column->name;?> == '')
						$this-><?php echo $column->name;?> = $this->old_<?php echo $column->name;?>_i;
				}

<?php }
}?>
			}
<?php }

foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$beforeSave = 1;
		$publicAttribute = $column->name.'_i';
		$publicAttributeLocation = preg_match('/(name|title)/', $column->name) ? '_title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? '_description' : '_'.$column->name) : '_'.$column->name);?>
			if($insert || (!$insert && !$this-><?php echo $column->name;?>)) {
				$<?php echo $column->name;?> = new SourceMessage();
				$<?php echo $column->name;?>->location = $location.'<?php echo $publicAttributeLocation;?>';
				$<?php echo $column->name;?>->message = $this-><?php echo $publicAttribute;?>;
				if($<?php echo $column->name;?>->save())
					$this-><?php echo $column->name;?> = $<?php echo $column->name;?>->id;
<?php if($slugCondition && $i18n && preg_match('/(name|title)/', $column->name)) {?>

				$this->slug = $this->urlTitle($this-><?php echo $publicAttribute;?>);
<?php }?>

			} else {
				$<?php echo $column->name;?> = SourceMessage::findOne($this-><?php echo $column->name;?>);
				$<?php echo $column->name;?>->message = $this-><?php echo $publicAttribute;?>;
				$<?php echo $column->name;?>->save();
			}

<?php }
}

foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if(in_array($column->type, ['date','datetime']) && $column->comment != 'trigger') {
		$beforeSave = 1;
		echo "\t\t\t\$this->$column->name = Yii::\$app->formatter->asDate(\$this->$column->name, 'php:Y-m-d');\n";	//Y-m-d H:i:s

	} else if($column->type == 'text' && in_array('serialize', $commentArray)) {
		$beforeSave = 1;
		echo "\t\t\t\$this->$column->name = serialize(\$this->$column->name);\n";

	} else if($column->name == 'tag_id') {
		$beforeSave = 1;
		$relationName =  $generator->setRelation($column->name);
		$publicAttribute = $relationName.'_i';?>
			if($insert) {
				$<?php echo $publicAttribute;?> = $this->urlTitle($this-><?php echo $publicAttribute;?>);
				if($this-><?php echo $column->name;?> == 0) {
					$<?php echo $relationName;?> = self::find()
						->select(['<?php echo $column->name;?>', 'body'])
						->andWhere(['body' => $<?php echo $publicAttribute;?>]);
						
					if($<?php echo $relationName;?> != null)
						$this-><?php echo $column->name;?> = $<?php echo $relationName;?>-><?php echo $column->name;?>;
					else {
						$data = new OmmuTags();
						$data->body = $this-><?php echo $publicAttribute;?>;
						if($data->save())
							$this-><?php echo $column->name;?> = $data-><?php echo $column->name;?>;
					}
				}
			}
<?php }
}
echo !$beforeSave ? "\t\t\t// Create action\n" : '';?>
		}
		return true;
	}
<?php 
endif;

$asEvents = 0;
$afterSave = 0;
if($uploadCondition)
	$asEvents = 1;
if($tableType != Generator::TYPE_VIEW && ($generator->generateEvents || $asEvents)) {?>

	/**
	 * After save attributes
	 */
	public function afterSave($insert, $changedAttributes)
	{
		parent::afterSave($insert, $changedAttributes);

<?php if($uploadCondition) {
$afterSave = 1;
if($generator->uploadPath['subfolder']) {?>
		$uploadPath = join('/', [self::getUploadPath(), $this-><?php echo $primaryKey;?>]);
<?php } else {?>
		$uploadPath = self::getUploadPath();
<?php }?>
		$verwijderenPath = join('/', [self::getUploadPath(), 'verwijderen']);
		$this->createUploadDirectory(self::getUploadPath()<?php echo $generator->uploadPath['subfolder'] ? ', $this->'.$primaryKey : '';?>);

		if($insert) {
<?php foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->type == 'text'  && in_array('file', $commentArray)) {?>
			$this-><?php echo $column->name;?> = UploadedFile::getInstance($this, '<?php echo $column->name;?>');
			if($this-><?php echo $column->name;?> instanceof UploadedFile && !$this-><?php echo $column->name;?>->getHasError()) {
				$fileName = time().'_'.$this-><?php echo $primaryKey;?>.'.'.strtolower($this-><?php echo $column->name;?>->getExtension()); 
				if($this-><?php echo $column->name;?>->saveAs(join('/', [$uploadPath, $fileName])))
					self::updateAll(['<?php echo $column->name;?>' => $fileName], ['<?php echo $primaryKey;?>' => $this-><?php echo $primaryKey;?>]);
			}

<?php }
}?>
		}
<?php }
echo !$afterSave ? "\t\t// Create action\n" : '';?>
	}
<?php }

$bdEvents = 0;
$beforeDelete = 0;
if($tableType != Generator::TYPE_VIEW && ($generator->generateEvents || $bdEvents)): ?>

	/**
	 * Before delete attributes
	 */
	public function beforeDelete()
	{
		if(parent::beforeDelete()) {
			// Create action
		}
		return true;
	}
<?php 
endif;

$adEvents = 0;
$afterDelete = 0;
if($uploadCondition)
	$adEvents = 1;
if($tableType != Generator::TYPE_VIEW && ($generator->generateEvents || $adEvents)) {?>

	/**
	 * After delete attributes
	 */
	public function afterDelete()
	{
		parent::afterDelete();

<?php if($uploadCondition) {
	$afterDelete = 1;
if($generator->uploadPath['subfolder']) {?>
		$uploadPath = join('/', [self::getUploadPath(), $this-><?php echo $primaryKey;?>]);
<?php } else {?>
		$uploadPath = self::getUploadPath();
<?php }?>
		$verwijderenPath = join('/', [self::getUploadPath(), 'verwijderen']);

<?php foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->type == 'text' && in_array('file', $commentArray)) {?>
		if($this-><?php echo $column->name;?> != '' && file_exists(join('/', [$uploadPath, $this-><?php echo $column->name;?>])))
			rename(join('/', [$uploadPath, $this-><?php echo $column->name;?>]), join('/', [$verwijderenPath, time().'_deleted_'.$this-><?php echo $column->name;?>]));

<?php 	}
	}
}
echo !$afterDelete ? "\t\t// Create action\n" : '';?>
	}
<?php }?>
}
