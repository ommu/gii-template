<?php
/**
 * This is the template for generating CRUD search class of the specified model.
 */

use yii\helpers\StringHelper;
use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
	$modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

$tableSchema = $generator->tableSchema;
$tableColumns = $tableSchema->columns;
//echo '<pre>';
//print_r($generator);
//print_r($tableSchema);

$patternClass = $patternLabel = array();
$patternClass[0] = '(Ommu)';
$patternClass[1] = '(Swt)';

/**
 * Condition
 */
$userCondition = 0;
foreach ($tableSchema->columns as $column) {
	if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id')))
		$userCondition = 1;
}

$relationModel = [];
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
foreach ($tableSchema->columns as $column): 
if(!empty($foreignKeys) && in_array($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))) {
	$relationTableName = array_search($column->name, $foreignKeys);
	$relationModel[] = $relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
}
endforeach;

$publicVariable = array();
$relationVariable = array();
foreach ($tableSchema->columns as $column): 
if(!empty($foreignKeys) && in_array($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
	$relationTableName = array_search($column->name, $foreignKeys);
	$relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
	$relationVariable[] = $relationName = lcfirst(Inflector::singularize($generator->setRelationName($relationModelName)));
	$publicVariable[] = $relationSearchName = $relationName.'_search';
endif;
endforeach;
foreach ($tableSchema->columns as $column): 
if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
	$relationNameArray = explode('_', $column->name);
	$relationVariable[] = $relationName = lcfirst($relationNameArray[0]);
	$publicVariable[] = $relationSearchName = $relationName.'_search';
endif;
endforeach;

/**
 * foreignKeys Column
 */
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?= $searchModelClass."\n" ?>
 * version: 0.0.1
 *
 * <?= $searchModelClass ?> represents the model behind the search form about `<?= $generator->modelClass ?>`.
 *
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @link <?php echo $yaml['link']."\n";?>
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @contact <?php echo $yaml['contact']."\n";?>
 *
 */

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;
<?php if(!empty($relationModel)) {
foreach ($relationModel as $val):
	$modelClassArray = explode('\\', $generator->modelClass);
	// Only variables should be passed by reference
	$arrKeys = array_keys($modelClassArray);
	$modelClassArray[array_pop($arrKeys)] = $val;
echo '//use '.implode('\\', $modelClassArray).";\n";
endforeach;
}
echo $userCondition ? "//use ".ltrim('app\coremodules\user\models\Users', '\\').";\n" : '';?>

class <?= $searchModelClass ?> extends <?= (isset($modelAlias) ? $modelAlias : $modelClass). "\n"; ?>
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
<?php
foreach($rules as $rule):
if(count($rule->columns)):
	// Jika public var ada merge ke safe rule columns
	if($rule->ruleType == 'safe' && !empty($publicVariable)) {
		$rule->columns = \yii\helpers\ArrayHelper::merge($rule->columns, $publicVariable);
	}
?>
<?php echo "\t\t\t[['".implode("', '", $rule->columns)?>'], '<?=$rule->ruleType?>'],
<?php endif;
endforeach;?>
		];
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	/**
	 * Tambahkan fungsi beforeValidate ini pada model search untuk menumpuk validasi pd model induk. 
	 * dan "jangan" tambahkan parent::beforeValidate, cukup "return true" saja.
	 * maka validasi yg akan dipakai hanya pd model ini, semua script yg ditaruh di beforeValidate pada model induk
	 * tidak akan dijalankan.
	 */
	public function beforeValidate() {
		return true;
	}

	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{
		$query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find()->alias('t');
<?php 
//echo '<pre>';
//print_r($relationVariable);
if(!empty($relationVariable)):
foreach ($relationVariable as $key => $val):
	$relationVariable[$key] = $val.' '.$val;
endforeach;
echo "\t\t";?>$query->joinWith([<?php echo "'" .implode("', '", $relationVariable). "'";?>]);
<?php endif;?>

		// add conditions that should always apply here
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$attributes = array_keys($this->getTableSchema()->columns);
<?php 
if(!empty($publicVariable)):
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
foreach ($tableSchema->columns as $column): 
	if(!empty($foreignKeys) && in_array($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
		$relationTableName = array_search($column->name, $foreignKeys);
		$relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
		$relationAttributeName = $generator->getNameAttribute($relationTableName);
		//echo $relationModelName.' xxx '.$relationTableName;
		$relationName = lcfirst(Inflector::singularize($generator->setRelationName($relationModelName)));
		$relationSearchName = $relationName.'_search';?>
		$attributes['<?php echo $relationSearchName;?>'] = [
			'asc' => ['<?php echo $relationName;?>.<?php echo $relationAttributeName;?>' => SORT_ASC],
			'desc' => ['<?php echo $relationName;?>.<?php echo $relationAttributeName;?>' => SORT_DESC],
		];
<?php endif;
endforeach;
foreach ($tableSchema->columns as $column): 
	if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
		$relationNameArray = explode('_', $column->name);
		$relationName = lcfirst($relationNameArray[0]);
		$relationSearchName = $relationName.'_search'; ?>
		$attributes['<?php echo $relationSearchName;?>'] = [
			'asc' => ['<?php echo $relationName;?>.displayname' => SORT_ASC],
			'desc' => ['<?php echo $relationName;?>.displayname' => SORT_DESC],
		];
<?php endif;
endforeach;
endif;?>
		$dataProvider->setSort([
			'attributes' => $attributes,
			'defaultOrder' => ['<?php echo $tableSchema->primaryKey[0]?>' => SORT_DESC],
		]);

		$this->load($params);

		if(!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		// grid filtering conditions
		<?php echo implode("\n\t\t", $searchConditions); ?>

		return $dataProvider;
	}
}
