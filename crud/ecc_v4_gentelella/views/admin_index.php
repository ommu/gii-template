<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

$patternLabel = array();
$patternLabel[0] = '(Core )';
$patternLabel[1] = '(Zone )';

$labelButton = preg_replace($patternLabel, '', $label);

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this yii\web\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->modelClass)."\n"; ?>
 * version: 0.0.1
 *
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @link <?php echo $yaml['link']."\n";?>
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @contact <?php echo $yaml['contact']."\n";?>
 *
 */

use yii\helpers\Html;
use yii\helpers\Url;
use app\libraries\MenuContent;
use app\libraries\MenuOption;
use app\components\Utility;
use <?= ($generator->indexWidgetType === 'grid' ? "app\\libraries\\grid\\GridView" : "yii\\widgets\\ListView"); ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;'."\n" : ''; ?>

$this->params['breadcrumbs'][] = $this->title;

$this->params['menu']['content'] = [
	['label' => <?= $generator->generateString('Add '.$labelButton) ?>, 'url' => Url::to(['create']), 'icon' => 'plus-square'],
];
<?php if(!empty($generator->searchModelClass)): ?>
$this->params['menu']['option'] = [
<?= ($generator->indexWidgetType === 'grid' ? "\t// " : "\t") ?>['label' => <?php echo $generator->generateString('Search');?>, 'url' => 'javascript:void(0);'],
<?= ($generator->indexWidgetType !== 'grid' ? "\t// " : "\t") ?>['label' => <?php echo $generator->generateString('Grid Options');?>, 'url' => 'javascript:void(0);'],
];
<?php endif; ?>
?>

<?= $generator->enablePjax ? "<?php Pjax::begin(); ?>\n" : ''; ?>
<div class="col-md-12 col-sm-12 col-xs-12">
	<?= "<?php "?>if(Yii::$app->session->hasFlash('success'))
		echo Utility::flashMessage(Yii::$app->session->getFlash('success'));
	else if(Yii::$app->session->hasFlash('error'))
		echo Utility::flashMessage(Yii::$app->session->getFlash('error'), 'danger');?>

	<div class="x_panel">
		<div class="x_title">
			<h2><?= "<?php echo "?>Html::encode($this->title); ?></h2>
			<?= "<?php " ?>if($this->params['menu']['content']):
			echo MenuContent::widget(['items' => $this->params['menu']['content']]);
			endif;?>
<?php if(!empty($generator->searchModelClass)): ?>
			<ul class="nav navbar-right panel_toolbox">
				<li><a href="#" title="<?php echo "<?php echo {$generator->generateString('Toggle')};?>";?>" class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
<?php echo "\t\t\t\t<?php if(\$this->params['menu']['option']):?>\n";?>
				<li class="dropdown">
					<a href="#" title="<?php echo "<?php echo {$generator->generateString('Options')};?>";?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
<?php echo "\t\t\t\t\t<?php echo MenuOption::widget(['items' => \$this->params['menu']['option']]);?>\n";?>
				</li>
<?php echo "\t\t\t\t<?php endif;?>\n";?>
				<li><a href="#" title="<?php echo "<?php echo {$generator->generateString('Close')};?>";?>" class="close-link"><i class="fa fa-close"></i></a></li>
			</ul>
<?php endif; ?>
			<div class="clearfix"></div>
		</div>
		<div class="x_content">
<?php echo "\t\t\t<?php echo \$this->description != '' ? \"<p class=\\\"text-muted font-13 m-b-30\\\">\$this->description</p>\" : '';?>\n";?>

<?php if(!empty($generator->searchModelClass)): ?>
<?= "\t\t\t<?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>

<?= "\t\t\t<?php " . ($generator->indexWidgetType !== 'grid' ? "// " : "") ?>echo $this->render('_option_form', ['model' => $searchModel, 'gridColumns' => GridView::getActiveDefaultColumns($columns), 'route' => $this->context->route]); ?>

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
				'header' => <?php echo $generator->generateString('Options');?>,
				'contentOptions' => [
					'class'=>'action-column',
				],
				'buttons' => [
					'view' => function ($url, $model, $key) {
						$url = Url::to(['view', 'id' => $model->primaryKey]);
						return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => <?= $generator->generateString('View ' . $labelButton) ?>]);
					},
					'update' => function ($url, $model, $key) {
						$url = Url::to(['update', 'id' => $model->primaryKey]);
						return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ['title' => <?= $generator->generateString('Update ' . $labelButton) ?>]);
					},
					'delete' => function ($url, $model, $key) {
						$url = Url::to(['delete', 'id' => $model->primaryKey]);
						return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
							'title' => <?= $generator->generateString('Delete ' . $labelButton) ?>,
							'data-confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
							'data-method'  => 'post',
						]);
					},
				],
				'template' => '{view}{update}{delete}',
			]);
			
			echo GridView::widget([
				'dataProvider' => $dataProvider,
<?= !empty($generator->searchModelClass) ? "\t\t\t\t'filterModel' => \$searchModel,\n" : ''; ?>
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
		</div>
	</div>
</div>
<?= $generator->enablePjax ? "<?php Pjax::end(); ?>\n" : '' ?>