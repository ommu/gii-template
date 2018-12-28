<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);

$functionLabel = ucwords($generator->shortLabel($modelClass));

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this yii\web\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->modelClass)."\n"; ?>
<?= !empty($generator->searchModelClass) ? " * @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . "\n" : '' ?>
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
use <?= ($generator->indexWidgetType === 'grid' ? "app\\components\\grid\\GridView" : "yii\\widgets\\ListView"); ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;'."\n" : ''; ?>

$this->params['breadcrumbs'][] = $this->title;

$this->params['menu']['content'] = [
	['label' => <?= $generator->generateString('Add '.$functionLabel) ?>, 'url' => Url::to(['create']), 'icon' => 'plus-square'],
];
<?php if(!empty($generator->searchModelClass)): ?>
$this->params['menu']['option'] = [
<?= ($generator->indexWidgetType === 'grid' ? "\t//" : "\t") ?>['label' => <?php echo $generator->generateString('Search');?>, 'url' => 'javascript:void(0);'],
<?= ($generator->indexWidgetType !== 'grid' ? "\t//" : "\t") ?>['label' => <?php echo $generator->generateString('Grid Option');?>, 'url' => 'javascript:void(0);'],
];
<?php endif; ?>
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
<?= $generator->enablePjax ? "<?php Pjax::begin(); ?>\n" : ''; ?>

<?php if(!empty($generator->searchModelClass)): ?>
<?= "<?php " . ($generator->indexWidgetType === 'grid' ? "//" : "") ?>echo $this->render('_search', ['model'=>$searchModel]); ?>

<?= "<?php " . ($generator->indexWidgetType !== 'grid' ? "//" : "") ?>echo $this->render('_option_form', ['model'=>$searchModel, 'gridColumns'=>$this->activeDefaultColumns($columns), 'route'=>$this->context->route]); ?>

<?php endif; ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>
<?= "<?php \n" ?>
<?php if(!empty($generator->searchModelClass)): ?>
$columnData = $columns;
<?php else: ?>
$columnData = [
	['class' => 'yii\grid\SerialColumn'],
<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
	foreach ($generator->getColumnNames() as $name) {
		if (++$count < 6) {
			echo "\t\t\t\t'" . $name . "',\n";
		} else {
			echo "\t\t\t\t// '" . $name . "',\n";
		}
	}
} else {
	foreach ($tableSchema->columns as $column) {
		$format = $generator->generateColumnFormat($column);
		if (++$count < 6 && !$column->isPrimaryKey) {
			echo "\t\t\t\t'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
		} else {
			echo "\t\t\t\t// '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
		}
	}
}
?>
			];

<?php endif; ?>
array_push($columnData, [
	'class' => 'yii\grid\ActionColumn',
	'header' => <?php echo $generator->generateString('Option');?>,
	'contentOptions' => [
		'class'=>'action-column',
	],
	'buttons' => [
		'view' => function ($url, $model, $key) {
			$url = Url::to(['view', 'id'=>$model->primaryKey]);
			return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => <?= $generator->generateString('Detail ' . $functionLabel) ?>]);
		},
		'update' => function ($url, $model, $key) {
			$url = Url::to(['update', 'id'=>$model->primaryKey]);
			return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ['title' => <?= $generator->generateString('Update ' . $functionLabel) ?>]);
		},
		'delete' => function ($url, $model, $key) {
			$url = Url::to(['delete', 'id'=>$model->primaryKey]);
			return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
				'title' => <?= $generator->generateString('Delete ' . $functionLabel) ?>,
				'data-confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
				'data-method'  => 'post',
			]);
		},
	],
	'template' => '{view}{update}{delete}',
]);

echo GridView::widget([
	'dataProvider' => $dataProvider,
<?= !empty($generator->searchModelClass) ? "\t'filterModel' => \$searchModel,\n" : ''; ?>
	'layout' => '<div class="row"><div class="col-sm-12">{items}</div></div><div class="row sum-page"><div class="col-sm-5">{summary}</div><div class="col-sm-7">{pager}</div></div>',
	'columns' => $columnData,
]); ?>
<?php else: ?>
<?= "<?php echo " ?>ListView::widget([
	'dataProvider' => $dataProvider,
	'itemOptions' => ['class' => 'item'],
	'itemView' => function ($model, $key, $index, $widget) {
		return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
	},
]); ?>
<?php endif; ?>

<?= $generator->enablePjax ? "<?php Pjax::end(); ?>\n" : '' ?>
</div>