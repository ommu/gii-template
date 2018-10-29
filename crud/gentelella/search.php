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

$patternClass = $patternLabel = array();
$patternClass[0] = '(Ommu)';
$patternClass[1] = '(Swt)';

if(!empty($tableSchema->primaryKey))
	$primaryKey = $tableSchema->primaryKey[0];
else
	$primaryKey = key($tableSchema->columns);

$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);

$arrayRelations = [];
$arrayPublicVariable = [];

foreach ($tableSchema->columns as $column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$relationName = preg_match('/(name|title)/', $column->name) ? 'title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? 'description' : $name.'Rltn') : $column->name.'Rltn');
		$publicVariable = $column->name.'_i';
		if(!in_array($publicVariable, $arrayPublicVariable)) {
			$arrayRelations[$relationName] = $relationName;
			$arrayPublicVariable[] = $publicVariable;
		}
	}
endforeach;
foreach ($tableSchema->columns as $column):
	if(in_array($column->name, ['tag_id'])) {
		$relationName = $generator->setRelation($column->name);
		$publicVariable = $relationName.'_i';
		if(!in_array($publicVariable, $arrayPublicVariable)) {
			$arrayRelations[$relationName] = $relationName;
			$arrayPublicVariable[] = $publicVariable;
		}
	}
endforeach;
foreach ($tableSchema->columns as $column):
if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id'])):
	$relationTableName = trim($foreignKeys[$column->name]);
	$relationName = $generator->setRelation($column->name);
	$publicVariable = $relationName.'_search';
	if(!in_array($publicVariable, $arrayPublicVariable)) {
		$arrayRelations[$relationName] = $generator->getName2ndRelation($relationName, $generator->getNameAttribute($relationTableName,'.'));
		$arrayPublicVariable[] = $publicVariable;
	}
endif;
endforeach;
foreach ($tableSchema->columns as $column):
if(in_array($column->name, ['creation_id','modified_id','user_id','updated_id'])):
	$relationName = $generator->setRelation($column->name);
	$publicVariable = $relationName.'_search';
	if(!in_array($publicVariable, $arrayPublicVariable)) {
		$arrayRelations[$relationName] = $relationName;
		$arrayPublicVariable[] = $publicVariable;
	}
endif;
endforeach;

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?= $searchModelClass."\n" ?>
 *
 * <?= $searchModelClass ?> represents the model behind the search form about `<?= $generator->modelClass ?>`.
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
 */

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;

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
if(!empty($rule->columns)):
	// Jika public var ada merge ke safe rule columns
	if($rule->ruleType == 'safe' && !empty($arrayPublicVariable))
		$rule->columns = \yii\helpers\ArrayHelper::merge($rule->columns, $arrayPublicVariable);
		
	echo "\t\t\t[['".implode("', '", $rule->columns)?>'], '<?=$rule->ruleType?>'],
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
if(!empty($arrayRelations)):
foreach ($arrayRelations as $key => $val):
	$relations[] = $val.' '.$key;
endforeach;?>
		$query->joinWith([<?php echo "\n\t\t\t'" .implode("', \n\t\t\t'", $relations). "'\n\t\t";?>]);
<?php endif;?>

		// add conditions that should always apply here
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$attributes = array_keys($this->getTableSchema()->columns);
<?php 
if(!empty($arrayPublicVariable)):
$arrayPublicVariable = [];
foreach ($tableSchema->columns as $column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)):
		$relationName = preg_match('/(name|title)/', $column->name) ? 'title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? 'description' : $name.'Rltn') : $column->name.'Rltn');
		$publicVariable = $column->name.'_i';
		if(!in_array($publicVariable, $arrayPublicVariable)) {
			$arrayPublicVariable[] = $publicVariable;?>
		$attributes['<?php echo $publicVariable;?>'] = [
			'asc' => ['<?php echo $relationName;?>.message' => SORT_ASC],
			'desc' => ['<?php echo $relationName;?>.message' => SORT_DESC],
		];
<?php	}
	endif;
endforeach;
foreach ($tableSchema->columns as $column): 
	if(in_array($column->name, ['tag_id'])):
		$relationName = $generator->setRelation($column->name);
		$publicVariable = $relationName.'_i';
		if(!in_array($publicVariable, $arrayPublicVariable)) {
			$arrayPublicVariable[] = $publicVariable;?>
		$attributes['<?php echo $publicVariable;?>'] = [
			'asc' => ['<?php echo $relationName;?>.body' => SORT_ASC],
			'desc' => ['<?php echo $relationName;?>.body' => SORT_DESC],
		];
<?php 	}
	endif;
endforeach;
foreach ($tableSchema->columns as $column): 
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id','tag_id'))):
		$relationTableName = trim($foreignKeys[$column->name]);
		$relationName = $generator->setRelation($column->name);
		$relationAttributeName = $generator->getName2ndAttribute($relationName, $generator->getNameAttribute($relationTableName, '.'));
		if(trim($foreignKeys[$column->name]) == 'ommu_users')
			$relationAttributeName = 'displayname';
		$publicVariable = $relationName.'_search';
		if(!in_array($publicVariable, $arrayPublicVariable)) {
			$arrayPublicVariable[] = $publicVariable;?>
		$attributes['<?php echo $publicVariable;?>'] = [
			'asc' => ['<?php echo $relationName;?>.<?php echo $relationAttributeName;?>' => SORT_ASC],
			'desc' => ['<?php echo $relationName;?>.<?php echo $relationAttributeName;?>' => SORT_DESC],
		];
<?php 	}
	endif;
endforeach;
foreach ($tableSchema->columns as $column): 
	if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
		$relationName = $generator->setRelation($column->name);
		$publicVariable = $relationName.'_search';
		if(!in_array($publicVariable, $arrayPublicVariable)) {
			$arrayPublicVariable[] = $publicVariable;?>
		$attributes['<?php echo $publicVariable;?>'] = [
			'asc' => ['<?php echo $relationName;?>.displayname' => SORT_ASC],
			'desc' => ['<?php echo $relationName;?>.displayname' => SORT_DESC],
		];
<?php 	}
	endif;
endforeach;
endif;?>
		$dataProvider->setSort([
			'attributes' => $attributes,
			'defaultOrder' => ['<?php echo $primaryKey?>' => SORT_DESC],
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
