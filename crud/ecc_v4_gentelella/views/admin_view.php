<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$patternClass = array();
$patternClass[0] = '(Ommu)';
$patternClass[1] = '(Swt)';

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);
$tableSchema = $generator->getTableSchema();

$urlParams = $generator->generateUrlParams();

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
use yii\widgets\DetailView;

$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize($labelButton)) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu']['content'] = [
	['label' => <?= $generator->generateString('Back To Manage') ?>, 'url' => Url::to(['index']), 'icon' => 'table'],
	['label' => <?= $generator->generateString('Update') ?>, 'url' => Url::to(['update', <?= $urlParams ?>]), 'icon' => 'pencil'],
	['label' => <?= $generator->generateString('Delete') ?>, 'url' => Url::to(['delete', <?= $urlParams ?>]), 'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>, 'method' => 'post', 'icon' => 'trash'],
];
?>

<div class="col-md-12 col-sm-12 col-xs-12">
	<div class="x_panel">
		<div class="x_title">
			<h2><?= "<?php echo "?>Html::encode($this->title); ?></h2>
			<?= "<?php " ?>if($this->params['menu']['content']):
			echo MenuContent::widget(['items' => $this->params['menu']['content']]);
			endif;?>
			<ul class="nav navbar-right panel_toolbox">
				<li><a href="#" title="<?php echo "<?php echo {$generator->generateString('Toggle')};?>";?>" class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
				<li><a href="#" title="<?php echo "<?php echo {$generator->generateString('Close')};?>";?>" class="close-link"><i class="fa fa-close"></i></a></li>
			</ul>
			<div class="clearfix"></div>
		</div>
		<div class="x_content">
			<?= "<?php echo " ?>DetailView::widget([
				'model' => $model,
				'options' => [
					'class'=>'table table-striped detail-view',
				],
				'attributes' => [
<?php
//echo '<pre>';
//print_r($tableSchema);
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
if (($tableSchema = $tableSchema) === false) {
	foreach ($generator->getColumnNames() as $name) {
		echo "\t\t\t\t\t'" . $name . "',\n";
	}
} else {
	foreach ($tableSchema->columns as $column) {
if($column->dbType == 'tinyint(1)') {?>
					[
						'attribute' => '<?php echo $column->name;?>',
						'value' => $model-><?php echo $column->name;?> == 1 ? Yii::t('app', 'Yes') : Yii::t('app', 'No'),
					],
<?php } else if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))) {
	$relationNameArray = explode('_', $column->name);
	$relationName = lcfirst($relationNameArray[0]);
	$relationSearchName = $relationName.'_search';?>
					[
						'attribute' => '<?php echo $relationSearchName;?>',
						'value' => $model-><?php echo $column->name;?> ? $model-><?php echo $relationName;?>->displayname : '-',
					],
<?php } else if(in_array($column->dbType, array('timestamp','datetime','date'))) {?>
					[
						'attribute' => '<?php echo $column->name;?>',
						'value' => !in_array($model-><?php echo $column->name;?>, <?php echo $column->dbType == 'date' ? "['0000-00-00','1970-01-01']" : "['0000-00-00 00:00:00','1970-01-01 00:00:00']";?>) ? Yii::$app->formatter->format($model-><?php echo $column->name;?>, '<?php echo $column->dbType == 'date' ? $column->dbType : 'datetime';?>') : '-',
					],
<?php } else if(in_array($column->dbType, array('text'))) {?>
					[
						'attribute' => '<?php echo $column->name;?>',
						'value' => $model-><?php echo $column->name;?> ? $model-><?php echo $column->name;?> : '-',
						'format'	=> 'html',
					],
<?php } else {
if(!empty($foreignKeys) && in_array($column->name, $foreignKeys)) {
	$relationTableName = array_search($column->name, $foreignKeys);
	$relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
	$relationAttributeName = $generator->getNameAttribute($relationTableName);
	$relationName = lcfirst(Inflector::singularize($generator->setRelationName($relationModelName)));
	$relationSearchName = $relationName.'_search';?>
					[
						'attribute' => '<?php echo $relationSearchName;?>',
						'value' => $model-><?php echo $relationName;?>-><?php echo $relationAttributeName;?>,
					],
<?php } else {
	$format = $generator->generateColumnFormat($column);
			echo "\t\t\t\t\t'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
		}
	}
	}
}
?>
				],
			]) ?>
		</div>
	</div>
</div>