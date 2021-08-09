<?php
/**
 * This is the template for generating CRUD search class of the specified model.
 */

use yii\helpers\StringHelper;
use yii\helpers\Inflector;
use yii\helpers\ArrayHelper;

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

$hasManyRelation = [];
foreach ($relations as $name => $relation) {
	if(!$relation[2])
		continue;
    $relationName = ($relation[2] ? lcfirst($generator->setRelation($name, true)) : $generator->setRelation($name));
    $hasManyRelation[Inflector::singularize($relationName)] = $relationName;
}

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

foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$relationName = $generator->i18nRelation($column->name);
		$propertyName = $column->name.'_i';
		$arrayRelations[$relationName]['relation'] = $relationName;
		$arrayRelations[$relationName]['relationAlias'] = $relationName;
		$propertyNameFilter = ArrayHelper::map($arrayRelations, 'property', 'property');
		if(!in_array($propertyName, $propertyNameFilter))
			$arrayRelations[$relationName]['propertySearch'] = $arrayRelations[$relationName]['property'] = $propertyName;
		$propertyFieldFilter = ArrayHelper::map($arrayRelations, 'propertyField', 'propertyField');
		if(!in_array($propertyName, $propertyFieldFilter))
			$arrayRelations[$relationName]['propertyField'] = join('.', [$relationName, 'message']);
	}
}
foreach ($tableSchema->columns as $column) {
	if(in_array($column->name, ['tag_id'])) {
		$relationName = $generator->setRelation($column->name);
		$propertyName = $relationName.ucwords('body');
		$arrayRelations[$relationName]['relation'] = $relationName;
		$arrayRelations[$relationName]['relationAlias'] = $relationName;
		$propertyNameFilter = ArrayHelper::map($arrayRelations, 'property', 'property');
		if(!in_array($propertyName, $propertyNameFilter))
			$arrayRelations[$relationName]['propertySearch'] = $arrayRelations[$relationName]['property'] = $propertyName;
		$propertyFieldFilter = ArrayHelper::map($arrayRelations, 'propertyField', 'propertyField');
		if(!in_array($propertyName, $propertyFieldFilter))
			$arrayRelations[$relationName]['propertyField'] = join('.', [$relationName, 'body']);
	}
}
foreach ($tableSchema->columns as $column) {
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['tag_id'])) {
		$relationName = $generator->setRelation($column->name);
		$relationFixedName = $generator->setRelationFixed($relationName, $tableSchema->columns);
		$relationTable = trim($foreignKeys[$column->name]);
		$relationSchema = $generator->getTableSchemaWithTableName($relationTable);
		$relationAttribute = key($generator->getNameAttributes($relationSchema));
		if(in_array($relationTable, ['ommu_users', 'ommu_members']))
			$relationAttribute = 'displayname';
		$propertyName = $relationName.ucwords(Inflector::id2camel($relationAttribute, '_'));
		if(preg_match('/('.$relationName.')/', $relationAttribute))
			$propertyName = lcfirst(Inflector::id2camel($relationAttribute, '_'));
		$arrayRelations[$relationFixedName]['relation'] = $generator->getName2ndRelation($relationName, $generator->getNameAttribute($relationTable,'.'));
		$arrayRelations[$relationFixedName]['relationAlias'] = $relationFixedName;
		$propertyNameFilter = ArrayHelper::map($arrayRelations, 'property', 'property');
		if(!in_array($propertyName, $propertyNameFilter))
			$arrayRelations[$relationFixedName]['propertySearch'] = $arrayRelations[$relationName]['property'] = $propertyName;
		$propertyFieldFilter = ArrayHelper::map($arrayRelations, 'propertyField', 'propertyField');
		if(!in_array($column->name, $propertyFieldFilter)) {
			if(preg_match('/(smallint)/', $column->type))
				$arrayRelations[$relationFixedName]['property'] = $column->name;
			$arrayRelations[$relationFixedName]['propertyField'] = join('.', [$relationName, $generator->getName2ndAttribute($relationName, $generator->getNameAttribute($relationTable, '.'))]);
		}
	}
}

$memberCondition = 0;
$memberUserCondition = 0;
foreach ($tableSchema->columns as $column) {
	if($column->autoIncrement || $column->isPrimaryKey)
		continue;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		continue;

	$commentArray = explode(',', $column->comment);
	if(in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','member_id'])) {
        if ($column->name == 'member_id') {
            $memberCondition = 1;
        }
        if ($memberCondition && $column->name == 'user_id') {
            $memberUserCondition = 1;
        }
		$relationName = $generator->setRelation($column->name);
		$relationFixedName = $generator->setRelationFixed($relationName, $tableSchema->columns);
		$propertyName = $relationName.'Displayname';
		$arrayRelations[$relationFixedName]['relation'] = $relationFixedName;
		$arrayRelations[$relationFixedName]['relationAlias'] = $relationFixedName;
		$propertyNameFilter = ArrayHelper::map($arrayRelations, 'property', 'property');
		if(!in_array($propertyName, $propertyNameFilter))
			$arrayRelations[$relationName]['propertySearch'] = $arrayRelations[$relationName]['property'] = $propertyName;
		$propertyFieldFilter = ArrayHelper::map($arrayRelations, 'propertyField', 'propertyField');
		if(!in_array($propertyName, $propertyFieldFilter))
			$arrayRelations[$relationName]['propertyField'] = join('.', [$relationFixedName, 'displayname']);
	}
}

foreach($rules as $rule):
if ($rule->ruleType == 'integer' && !empty($hasManyRelation)) {
    $rule->columns = ArrayHelper::merge($rule->columns, array_flip($hasManyRelation));
}
if(!empty($rule->columns)):
    // Jika public var ada merge ke safe rule columns
    if ($rule->ruleType == 'safe' && !empty($arrayRelations)) {
        $propertySearch = ArrayHelper::map($arrayRelations, 'propertySearch', 'propertySearch');
        if ($memberUserCondition) {
            unset($propertySearch['userDisplayname']);
        }
        $rule->columns = \yii\helpers\ArrayHelper::merge($rule->columns, $propertySearch);
    }

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
	public function search($params, $column=null)
	{
        if (!($column && is_array($column))) {
            $query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find()->alias('t');
        } else {
            $query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find()->alias('t')->select($column);
        }
<?php
if(!empty($arrayRelations)):
	$propertyFields = ArrayHelper::map($arrayRelations, 'relationAlias', 'relation');
foreach ($propertyFields as $key => $val):
	$relationAlias[] = $val.' '.$key;
endforeach;?>
		$query->joinWith([<?php echo "\n\t\t\t// '" .implode("', \n\t\t\t// '", $relationAlias). "'\n\t\t";?>]);<?php echo "\n";?>
<?php foreach ($arrayRelations as $val) {
    if ($memberUserCondition && $val['relation'] == $val['relationAlias'] && $val['relationAlias'] == 'user') {
        continue;
    }
	$smallintCondition = ($val['propertySearch'] == $val['property']) ? false : true ; ?>
        if ((isset($params['sort']) && in_array($params['sort'], ['<?php echo $smallintCondition ? $val['property'] : $val['propertySearch'];?>', '-<?php echo $smallintCondition ? $val['property'] : $val['propertySearch'];?>'])) || (isset($params['<?php echo $val['propertySearch'];?>']) && $params['<?php echo $val['propertySearch'];?>'] != '')) {
<?php if ($memberUserCondition && $val['relation'] == $val['relationAlias'] && $val['relationAlias'] == 'member') {?>
            $query->joinWith(['<?php echo $val['relation'];?> <?php echo $val['relationAlias'];?>', 'user user']);
        }
<?php } else {?>
            $query->joinWith(['<?php echo $val['relation'];?> <?php echo $val['relationAlias'];?>']);
        }
<?php }
}

if (!empty($hasManyRelation)) {
    foreach ($hasManyRelation as $key => $val) {?>
        if ((isset($params['sort']) && in_array($params['sort'], ['<?php echo $key;?>', '-<?php echo $key;?>'])) || (isset($params['<?php echo $key;?>']) && $params['<?php echo $key;?>'] != '')) {
            $query->joinWith(['<?php echo $val;?> <?php echo $val;?>']);
            if (isset($params['sort']) && in_array($params['sort'], ['<?php echo $key;?>', '-<?php echo $key;?>'])) {
                $query->select(['t.*', 'count(<?php echo $val;?>.id) as <?php echo $key;?>']);
            }
        }
<?php }
}

echo "\n";?>
		$query->groupBy(['<?php echo $generator->getPrimaryKey($tableSchema);?>']);
<?php endif;?>

        // add conditions that should always apply here
		$dataParams = [
			'query' => $query,
		];
        // disable pagination agar data pada api tampil semua
        if (isset($params['pagination']) && $params['pagination'] == 0) {
            $dataParams['pagination'] = false;
        }
		$dataProvider = new ActiveDataProvider($dataParams);

		$attributes = array_keys($this->getTableSchema()->columns);
<?php 
if(!empty($arrayRelations)) {
	$propertyFields = ArrayHelper::map($arrayRelations, 'property', 'propertyField');
    if ($memberUserCondition) {
        unset($propertyFields['userDisplayname']);
    }
	foreach ($propertyFields as $key => $val) {?>
		$attributes['<?php echo $key;?>'] = [
			'asc' => ['<?php echo $val;?>' => SORT_ASC],
			'desc' => ['<?php echo $val;?>' => SORT_DESC],
		];
<?php }
}

if (!empty($hasManyRelation)) {
    foreach ($hasManyRelation as $key => $val) {?>
        $attributes['<?php echo $key;?>'] = [
            'asc' => ['<?php echo $key;?>' => SORT_ASC],
            'desc' => ['<?php echo $key;?>' => SORT_DESC],
        ];
<?php }
}?>
		$dataProvider->setSort([
			'attributes' => $attributes,
			'defaultOrder' => ['<?php echo $generator->getPrimaryKey($tableSchema);?>' => SORT_DESC],
		]);

        if (Yii::$app->request->get('<?php echo $generator->getPrimaryKey($tableSchema);?>')) {
            unset($params['<?php echo $generator->getPrimaryKey($tableSchema);?>']);
        }
		$this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

		// grid filtering conditions
        <?php 
        $searchConditions = $searchConditions;
        $likeIndex = count($searchConditions) - 1;
        $likeSearchConditions = $searchConditions[$likeIndex];
        unset($searchConditions[$likeIndex]);
        
        if (!empty($hasManyRelation)) {
            foreach ($hasManyRelation as $key => $val) {
                $data = "if (isset(\$params['$key']) && \$params['$key'] != '') {
            if (\$this->$key == 1) {
                \$query->andWhere(['is not', '$val.id', null]);
            } else if (\$this->$key == 0) {
                \$query->andWhere(['is', '$val.id', null]);
            }
        }\n";
                array_push($searchConditions, $data);
            }
        }

        array_push($searchConditions, $likeSearchConditions);

        echo implode("\n\t\t", $searchConditions);
        //print_r($searchConditions); ?>

		return $dataProvider;
	}
}
