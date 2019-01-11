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
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->type == 'text' && in_array('file', $commentArray)) 
		$uploadCondition = 1;
	if($column->comment != '' && $column->comment[0] == '"')
		$getFunctionCondition = 1;
	if($column->name == 'permission')
		$permissionCondition = 1;
}
$primaryKeyColumn = $tableSchema->columns[$primaryKey];
if($primaryKeyColumn->comment == 'trigger')
	$primaryKeyTriggerCondition = 1;

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

use yii\helpers\Url;
<?php echo $uploadCondition ? "use yii\helpers\Html;\n" : '';?>
use yii\widgets\DetailView;
<?php echo $uploadCondition || $getFunctionCondition || $permissionCondition ? "use ".ltrim($generator->modelClass).";\n" : '';?>

$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($functionLabel) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $model-><?= $generator->getNameAttribute(); ?>;

$this->params['menu']['content'] = [
	['label' => <?= $generator->generateString('Back To Manage') ?>, 'url' => Url::to(['index']), 'icon' => 'table'],
	['label' => <?= $generator->generateString('Update') ?>, 'url' => Url::to(['update', <?= $urlParams ?>]), 'icon' => 'pencil'],
	['label' => <?= $generator->generateString('Delete') ?>, 'url' => Url::to(['delete', <?= $urlParams ?>]), 'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>, 'method' => 'post', 'icon' => 'trash'],
];
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

<?= "<?php echo " ?>DetailView::widget([
	'model' => $model,
	'options' => [
		'class'=>'table table-striped detail-view',
	],
	'attributes' => [
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
		if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
			$foreignCondition = 1;

		$commentArray = explode(',', $column->comment);

if($foreignCondition || in_array('user', $commentArray) || ((!$column->autoIncrement || !$column->isPrimaryKey) && in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id']))) {
	$smallintCondition = 0;
	$foreignCondition = 0;
	if(preg_match('/(smallint)/', $column->type))
		$smallintCondition = 1;
	$relationName = $generator->setRelation($column->name);
	$relationFixedName = $generator->setRelationFixed($relationName, $tableSchema->columns);
	$publicAttribute = $relationName.'_search';
	$relationAttribute = 'displayname';
	if(array_key_exists($column->name, $foreignKeys)) {
		$foreignCondition = 1;
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
		$publicAttribute = $column->name;?>
		[
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => isset($model-><?php echo $relationFixedName;?>) ? $model-><?php echo $relationFixedName;?>-><?php echo $relationAttribute;?> : '-',
		],
<?php } else if(in_array($column->dbType, array('timestamp','datetime','date'))) {?>
		[
			'attribute' => '<?php echo $column->name;?>',
<?php if($column->dbType == 'date') {?>
			'value' => Yii::$app->formatter->asDate($model-><?php echo $column->name;?>, 'medium'),
<?php } else {?>
			'value' => Yii::$app->formatter->asDatetime($model-><?php echo $column->name;?>, 'medium'),
<?php }?>
		],
<?php } else if($column->dbType == 'tinyint(1)') {?>
		[
			'attribute' => '<?php echo $column->name;?>',
<?php if($primaryKeyTriggerCondition) {
if($column->comment != '') {
	$commentArray = explode(',', $column->comment);?>
			'value' => $model-><?php echo $column->name;?> == 1 ? Yii::t('app', '<?php echo $commentArray[0];?>') : Yii::t('app', '<?php echo $commentArray[1];?>'),
<?php } else {?>
			'value' => $this->filterYesNo($model-><?php echo $column->name;?>),
<?php }
} else {
if(in_array($column->name, ['publish','headline']) || ($column->comment != '' && $column->comment[7] != '[')) {
	$comment = $column->comment;
	if($column->name == 'headline' && $comment == '')
		$comment = 'Headline,Unheadline';
	if($comment != '') {
if($comment != '' && $comment[0] == '"') {
	$functionName = ucfirst($generator->setRelation($column->name));?>
			'value' => <?php echo $modelClass;?>::get<?php echo $functionName;?>($model-><?php echo $column->name;?>),
<?php } else {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>, '<?php echo $comment;?>'),
<?php }?>
<?php } else {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>),
<?php }
if($column->name == 'publish' || ($comment != '' && $comment[0] != '"')) {?>
			'format' => 'raw',
<?php }
} else if($column->name == 'permission') {
	$functionName = ucfirst($generator->setRelation($column->name));?>
			'value' => <?php echo $modelClass;?>::get<?php echo $functionName;?>($model-><?php echo $column->name;?>),
<?php } else {?>
			'value' => $this->filterYesNo($model-><?php echo $column->name;?>),
<?php }
}?>
		],
<?php } else if (is_array($column->enumValues) && count($column->enumValues) > 0) {
			$dropDownOptionKey = $dropDownOptions[$column->dbType];
			$functionName = ucfirst($generator->setRelation($dropDownOptionKey));?>
		[
			'attribute' => '<?php echo $column->name;?>',
			'value' => <?php echo $modelClass;?>::get<?php echo $functionName;?>($model-><?php echo $column->name;?>),
		],
<?php } else if($column->type == 'text') {?>
		[
			'attribute' => '<?php echo $column->name;?>',
<?php if(in_array('file', $commentArray)):?>
			'value' => function ($model) {
<?php if($generator->uploadPathSubfolder) {?>
				$uploadPath = join('/', [<?php echo $modelClass;?>::getUploadPath(false), $model-><?php echo $primaryKey;?>]);
<?php } else {?>
				$uploadPath = <?php echo $modelClass;?>::getUploadPath(false);
<?php }?>
				return $model-><?php echo $column->name;?> ? Html::img(join('/', [Url::Base(), $uploadPath, $model-><?php echo $column->name;?>]), ['width' => '100%']).'<br/><br/>'.$model-><?php echo $column->name;?> : '-';
			},
<?php elseif(in_array('serialize', $commentArray)):?>
			'value' => serialize($model-><?php echo $column->name;?>),
<?php else:?>
			'value' => $model-><?php echo $column->name;?> ? $model-><?php echo $column->name;?> : '-',
<?php endif;
if(in_array('redactor', $commentArray) || in_array('file', $commentArray)):?>
			'format' => 'html',
<?php endif;?>
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
		],
<?php } else {
		$format = $generator->generateColumnFormat($column);
		echo "\t\t'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
		}
	}
	}
}
?>
	],
]) ?>

</div>