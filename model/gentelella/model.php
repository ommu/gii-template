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
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

/**
 * Variable
 */
use app\libraries\gii\model\Generator;
use yii\helpers\Inflector;

$patternClass = $patternLabel =[];
$patternClass[0] = '(Ommu)';
$patternClass[1] = '(Swt)';

$patternLabel[0] = '(ID)';
$patternLabel[1] = '(Search)';

/**
 * Condition
 */
$publishCondition = 0;
$slugCondition = 0;
$userCondition = 0;
$tagCondition = 0;
$uploadCondition = 0;
$i18n = 0;

$arrayRelations = [];
$arrayInputPublicVariable = [];
$arraySearchPublicVariable = [];
$arrayAttributeName = [];

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
 * @link <?php echo $yaml['link']."\n";?>
 *
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
 * The followings are the available columns in table "<?= $generator->generateTableName($tableName) ?>":
<?php foreach ($tableSchema->columns as $column):
if(!($column->name[0] == '_')): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endif;
endforeach; ?>
<?php if (!empty($relations)): ?>
 *
 * The followings are the available model relations:
<?php 
//echo '<pre>';
//print_r($relations);
foreach ($relations as $name => $relation):
$relationModel = preg_replace($patternClass, '', $relation[1]);
//echo $name."\n";
//echo $relation[1]."\n";
$arrayRelations[] = $relationName = ($relation[2] ? lcfirst($generator->setRelationName($name)) : lcfirst(Inflector::singularize($generator->setRelationName($relation[1]))));?>
 * @property <?= $relationModel . ($relation[2] ? '[]' : '') . ' $' . $relationName ."\n" ?>
<?php endforeach;
foreach ($tableSchema->columns as $column):
	if($column->dbType == 'tinyint(1)' && in_array($column->name, ['publish','headline']))
		$publishCondition = 1;
	elseif($column->name == 'slug') 
		$slugCondition = 1;
	elseif(in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id'])) {
		$relationNameArray = explode('_', $column->name);
		$relationName = lcfirst($relationNameArray[0]);
		if(!in_array($relationName, $arrayRelations)) {
			if(in_array($column->name, ['creation_id','modified_id','user_id','updated_id']))
				echo " * @property Users \${$relationName}\n";
			else if($column->name == 'tag_id') 
				echo " * @property CoreTags \${$relationName}\n";
			$arrayRelations[] = $relationName;
		}
		if(in_array($column->name, ['creation_id','modified_id','user_id','updated_id']))
			$userCondition = 1;
		else if($column->name == 'tag_id') 
			$tagCondition = 1;
	} else {
		if($column->type == 'text' && $column->comment == 'file') 
			$uploadCondition = 1;
		else {
			$commentArray = explode(',', $column->comment);
			if(in_array('trigger[delete]', $commentArray))
				$i18n = 1;
		}
	}
endforeach;
endif; ?>
 *
 */

namespace <?= $generator->ns ?>;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
<?php 
echo $uploadCondition ? "use ".ltrim('yii\web\UploadedFile', '\\').";\n" : '';
echo $slugCondition ? "use ".ltrim('yii\behaviors\SluggableBehavior', '\\').";\n" : '';
echo $publishCondition ? "use ".ltrim('app\libraries\grid\GridView', '\\').";\n" : '';
echo $i18n ? "use ".ltrim('app\components\Utility', '\\').";\n" : '';
echo $tagCondition ? "use ".ltrim('app\models\CoreTags', '\\').";\n" : '';
echo $i18n ? "use ".ltrim('app\models\SourceMessage', '\\').";\n" : '';
echo $userCondition ? "use ".ltrim('app\coremodules\user\models\Users', '\\').";\n" : '';
?>

class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
	public $gridForbiddenColumn = [];
<?php 
//echo '<pre>';
//print_r($tableSchema->columns);
foreach ($tableSchema->columns as $column): 
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$inputPublicVariable = lcfirst(Inflector::singularize($column->name)).'_i';
		if(!in_array($inputPublicVariable, $arrayInputPublicVariable))
			$arrayInputPublicVariable[] = $inputPublicVariable;
	}
endforeach;
foreach ($tableSchema->columns as $column):
	if(in_array($column->name, array('tag_id'))) {
		$relationNameArray = explode('_', $column->name);
		$inputPublicVariable = lcfirst(Inflector::singularize($relationNameArray[0])).'_i';
		if(!in_array($inputPublicVariable, $arrayInputPublicVariable))
			$arrayInputPublicVariable[] = $inputPublicVariable;
	}
endforeach;
foreach ($tableSchema->columns as $column):
	if(!(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) && $column->type == 'text' && $column->comment == 'file') {
		$inputPublicVariable = 'old_'.lcfirst(Inflector::singularize($column->name)).'_i';
		if(!in_array($inputPublicVariable, $arrayInputPublicVariable))
			$arrayInputPublicVariable[] = $inputPublicVariable;
	}
endforeach;

foreach ($tableSchema->columns as $column): 
if(!empty($foreignKeys) && in_array($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
	$relationTableName = array_search($column->name, $foreignKeys);
	$relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
	$relationName = lcfirst(Inflector::singularize($generator->setRelationName($relationModelName)));
	$searchPublicVariable = $relationName.'_search';
	if(!in_array($searchPublicVariable, $arraySearchPublicVariable))
		$arraySearchPublicVariable[] = $searchPublicVariable;
endif;
endforeach;
foreach ($tableSchema->columns as $column): 
if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
	$relationNameArray = explode('_', $column->name);
	$searchPublicVariable = lcfirst(Inflector::singularize($relationNameArray[0])).'_search';
	if(!in_array($searchPublicVariable, $arraySearchPublicVariable))
		$arraySearchPublicVariable[] = $searchPublicVariable;
endif;
endforeach;

if(!empty($arrayInputPublicVariable)) {
	foreach ($arrayInputPublicVariable as $val):
		echo "\tpublic $$val;\n";
	endforeach;
}
if(!empty($arraySearchPublicVariable)) {
	echo "\n\t// Variable Search\n"; 
foreach ($arraySearchPublicVariable as $val):
	echo "\tpublic $$val;\n";
endforeach;
}?>

	/**
	 * @return string the associated database table name
	 */
	public static function tableName()
	{
		return '<?= $generator->generateTableName($tableName) ?>';
	}
<?php
if($tableType == Generator::TYPE_VIEW):
?>

	/**
	 * @return string the primarykey column
	 */
	public static function primaryKey() {
		return ['<?=$viewPrimaryKey?>'];
	}
<?php
endif;
?>
<?php if ($generator->db !== 'db'): ?>

	/**
	 * @return \yii\db\Connection the database connection used by this AR class.
	 */
	public static function getDb()
	{
		return Yii::$app->get('<?= $generator->db ?>');
	}
<?php endif; ?>
<?php if ($slugCondition):
$getNameAttribute = $generator->getNameAttribute();?>

	/**
	 * behaviors model class.
	 */
	public function behaviors() {
		return [
			[
				'class'	 => SluggableBehavior::className(),
				'attribute' => '<?php echo $i18n && preg_match('/(name|title)/', $getNameAttribute) ? 'title.message' : $getNameAttribute;?>',
				'immutable' => true,
				'ensureUnique' => true,
			],
		];
	}
<?php endif; ?>

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        return [<?= "\n            " . implode(",\n            ", preg_replace($patternClass, '', $rules)) . ",\n        " ?>];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return [
<?php 
//echo '<pre>';
//print_r($labels);
foreach ($labels as $name => $label):
if(count(explode(' ', $label)) > 1)
	$label = trim(preg_replace($patternLabel, '', $label));
	if(!($name[0] == '_')) {
		$arrayAttributeName[] = $label;
		echo "\t\t\t'$name' => " . $generator->generateString($label) . ",\n";
	}
endforeach;
//echo '<pre>';
//print_r($foreignKeys);
//print_r($tableSchema->columns);
foreach ($tableSchema->columns as $column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$relationName = lcfirst(Inflector::singularize($column->name));
		$attributeName = $relationName.'_i';
		if(!in_array($attributeName, $arrayAttributeName)) {
			$arrayAttributeName[] = $attributeName;
			$attributeLabels = implode(' ', array_map('ucfirst', explode('_', $relationName)));
			if(count(explode(' ', $attributeLabels)) > 1)
				$attributeLabels = trim(preg_replace($patternLabel, '', $attributeLabels));
			echo "\t\t\t'$attributeName' => " . $generator->generateString($attributeLabels) . ",\n";
		}
	}
endforeach;
foreach ($tableSchema->columns as $column):
	if(in_array($column->name, array('tag_id'))) {
		$relationArray = explode('_', $column->name);
		$relationName = lcfirst(Inflector::singularize($relationArray[0]));
		$attributeName = $relationName.'_i';
		if(!in_array($attributeName, $arrayAttributeName)) {
			$arrayAttributeName[] = $attributeName;
			$attributeLabels = implode(' ', array_map('ucfirst', explode('_', $relationName)));
			if(count(explode(' ', $attributeLabels)) > 1)
				$attributeLabels = trim(preg_replace($patternLabel, '', $attributeLabels));
			echo "\t\t\t'$attributeName' => " . $generator->generateString($attributeLabels) . ",\n";
		}
	}
endforeach;
foreach ($tableSchema->columns as $column):
	if($column->type == 'text' && $column->comment == 'file') {
		$relationName = lcfirst(Inflector::singularize($column->name));
		$attributeName = 'old_'.$relationName.'_i';
		if(!in_array($attributeName, $arrayAttributeName)) {
			$arrayAttributeName[] = $attributeName;
			$attributeLabels = implode(' ', array_map('ucfirst', explode('_', 'old_'.$relationName)));
			if(count(explode(' ', $attributeLabels)) > 1)
				$attributeLabels = trim(preg_replace($patternLabel, '', $attributeLabels));
			echo "\t\t\t'$attributeName' => " . $generator->generateString($attributeLabels) . ",\n";
		}
	}
endforeach;
foreach ($tableSchema->columns as $column):
	if(!empty($foreignKeys) && in_array($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
		$relationTableName = array_search($column->name, $foreignKeys);
		//echo $relationTableName."\n";
		$relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
		//echo $relationModelName."\n";
		$relationName = lcfirst(Inflector::singularize($generator->setRelationName($relationModelName)));
		//echo $relationName."\n";
		$attributeName = $relationName.'_search';
		if(!in_array($attributeName, $arrayAttributeName)) {
			$arrayAttributeName[] = $attributeName;
			$attributeLabels = implode(' ', array_map('ucfirst', explode('_', $attributeName)));
			if(count(explode(' ', $attributeLabels)) > 1)
				$attributeLabels = trim(preg_replace($patternLabel, '', $attributeLabels));
			echo "\t\t\t'$attributeName' => " . $generator->generateString($attributeLabels) . ",\n";
		}
	endif;
endforeach;
foreach ($tableSchema->columns as $column):
	if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
		$relationArray = explode('_', $column->name);
		$attributeName = lcfirst(Inflector::singularize($relationArray[0])).'_search';
		if(!in_array($attributeName, $arrayAttributeName)) {
			$arrayAttributeName[] = $attributeName;
			$attributeLabels = implode(' ', array_map('ucfirst', explode('_', $attributeName)));
			if(count(explode(' ', $attributeLabels)) > 1)
				$attributeLabels = trim(preg_replace($patternLabel, '', $attributeLabels));
			echo "\t\t\t'$attributeName' => " . $generator->generateString($attributeLabels) . ",\n";
		}
	endif;
endforeach; ?>
		];
	}
<?php 
//echo '<pre>';
//print_r($relations);
$arrayRelations = [];
foreach ($relations as $name => $relation):
	$relationName = $relation[2] ? $name : Inflector::singularize($relation[1]);
	$arrayRelations[] = $relationName = $generator->setRelationName($relationName);
	//echo $relationName; ?>

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function get<?php echo $relationName;?>()
	{
		<?= preg_replace($patternClass, '', $relation[0]) . "\n" ?>
	}
<?php endforeach;
//echo '<pre>';
//print_r($tableSchema->columns);
if($i18n):
	foreach ($tableSchema->columns as $column):
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)) {
			$relationName = preg_match('/(name|title)/', $column->name) ? 'title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? 'description' : $name.'Rltn') : $column->name.'Rltn');
			$relationName = ucfirst($relationName);
			if(!in_array($relationName, $arrayRelations)) {
				$arrayRelations[] = $relationName;?>
	
	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function get<?php echo $relationName;?>()
	{
		return $this->hasOne(SourceMessage::className(), ['id' => '<?php echo $column->name;?>']);
	}
	<?php	}
		}
	endforeach;
endif;
foreach ($tableSchema->columns as $column):
	if(!$column->isPrimaryKey && in_array($column->name, array('creation_id','modified_id','user_id','updated_id','tag_id'))):
		$relationNameArray = explode('_', $column->name);
		$relationName = lcfirst(Inflector::singularize($relationNameArray[0])); ?>

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function get<?= ucfirst($relationName) ?>()
	{
		return $this->hasOne(<?php echo $column->name == 'tag_id' ? 'CoreTags' : 'Users';?>::className(), ['<?php echo $column->name == 'tag_id' ? 'tag_id' : 'user_id';?>' => '<?php echo $column->name;?>']);
	}
<?php endif;
endforeach; ?>
<?php if ($queryClassName): ?>
<?php
	$queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
	echo "\n";
?>
	/**
	 * @inheritdoc
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
//echo '<pre>';
//print_r($tableSchema);
foreach ($tableSchema->columns as $column):
if(!$column->isPrimaryKey || !$column->autoIncrement):
//if($column->dbType != 'tinyint(1)' && !in_array($column->name, ['publish','headline'])):
if($column->dbType != 'tinyint(1)'):
if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
	$relationNameArray = explode('_', $column->name);
	$relationName = lcfirst($relationNameArray[0]);
	$relationSearchName = $relationName.'_search'; ?>
		if(!Yii::app()->getRequest()->getParam('<?php echo $relationName;?>')) {
			$this->templateColumns['<?php echo $relationSearchName;?>'] = [
				'attribute' => '<?php echo $relationSearchName;?>',
				'value' => function($model, $key, $index, $column) {
					return isset($model-><?php echo $relationName;?>->displayname) ? $model-><?php echo $relationName;?>->displayname : '-';
				},
			];
		}
<?php elseif(in_array($column->dbType, array('timestamp','datetime','date'))):?>
		$this->templateColumns['<?php echo $column->name;?>'] = [
			'attribute' => '<?php echo $column->name;?>',
			'filter'	=> \yii\jui\DatePicker::widget([
				'dateFormat' => 'yyyy-MM-dd',
				'attribute' => '<?php echo $column->name;?>',
				'model'  => $this,
			]),
			'value' => function($model, $key, $index, $column) {
				if(!in_array($model-><?php echo $column->name;?>, <?php echo $column->dbType == 'date' ? "\n\t\t\t\t\t['0000-00-00','1970-01-01','-0001-11-30']" : "\n\t\t\t\t\t['0000-00-00 00:00:00','1970-01-01 00:00:00','-0001-11-30 00:00:00']";?>)) {
					return Yii::$app->formatter->format($model-><?php echo $column->name;?>, '<?php echo $column->dbType == 'date' ? $column->dbType : 'date';?>'/*datetime*/);
				}else {
					return '-';
				}
			},
			'format'	=> 'html',
		];
<?php else:
if(!empty($foreignKeys) && in_array($column->name, $foreignKeys)):
	$relationTableName = array_search($column->name, $foreignKeys);
	$relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
	$relationAttributeName = $generator->getNameAttribute($relationTableName);
	$relationName = lcfirst(Inflector::singularize($generator->setRelationName($relationModelName)));
	$relationSearchName = $relationName.'_search';?>
		if(!isset($_GET['<?php echo $relationName;?>'])) {
			$this->templateColumns['<?php echo $relationSearchName;?>'] = [
				'attribute' => '<?php echo $relationSearchName;?>',
				'value' => function($model, $key, $index, $column) {
					return $model-><?php echo $relationName;?>-><?php echo $relationAttributeName;?>;
				},
			];
		}
<?php else:?>
		$this->templateColumns['<?php echo $column->name;?>'] = '<?php echo $column->name;?>';
<?php endif;
endif;
endif;
endif;
endforeach;
foreach ($tableSchema->columns as $column):
if(!$column->isPrimaryKey || !$column->autoIncrement):
if($column->dbType == 'tinyint(1)' && !in_array($column->name, ['publish','headline'])):?>
		$this->templateColumns['<?php echo $column->name;?>'] = [
			'attribute' => '<?php echo $column->name;?>',
			'value' => function($model, $key, $index, $column) {
				return $model-><?php echo $column->name;?>;
			},
			'contentOptions' => ['class'=>'center'],
		];
<?php endif;
endif;
endforeach;
foreach ($tableSchema->columns as $column):
if(!$column->isPrimaryKey || !$column->autoIncrement):
if($column->dbType == 'tinyint(1)' && $column->name == 'headline'):?>
		$this->templateColumns['<?php echo $column->name;?>'] = [
			'attribute' => '<?php echo $column->name;?>',
			'filter' => GridView::getFilterYesNo(),
			'value' => function($model, $key, $index, $column) {
				$url = Url::to(['headline', 'id' => $model->primaryKey]);
				return GridView::getHeadline($url, $model-><?php echo $column->name;?>);
			},
			'contentOptions' => ['class'=>'center'],
			'format'	=> 'raw',
		];
<?php endif;
endif;
endforeach;
foreach ($tableSchema->columns as $column):
if(!$column->isPrimaryKey || !$column->autoIncrement):
if($column->dbType == 'tinyint(1)' && $column->name == 'publish'):?>
		if(!isset($_GET['trash'])) {
			$this->templateColumns['<?php echo $column->name;?>'] = [
				'attribute' => '<?php echo $column->name;?>',
				'filter' => GridView::getFilterYesNo(),
				'value' => function($model, $key, $index, $column) {
					$url = Url::to(['publish', 'id' => $model->primaryKey]);
					return GridView::getPublish($url, $model-><?php echo $column->name;?>);
				},
				'contentOptions' => ['class'=>'center'],
				'format'	=> 'raw',
			];
		}
<?php endif;
endif;
endforeach; ?>
<?php /*
		if(count($this->defaultColumns) == 0) {
foreach ($tableSchema->columns as $column):
	if(!$column->isPrimaryKey) {
		if(in_array($column->dbType, array('timestamp','datetime','date'))) {?>
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

<?php
//echo '<pre>';
//print_r($tableSchema->columns);
foreach($tableSchema->columns as $name=>$column):
	if($column->isPrimaryKey && (($column->type == 'tinyint' && $column->size == '3') || ($column->type == 'smallint' && in_array($column->size, array('3','5'))))):
		$functionName = $generator->setRelationName($className);
		$attributeName = $generator->getNameAttribute($generator->generateTableName($tableName));?>
	/**
	 * function get<?= $functionName."\n"; ?>
	 */
	public static function get<?= $functionName ?>(<?php echo $publishCondition ? '$publish = null' : '';?>) 
	{
		$items = [];
		$model = self::find();
<?php if($publishCondition) {?>
		if($publish != null)
			$model = $model->andWhere(['publish' => $publish]);
<?php }?>
		$model = $model->orderBy('<?php echo $attributeName;?> ASC')->all();

		if($model !== null) {
			foreach($model as $val) {
				$items[$val-><?php echo $column->name;?>] = $val-><?php echo $attributeName;?>;
			}
		}
		
		return $items;
	}
<?php endif;
endforeach;
if($uploadCondition):?>

	/**
	 * @param returnAlias set true jika ingin kembaliannya path alias atau false jika ingin string
	 * relative path. default true.
	 */
	public static function getPagePath($returnAlias=true) 
	{
		return ($returnAlias ? Yii::getAlias('@webroot/public/main') : 'public/main');
	}
<?php endif;
if($i18n || $uploadCondition || $tagCondition):?>

	/**
	* after find attributes
	*/
	public function afterFind() 
	{
<?php foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$publicAttribute = $column->name.'_i';
		$publicAttributeRelation = preg_match('/(name|title)/', $column->name) ? 'title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? 'description' : $column->name.'Rltn') : $column->name.'Rltn');
		echo "\t\t\$this->$publicAttribute = \$this->{$publicAttributeRelation}->message;\n";
	}
}
foreach ($tableSchema->columns as $column) {
	if(in_array($column->name, array('tag_id'))) {
		$relationNameArray = explode('_', $column->name);
		$relationName = lcfirst(Inflector::singularize($relationNameArray[0]));
		$publicAttribute = $relationName.'_i';
		echo "\t\t\$this->$publicAttribute = \$this->{$relationName}->body;\n";
	} else {
		if($column->type == 'text' && $column->comment == 'file') {
			$inputPublicVariable = 'old_'.lcfirst(Inflector::singularize($column->name)).'_i';
			echo "\t\t\$this->$inputPublicVariable = \$this->$column->name;\n";
		}
	}
}?>
	}
<?php endif;

$bsEvents = 0;
foreach($tableSchema->columns as $column)
{
	if($uploadCondition || in_array($column->name, array('creation_id','modified_id','user_id','updated_id')))
		$bsEvents = 1;
}
if($generator->generateEvents || $bsEvents): ?>

	/**
	 * before validate attributes
	 */
	public function beforeValidate() 
	{
		if(parent::beforeValidate()) {
<?php
$creationCondition = 0;
foreach($tableSchema->columns as $column):
	if(in_array($column->name, array('creation_id','modified_id','updated_id')) && $column->comment != 'trigger'):
		if($column->name == 'creation_id') {
			$creationCondition = 1;
			echo "\t\t\tif(\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->{$column->name} = !Yii::\$app->user->isGuest ? Yii::\$app->user->id : null;\n";

		} else {
			if($creationCondition) {
				echo "\t\t\telse\n";
			}else
				echo "\t\t\tif(!\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->{$column->name} = !Yii::\$app->user->isGuest ? Yii::\$app->user->id : null;\n";
		}
	endif;
endforeach;
?>
		}
		return true;
	}
<?php 
endif;

$bsEvents = 0;
foreach($tableSchema->columns as $column)
{
	if($i18n || (in_array($column->type, ['date','datetime']) && $column->comment != 'trigger')  || ($column->type == 'text' && $column->comment == 'serialize')|| in_array($column->name, ['tag_id']))
		$bsEvents = 1;
}
if($generator->generateEvents || $bsEvents): ?>

	/**
	 * before save attributes
	 */
	public function beforeSave($insert) 
	{
<?php if($i18n) {?>
		$module = strtolower(Yii::$app->controller->module->id);
		$controller = strtolower(Yii::$app->controller->id);
		$action = strtolower(Yii::$app->controller->action->id);

		$location = Utility::getUrlTitle($module.' '.$controller);
		
<?php }?>
		if(parent::beforeSave($insert)) {
<?php 
foreach($tableSchema->columns as $column):
	if(in_array($column->type, array('date','datetime')) && $column->comment != 'trigger')
		echo "\t\t\t\$this->$column->name = date('Y-m-d', strtotime(\$this->$column->name));\n";	//Y-m-d H:i:s

	else if($column->type == 'text' && $column->comment == 'serialize')
		echo "\t\t\t\$this->$column->name = serialize(\$this->$column->name);\n";

	else if($column->name == 'tag_id') {
		$relationArray = explode('_', $column->name);
		$relationName =  lcfirst(Inflector::singularize($relationArray[0]));
		$publicAttribute = $relationName.'_i';
	}
endforeach;
foreach($tableSchema->columns as $column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$publicAttribute = $column->name.'_i';
		$publicAttributeLocation = preg_match('/(name|title)/', $column->name) ? '_title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? '_description' : '_'.$column->name) : '_'.$column->name);?>

			if($this->isNewRecord || (!$this->isNewRecord && !$this-><?php echo $column->name;?>)) {
				$<?php echo $column->name;?> = new SourceMessage();
				$<?php echo $column->name;?>->location = $location.'<?php echo $publicAttributeLocation;?>';
				$<?php echo $column->name;?>->message = $this-><?php echo $publicAttribute;?>;
				if($<?php echo $column->name;?>->save())
					$this-><?php echo $column->name;?> = $<?php echo $column->name;?>->id;
				
			} else {
				$<?php echo $column->name;?> = SourceMessage::findOne($this->name);
				$<?php echo $column->name;?>->message = $this-><?php echo $publicAttribute;?>;
				$<?php echo $column->name;?>->save();
			}
<?php }
endforeach;?>
			// Create action
		}
		return true;
	}
<?php 
endif;

$bsEvents = 0;
if($generator->generateEvents || $bsEvents): ?>

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
if($generator->generateEvents || $bsEvents): ?>

	/**
	 * After save attributes
	 */
	public function afterSave($insert, $changedAttributes) 
	{
		parent::afterSave($insert, $changedAttributes);
		// Create action
	}
<?php 
endif;

$bsEvents = 0;
if($generator->generateEvents || $bsEvents): ?>

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

$bsEvents = 0;
if($generator->generateEvents || $bsEvents): ?>

	/**
	 * After delete attributes
	 */
	public function afterDelete() 
	{
		parent::afterDelete();
		// Create action
	}
<?php endif; ?>
}
