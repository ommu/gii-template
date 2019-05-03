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
		$namespace = 'ommu\users\models\Users';
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
<?php if(!empty($arrayRelation)):
echo 'use yii\widgets\DetailView;'."\n";
	foreach($arrayNamespace as  $val) { ?>
use <?= ltrim($val, '\\') ?>;
<?php }
endif;?>

$this->params['breadcrumbs'][] = $this->title;

<?php if(!$primaryKeyTriggerCondition):?>
$this->params['menu']['content'] = [
	['label' => <?= $generator->generateString('Add '.$functionLabel) ?>, 'url' => Url::to(['create']), 'icon' => 'plus-square', 'htmlOptions' => ['class'=>'btn btn-success']],
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
		$dropDownOptions = '';?>
<?php echo "<?php ";?>if($<?php echo $arrayRelation[$key]['relation'];?> != null) {
$model = $<?php echo $arrayRelation[$key]['relation'];?>;
echo DetailView::widget([
	'model' => $model,
	'options' => [
		'class'=>'table table-striped detail-view',
	],
	'attributes' => [
<?php
$parentTableName = $arrayRelation[$key]['table'];
$parentTableSchema = $generator->getTableSchemaWithTableName($parentTableName);
$parentClassName = $generator->generateClassName($parentTableName);
$parentPrimaryKey = $generator->getPrimaryKey($parentTableSchema);
$parentForeignKeys = $generator->getForeignKeys($parentTableSchema->foreignKeys);
$parentController = strtolower(Inflector::singularize($generator->setRelation($parentTableName, true)));
$parentController = $parentController != $generator->getModuleName() ? $parentController : 'admin';
$dropDownOptions = $generator->dropDownOptions($parentTableSchema);

if (($parentTableSchema = $parentTableSchema) === false) {
	foreach ($generator->getColumnNames() as $name) {
		echo "\t\t'" . $name . "',\n";
	}
} else {
	foreach ($parentTableSchema->columns as $column) {
		if($parentTableName == 'ommu_users') {
			if(!in_array($column->name, ['enabled','verified','level_id','email','lastlogin_date']))
				continue;
		} else {
			if($column->name[0] == '_' || $column->autoIncrement || $column->isPrimaryKey || $column->dbType == 'tinyint(1)' || in_array($column->name, ['orders','creation_date','creation_id','modified_date','modified_id','updated_date','updated_id','slug']))
				continue;
		}

		$foreignCondition = 0;
		$foreignUserCondition = 0;
		if(!empty($parentForeignKeys) && array_key_exists($column->name, $parentForeignKeys)) {
			$foreignCondition = 1;
			if($parentForeignKeys[$column->name] == 'ommu_users')
				$foreignUserCondition = 1;
		}

		$commentArray = explode(',', $column->comment);

if($foreignCondition || in_array('user', $commentArray) || ((!$column->autoIncrement || !$column->isPrimaryKey) && in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id']))) {
	$smallintCondition = 0;
	if(preg_match('/(smallint)/', $column->type))
		$smallintCondition = 1;
	$relationName = $generator->setRelation($column->name);
	$relationFixedName = $generator->setRelationFixed($relationName, $parentTableSchema->columns);
	$relationAttribute = $variableAttribute = 'displayname';
	$publicAttribute = $relationName.ucwords(Inflector::id2camel($variableAttribute, '_'));
	if(array_key_exists($column->name, $parentForeignKeys)) {
		$relationTable = trim($parentForeignKeys[$column->name]);
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
		$relationAttribute = 'body';
	}?>
		[
			'attribute' => '<?php echo $publicAttribute;?>',
<?php if($parentTableName != 'ommu_users' && $foreignCondition && !$foreignUserCondition):?>
			'value' => function ($model) {
				$<?php echo $publicAttribute;?> = isset($model-><?php echo $relationFixedName;?>) ? $model-><?php echo $relationFixedName;?>-><?php echo $relationAttribute;?> : '-';
				if($<?php echo $publicAttribute;?> != '-')
					return Html::a($<?php echo $publicAttribute;?>, ['<?php echo Inflector::singularize($relationName);?>/view', 'id'=>$model-><?php echo $column->name;?>], ['title'=>$<?php echo $publicAttribute;?>, 'class'=>'modal-btn']);
				return $<?php echo $publicAttribute;?>;
			},
			'format' => 'html',
<?php else:?>
			'value' => isset($model-><?php echo $relationFixedName;?>) ? $model-><?php echo $relationFixedName;?>-><?php echo $relationAttribute;?> : '-',
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
		],
<?php } else if($column->dbType == 'tinyint(1)') {
	$comment = $column->comment;?>
		[
			'attribute' => '<?php echo $column->name;?>',
<?php if($comment != '') {
if($comment != '' && $comment[0] == '"') {
	$functionName = ucfirst($generator->setRelation($column->name));?>
			'value' => <?php echo $parentClassName;?>::get<?php echo $functionName;?>($model-><?php echo $column->name;?>),
<?php } else {
	$commentArray = explode(',', $column->comment);?>
			'value' => $model-><?php echo $column->name;?> == 1 ? Yii::t('app', '<?php echo $commentArray[0];?>') : Yii::t('app', '<?php echo $commentArray[1];?>'),
<?php }
} else {?>
			'value' => $this->filterYesNo($model-><?php echo $column->name;?>),
<?php }?>
		],
<?php } else if (is_array($column->enumValues) && count($column->enumValues) > 0) {
			$dropDownOptionKey = $dropDownOptions[$column->dbType];
			$functionName = ucfirst($generator->setRelation($dropDownOptionKey));?>
		[
			'attribute' => '<?php echo $column->name;?>',
			'value' => <?php echo $parentClassName;?>::get<?php echo $functionName;?>($model-><?php echo $column->name;?>),
		],
<?php } else if($column->type == 'text') {?>
		[
			'attribute' => '<?php echo $column->name;?>',
<?php if(in_array('file', $commentArray)):?>
			'value' => function ($model) {
<?php if($generator->uploadPathSubfolder) {?>
				$uploadPath = join('/', [<?php echo $parentClassName;?>::getUploadPath(false), $model-><?php echo $primaryKey;?>]);
<?php } else {?>
				$uploadPath = <?php echo $parentClassName;?>::getUploadPath(false);
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
<?php } else if(preg_match('/(name|title)/', $column->name) && !in_array('trigger[delete]', $commentArray)) {?>
		[
			'attribute' => '<?php echo $column->name;?>',
			'value' => function ($model) {
				if($model-><?php echo $column->name;?> != '')
					return Html::a($model-><?php echo $column->name;?>, ['<?php echo $parentController;?>/view', 'id'=>$model-><?php echo $parentPrimaryKey;?>], ['title'=>$model-><?php echo $column->name;?>, 'class'=>'modal-btn']);
				return $model-><?php echo $column->name;?>;
			},
			'format' => 'html',
		],
<?php } else {
	if(in_array('trigger[delete]', $commentArray)) {
		$publicAttribute = $column->name.'_i';?>
		[
			'attribute' => '<?php echo $publicAttribute;?>',
<?php if(preg_match('/(name|title)/', $column->name)) {?>
			'value' => function ($model) {
				if($model-><?php echo $publicAttribute;?> != '')
					return Html::a($model-><?php echo $publicAttribute;?>, ['<?php echo $parentController;?>/view', 'id'=>$model-><?php echo $parentPrimaryKey;?>], ['title'=>$model-><?php echo $publicAttribute;?>, 'class'=>'modal-btn']);
				return $model-><?php echo $publicAttribute;?>;
			},
<?php } else {?>
			'value' => $model-><?php echo $publicAttribute;?>,
<?php }?>
<?php if(preg_match('/(name|title)/', $column->name) || in_array('redactor', $commentArray)) {?>
			'format' => 'html',
<?php }?>
		],
<?php } else {
		$format = $generator->generateColumnFormat($column);
		echo "\t\t'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
		}
	}
	}
}?>
	],
]);
}?>

<?php }
}

if(!empty($generator->searchModelClass)): ?>
<?= "<?php " . ($generator->indexWidgetType === 'grid' ? "//" : "") ?>echo $this->render('_search', ['model'=>$searchModel]); ?>

<?= "<?php " . ($generator->indexWidgetType !== 'grid' ? "//" : "") ?>echo $this->render('_option_form', ['model'=>$searchModel, 'gridColumns'=>$searchModel->activeDefaultColumns($columns), 'route'=>$this->context->route]); ?>

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