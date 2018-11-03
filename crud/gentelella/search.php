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
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);

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
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
<?php
$arrayRelations = [];
$inputRuleVariables = [];
$inputSearchVariables = [];

foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$relationName = $generator->i18nRelation($column->name);
		$inputRuleVariable = $column->name.'_i';
		$arrayRelations[$relationName] = $relationName;
		if(!in_array($inputRuleVariable, $inputRuleVariables)) {
			$inputRuleVariables[] = $inputRuleVariable;
		}
		if(!in_array($inputRuleVariable, $inputSearchVariables)) {
			$inputSearchVariables[$inputRuleVariable] = join('.', [$relationName, 'message']);
		}
	}
}
foreach ($tableSchema->columns as $column) {
	if(in_array($column->name, ['tag_id'])) {
		$relationName = $generator->setRelation($column->name);
		$inputRuleVariable = $relationName.'_i';
		$arrayRelations[$relationName] = $relationName;
		if(!in_array($inputRuleVariable, $inputRuleVariables)) {
			$inputRuleVariables[] = $inputRuleVariable;
		}
		if(!in_array($inputRuleVariable, $inputSearchVariables)) {
			$inputSearchVariables[$inputRuleVariable] = join('.', [$relationName, 'body']);
		}
	}
}
foreach ($tableSchema->columns as $column) {
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['tag_id'])) {
		$smallintCondition = 0;
		if(preg_match('/(smallint)/', $column->type))
			$smallintCondition = 1;
		$relationName = $generator->setRelation($column->name);
		$inputRuleVariable = $relationName.'_search';
		$relationTableName = trim($foreignKeys[$column->name]);
		$arrayRelations[$relationName] = $generator->getName2ndRelation($relationName, $generator->getNameAttribute($relationTableName,'.'));
		if(!$smallintCondition && !in_array($inputRuleVariable, $inputRuleVariables)) {
			$inputRuleVariables[] = $inputRuleVariable;
		}
		if(!in_array($column->name, $inputSearchVariables)) {
			$attribute = $smallintCondition ? $column->name : $inputRuleVariable;
			$inputSearchVariables[$attribute] = join('.', [$relationName, $generator->getName2ndAttribute($relationName, $generator->getNameAttribute($relationTableName, '.'))]);
		}
	}
}
foreach ($tableSchema->columns as $column) {
	if($column->autoIncrement || $column->isPrimaryKey)
		continue;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		continue;

	$commentArray = explode(',', $column->comment);
	if(in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','member_id'])) {
		$relationName = $generator->setRelation($column->name);
		$inputRuleVariable = $relationName.'_search';
		$arrayRelations[$relationName] = $relationName;
		if(!in_array($inputRuleVariable, $inputRuleVariables)) {
			$inputRuleVariables[] = $inputRuleVariable;
		}
		if(!in_array($inputRuleVariable, $inputSearchVariables)) {
			$inputSearchVariables[$inputRuleVariable] = join('.', [$relationName, 'displayname']);
		}
	}
}

foreach($rules as $rule):
if(!empty($rule->columns)):
	// Jika public var ada merge ke safe rule columns
	if($rule->ruleType == 'safe' && !empty($inputRuleVariables))
		$rule->columns = \yii\helpers\ArrayHelper::merge($rule->columns, $inputRuleVariables);
		
	echo "\t\t\t[['".implode("', '", $rule->columns)?>'], '<?=$rule->ruleType?>'],
<?php endif;
endforeach;?>
		];
	}

	/**
	 * {@inheritdoc}
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
	 *
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
if(!empty($inputSearchVariables)) {
	foreach ($inputSearchVariables as $key => $val) {?>
		$attributes['<?php echo $key;?>'] = [
			'asc' => ['<?php echo $val;?>' => SORT_ASC],
			'desc' => ['<?php echo $val;?>' => SORT_DESC],
		];
<?php }
}?>
		$dataProvider->setSort([
			'attributes' => $attributes,
			'defaultOrder' => ['<?php echo $generator->getPrimaryKey($tableSchema);?>' => SORT_DESC],
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
