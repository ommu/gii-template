<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);
$tableSchema = $generator->getTableSchema();

$primaryKey = $generator->getPrimaryKey($tableSchema);
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
$functionLabel = ucwords(Inflector::pluralize($generator->shortLabel($modelClass)));

$uploadCondition = 0;
$getFunctionCondition = 0;
$permissionCondition = 0;
$primaryKeyTriggerCondition = 0;
$relationCondition = 0;
$enumCondition = 0;
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->type == 'text' && in_array('file', $commentArray)) 
		$uploadCondition = 1;
	if($column->comment != '' && $column->comment[0] == '"')
		$getFunctionCondition = 1;
	if($column->name == 'permission')
		$permissionCondition = 1;
	if (is_array($column->enumValues) && count($column->enumValues) > 0)
		$enumCondition = 1;
}
$primaryKeyColumn = $tableSchema->columns[$primaryKey];
if($primaryKeyColumn->comment == 'trigger')
	$primaryKeyTriggerCondition = 1;

foreach ($relations as $name => $relation) {
	if($relation[2])
		$relationCondition = 1;
}

$dropDownOptions = $generator->dropDownOptions($tableSchema);

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this app\components\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->modelClass)."\n"; ?>
 *
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($generator->useModified) {?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @modified by <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
<?php }?>
 * @link <?php echo $generator->link."\n";?>
 *
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

$this->params['breadcrumbs'][] = ['label' => <?php echo $generator->generateString($functionLabel) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $model-><?php echo $generator->getNameAttribute(); ?>;

if(!$small) {
$this->params['menu']['content'] = [
	['label' => <?php echo $generator->generateString('Detail') ?>, 'url' => Url::to(['view', <?php echo $urlParams ?>]), 'icon' => 'eye', 'htmlOptions' => ['class'=>'btn btn-success']],
	['label' => <?php echo $generator->generateString('Update') ?>, 'url' => Url::to(['update', <?php echo $urlParams ?>]), 'icon' => 'pencil', 'htmlOptions' => ['class'=>'btn btn-primary']],
	['label' => <?php echo $generator->generateString('Delete') ?>, 'url' => Url::to(['delete', <?php echo $urlParams ?>]), 'htmlOptions' => ['data-confirm'=><?php echo $generator->generateString('Are you sure you want to delete this item?') ?>, 'data-method'=>'post', 'class'=>'btn btn-danger'], 'icon' => 'trash'],
];
} ?>

<div class="<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

<?php echo "<?php\n" ?>
$attributes = [
<?php
if (($tableSchema = $tableSchema) === false) {
	foreach ($generator->getColumnNames() as $name) {
		echo "\t\t'" . $name . "',\n";
	}
} else {
	foreach ($tableSchema->columns as $column) {
		if($column->name[0] == '_')
			continue;

		$foreignCondition = 0;
		$foreignUserCondition = 0;
		if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
			$foreignCondition = 1;
			if($foreignKeys[$column->name] == 'ommu_users')
				$foreignUserCondition = 1;
		}

		$commentArray = explode(',', $column->comment);

if($foreignCondition || in_array('user', $commentArray) || ((!$column->autoIncrement || !$column->isPrimaryKey) && in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id']))) {
	$smallintCondition = 0;
	if(preg_match('/(smallint)/', $column->type))
		$smallintCondition = 1;
	$relationName = $generator->setRelation($column->name);
	$relationFixedName = $generator->setRelationFixed($relationName, $tableSchema->columns);
	$relationAttribute = $variableAttribute = 'displayname';
	$publicAttribute = $relationName.ucwords(Inflector::id2camel($variableAttribute, '_'));
	if(array_key_exists($column->name, $foreignKeys)) {
		$relationTable = trim($foreignKeys[$column->name]);
		$relationAttribute = $generator->getNameAttribute($relationTable);
		$relationSchema = $generator->getTableSchemaWithTableName($relationTable);
		$variableAttribute = key($generator->getNameAttributes($relationSchema));
		if(in_array($relationTable, ['ommu_users', 'ommu_members']))
			$relationAttribute = $variableAttribute = 'displayname';
		$publicAttribute = $relationName.ucwords(Inflector::id2camel($variableAttribute, '_'));
		if(preg_match('/('.$relationName.')/', $variableAttribute))
			$publicAttribute = lcfirst(Inflector::id2camel($variableAttribute, '_'));
	}
	if($column->name == 'tag_id') {
		$publicAttribute = $relationName.ucwords('body');
		$relationAttribute =  'body';
	}?>
	[
		'attribute' => '<?php echo $publicAttribute;?>',
<?php if($foreignCondition && !$foreignUserCondition):
	$controller = Inflector::singularize($relationName) != $generator->getModuleName() ? Inflector::singularize($relationName) : 'admin';?>
		'value' => function ($model) {
			$<?php echo $publicAttribute;?> = isset($model-><?php echo $relationFixedName;?>) ? $model-><?php echo $relationFixedName;?>-><?php echo $relationAttribute;?> : '-';
			if($<?php echo $publicAttribute;?> != '-')
				return Html::a($<?php echo $publicAttribute;?>, ['<?php echo $controller;?>/view', 'id'=>$model-><?php echo $column->name;?>], ['title'=>$<?php echo $publicAttribute;?>, 'class'=>'modal-btn']);
			return $<?php echo $publicAttribute;?>;
		},
		'format' => 'html',
<?php else:?>
		'value' => isset($model-><?php echo $relationFixedName;?>) ? $model-><?php echo $relationFixedName;?>-><?php echo $relationAttribute;?> : '-',
		'visible' => !$small,
<?php endif;?>
	],
<?php } else if(in_array($column->dbType, array('timestamp','datetime','date'))) {?>
	[
		'attribute' => '<?php echo $column->name;?>',
<?php if($column->dbType == 'date') {?>
		'value' => Yii::$app->formatter->asDate($model-><?php echo $column->name;?>, 'medium'),
<?php } else {?>
		'value' => Yii::$app->formatter->asDatetime($model-><?php echo $column->name;?>, 'medium'),
<?php }?>
		'visible' => !$small,
	],
<?php } else if($column->dbType == 'tinyint(1)') {?>
	[
		'attribute' => '<?php echo $column->name;?>',
<?php if($primaryKeyTriggerCondition) {
if($column->comment != '') {
	$commentArray = explode(',', $column->comment);?>
		'value' => $model-><?php echo $column->name;?> == 1 ? Yii::t('app', '<?php echo $commentArray[0];?>') : Yii::t('app', '<?php echo $commentArray[1];?>'),
<?php } else {?>
		'value' => $model->filterYesNo($model-><?php echo $column->name;?>),
<?php }
} else {
if(in_array($column->name, ['publish','headline']) || ($column->comment != '' && $column->comment[7] != '[')) {
	$comment = $column->comment;
	if($column->name == 'headline' && $comment == '')
		$comment = 'Headline,Unheadline';
	if($comment != '') {
if($comment != '' && $comment[0] == '"') {
	$functionName = ucfirst($generator->setRelation($column->name));?>
		'value' => $model::get<?php echo $functionName;?>($model-><?php echo $column->name;?>),
<?php } else {?>
		'value' => $model->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>, '<?php echo $comment;?>'),
<?php }?>
<?php } else {?>
		'value' => $model->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>),
<?php }
if($column->name == 'publish' || ($comment != '' && $comment[0] != '"')) {?>
		'format' => 'raw',
<?php }
} else if($column->name == 'permission') {
	$functionName = ucfirst($generator->setRelation($column->name));?>
		'value' => $model::get<?php echo $functionName;?>($model-><?php echo $column->name;?>),
<?php } else {?>
		'value' => $model->filterYesNo($model-><?php echo $column->name;?>),
<?php }
}?>
		'visible' => !$small,
	],
<?php } else if (is_array($column->enumValues) && count($column->enumValues) > 0) {
			$dropDownOptionKey = $dropDownOptions[$column->dbType];
			$functionName = ucfirst($generator->setRelation($dropDownOptionKey));?>
	[
		'attribute' => '<?php echo $column->name;?>',
		'value' => $model::get<?php echo $functionName;?>($model-><?php echo $column->name;?>),
		'visible' => !$small,
	],
<?php } else if($column->type == 'text') {?>
	[
		'attribute' => '<?php echo $column->name;?>',
<?php if(in_array('file', $commentArray)):?>
		'value' => function ($model) {
<?php if($generator->uploadPathSubfolder) {?>
			$uploadPath = join('/', [$model::getUploadPath(false), $model-><?php echo $primaryKey;?>]);
<?php } else {?>
			$uploadPath = $model::getUploadPath(false);
<?php }
if(in_array('pdf', $commentArray)) {?>
			return $model-><?php echo $column->name;?> ? Html::a($model-><?php echo $column->name;?>, Url::to(join('/', ['@webpublic', $uploadPath, $model-><?php echo $column->name;?>])), ['title'=>$model-><?php echo $column->name;?>, 'target'=>'_blank']) : '-';
<?php } else {?>
			return $model-><?php echo $column->name;?> ? Html::img(Url::to(join('/', ['@webpublic', $uploadPath, $model-><?php echo $column->name;?>])), ['alt'=>$model-><?php echo $column->name;?>, 'class'=>'mb-3']).'<br/>'.$model-><?php echo $column->name;?> : '-';
<?php }?>
		},
<?php elseif(in_array('serialize', $commentArray)):?>
		'value' => serialize($model-><?php echo $column->name;?>),
<?php elseif(in_array('json', $commentArray)):?>
		'value' => Json::encode($model-><?php echo $column->name;?>),
<?php else:?>
		'value' => $model-><?php echo $column->name;?> ? $model-><?php echo $column->name;?> : '-',
<?php endif;
if(in_array('redactor', $commentArray) || in_array('file', $commentArray)):
if(in_array('file', $commentArray) && in_array('pdf', $commentArray)):?>
		'format' => 'raw',
<?php else:?>
		'format' => 'html',
<?php endif;?>
<?php endif;?>
		'visible' => !$small,
	],
<?php } else {
	if(in_array('trigger[delete]', $commentArray)) {
		$publicAttribute = $column->name.'_i';?>
	[
		'attribute' => '<?php echo $publicAttribute;?>',
		'value' => $model-><?php echo $publicAttribute;?>,
<?php if(in_array('redactor', $commentArray)):?>
		'format' => 'html',
<?php endif;?>
		'visible' => !$small,
	],
<?php } else {
		$format = $generator->generateColumnFormat($column);
if($format == 'text') {?>
	[
		'attribute' => '<?php echo $column->name;?>',
		'value' => $model-><?php echo $column->name;?> ? $model-><?php echo $column->name;?> : '-',
<?php if(!preg_match('/(name|title|body)/', $column->name)) {?>
		'visible' => !$small,
<?php }?>
	],
<?php } else
		echo "\t'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
		}
	}
	}
}

foreach ($relations as $name => $relation) {
	if(!$relation[2])
		continue;

	$publishRltnCondition = 0;
	if(preg_match('/(%s.publish)/', $relation[0]))
		$publishRltnCondition = 1;
	$relationName = ($relation[2] ? lcfirst($generator->setRelation($name, true)) : $generator->setRelation($name));
	$controller = Inflector::singularize($relationName) != $generator->getModuleName() ? Inflector::singularize($relationName) : 'admin'; ?>
	[
		'attribute' => '<?php echo $relationName;?>',
		'value' => function ($model) {
			$<?php echo lcfirst($relationName);?> = $model->get<?php echo ucfirst($relationName);?>(true);
			return Html::a($<?php echo lcfirst($relationName);?>, ['<?php echo $controller;?>/manage', '<?php echo $generator->setRelation($relation[4]);?>'=>$model->primaryKey<?php echo $publishRltnCondition ? ', \'publish\'=>1' : '';?>], ['title'=>Yii::t('app', '{count} <?php echo $relationName;?>', ['count'=>$<?php echo lcfirst($relationName);?>])]);
		},
		'format' => 'html',
		'visible' => !$small,
	],
<?php }?>
	[
		'attribute' => '',
		'value' => Html::a(Yii::t('app', 'Update'), ['update', 'id'=>$model->primaryKey], ['title'=>Yii::t('app', 'Update'), 'class'=>'btn btn-primary']),
		'format' => 'html',
		'visible' => !$small && Yii::$app->request->isAjax ? true : false,
	],
];

echo DetailView::widget([
	'model' => $model,
	'options' => [
		'class'=>'table table-striped detail-view',
	],
	'attributes' => $attributes,
]); ?>

</div>