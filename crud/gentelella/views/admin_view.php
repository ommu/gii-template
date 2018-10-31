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

$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
$functionLabel = ucwords(Inflector::pluralize($generator->shortLabel($modelClass)));

$uploadCondition = 0;
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->type == 'text' && in_array('file', $commentArray)) 
		$uploadCondition = 1;
}

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this yii\web\View
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

use Yii;
use yii\helpers\Url;
use yii\widgets\DetailView;
<?php 
echo $uploadCondition ? "use ".ltrim($generator->modelClass)."\n" : '';
?>

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

if($foreignCondition || in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])) {
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
			'value' => !in_array($model-><?php echo $column->name;?>, <?php echo $column->dbType == 'date' ? "['0000-00-00','1970-01-01','0002-12-02','-0001-11-30']" : "['0000-00-00 00:00:00','1970-01-01 00:00:00','0002-12-02 07:07:12','-0001-11-30 00:00:00']";?>) ? Yii::$app->formatter->format($model-><?php echo $column->name;?>, '<?php echo $column->dbType == 'date' ? $column->dbType : 'datetime';?>') : '-',
		],
<?php } else if($column->dbType == 'tinyint(1)') {?>
		[
			'attribute' => '<?php echo $column->name;?>',
<?php if(in_array($column->name, ['publish','headline']) || ($column->comment != '' && $column->comment[7] != '[')) {
	if($column->name == 'publish') {
if($column->comment == '') {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>),
<?php } else {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>, '<?php echo $column->comment;?>'),
<?php 	}
	} else if($column->name == 'headline') {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>, 'Headline,Unheadline', true),
<?php } else {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>, '<?php echo $column->comment;?>'),
<?php }?>
			'format' => 'raw',
<?php } else {?>
			'value' => $this->filterYesNo($model-><?php echo $column->name;?>),
<?php }?>
		],
<?php } else if($column->type == 'text') {?>
		[
			'attribute' => '<?php echo $column->name;?>',
<?php if(in_array('file', $commentArray)):?>
			'value' => function ($model) {
				$image = join('/', [Url::Base(), <?php echo $modelClass;?>::getUploadPath(false), $model-><?php echo $column->name;?>]);
				return $model-><?php echo $column->name;?> ? Html::img($image, ['width' => '100%']).'<br/><br/>'.$image : '-';
			},
<?php elseif(in_array('serialize', $commentArray)):?>
			'value' => serialize($model-><?php echo $column->name;?>),
<?php else:?>
			'value' => $model-><?php echo $column->name;?> ? $model-><?php echo $column->name;?> : '-',
<?php endif;
if(in_array('redactor', $commentArray) || in_array('file', $commentArray)):?>
			'format' => 'raw',
<?php endif;?>
		],
<?php } else {
	if(in_array('trigger[delete]', $commentArray)) {
		$publicAttribute = $column->name.'_i';
		$publicAttributeRelation = preg_match('/(name|title)/', $column->name) ? 'title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? 'description' : $column->name.'Rltn') : $column->name.'Rltn');?>
		[
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => isset($model-><?php echo $publicAttributeRelation;?>) ? $model-><?php echo $publicAttributeRelation;?>->message : '-',
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
