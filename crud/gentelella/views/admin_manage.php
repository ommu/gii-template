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

$tableSchema = $generator->getTableSchema();
$primaryKey = $generator->getPrimaryKey($tableSchema);

$primaryKeyTriggerCondition = 0;
$primaryKeyColumn = $tableSchema->columns[$primaryKey];
if($primaryKeyColumn->comment == 'trigger')
	$primaryKeyTriggerCondition = 1;

$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
$arrayRelation = $arrayNamespace = [];
$i=0;
foreach($foreignKeys as $key => $val) {
	$arrayRelation[$i]['relation'] = $generator->setRelation($key);
	$arrayRelation[$i]['table'] = $val;
	if($val == 'ommu_users')
		$namespace = 'app\models\Users';
	else if($val == 'ommu_members')
		$namespace = 'ommu\member\models\Members';
	else {
		$module = $tableSchema->columns[$key]->comment;
		if($module)
			$namespace = $generator->getUseModel($module, $generator->generateClassName($val));
		else
			$namespace = str_replace($modelClass, $generator->generateClassName($val), $generator->modelClass);
	}
	if(!in_array($namespace, $arrayNamespace))
		$arrayNamespace[] = $namespace;
	$i++;
}

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this app\components\View
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

<?php if(!$primaryKeyTriggerCondition):?>
$this->params['menu']['content'] = [
	['label' => <?= $generator->generateString('Add '.$functionLabel) ?>, 'url' => Url::to(['create']), 'icon' => 'plus-square', 'htmlOptions' => ['class' => 'btn btn-success']],
];
<?php endif;
if(!empty($generator->searchModelClass)): ?>
$this->params['menu']['option'] = [
<?= ($generator->indexWidgetType === 'grid' ? "\t//" : "\t") ?>['label' => <?php echo $generator->generateString('Search');?>, 'url' => 'javascript:void(0);'],
<?= ($generator->indexWidgetType !== 'grid' ? "\t//" : "\t") ?>['label' => <?php echo $generator->generateString('Grid Option');?>, 'url' => 'javascript:void(0);'],
];
<?php endif; ?>
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-manage">
<?= $generator->enablePjax ? "<?php Pjax::begin(); ?>\n\n" : '';

if(!empty($arrayRelation)) {
	foreach($arrayRelation as $key => $val) {
		$render = join('/', ['',$arrayRelation[$key]['relation'], 'admin_view']);
		if($arrayRelation[$key]['table'] == 'ommu_users') {
			$render = '@ommu/users/views/member/admin_view';
		} else if($arrayRelation[$key]['table'] == 'ommu_members') {
			$render = '@ommu/member/views/manage/admin/admin_view';
        } ?>
<?php echo "<?php ";?>if ($<?php echo $arrayRelation[$key]['relation'];?> != null) {
	echo $this->render('<?php echo $render;?>', ['model' => $<?php echo $arrayRelation[$key]['relation'];?>, 'small' => true]);
} ?>

<?php }
}

if(!empty($generator->searchModelClass)): ?>
<?= "<?php " . ($generator->indexWidgetType === 'grid' ? "//" : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>

<?= "<?php " . ($generator->indexWidgetType !== 'grid' ? "//" : "") ?>echo $this->render('_option_form', ['model' => $searchModel, 'gridColumns' => $searchModel->activeDefaultColumns($columns), 'route' => $this->context->route]); ?>

<?php endif; ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>
<?= "<?php\n" ?>
<?php if(!empty($generator->searchModelClass)): ?>
$columnData = $columns;
<?php else: ?>
$columnData = [
	['class' => 'app\components\grid\SerialColumn'],
<?php
$count = 0;
if ($tableSchema === false) {
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
	'class' => 'app\components\grid\ActionColumn',
	'header' => <?php echo $generator->generateString('Option');?>,
	'urlCreator' => function($action, $model, $key, $index) {
        if ($action == 'view') {
            return Url::to(['view', 'id' => $key]);
        }
        if ($action == 'update') {
            return Url::to(['update', 'id' => $key]);
        }
        if ($action == 'delete') {
            return Url::to(['delete', 'id' => $key]);
        }
	},
	'buttons' => [
		'view' => function ($url, $model, $key) {
			return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => <?= $generator->generateString('Detail ' . $functionLabel) ?>]);
		},
		'update' => function ($url, $model, $key) {
			return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ['title' => <?= $generator->generateString('Update ' . $functionLabel) ?>]);
		},
		'delete' => function ($url, $model, $key) {
			return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
				'title' => <?= $generator->generateString('Delete ' . $functionLabel) ?>,
				'data-confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
				'data-method'  => 'post',
			]);
		},
	],
	'template' => '{view} {update} {delete}',
]);

echo GridView::widget([
	'dataProvider' => $dataProvider,
<?= !empty($generator->searchModelClass) ? "\t'filterModel' => \$searchModel,\n" : ''; ?>
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