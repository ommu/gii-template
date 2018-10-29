<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if(empty($safeAttributes))
	$safeAttributes = $model->attributes();
$tableSchema = $generator->tableSchema;
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);

$redactorCondition = 0;
$uploadCondition = 0;
$foreignCondition = 0;
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if(in_array('redactor', $commentArray))
		$redactorCondition = 1;
	if(in_array('file', $commentArray))
		$uploadCondition = 1;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
		$foreignCondition = 1;
		if(preg_match('/(smallint)/', $column->type))
			$smallintCondition = 1;
	}
}

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this yii\web\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->modelClass)."\n"; ?>
 * @var $form yii\widgets\ActiveForm
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
use yii\helpers\Html;
<?php echo $uploadCondition ? "use ".ltrim('yii\helpers\Url', '\\').";\n" : '';?>
use yii\widgets\ActiveForm;
<?php echo $redactorCondition ? "use ".ltrim('yii\redactor\widgets\Redactor', '\\').";\n" : '';?>
<?php echo $uploadCondition ? "use ".ltrim($generator->modelClass, '\\').";\n" : '';
foreach ($tableSchema->columns as $column) {
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && preg_match('/(smallint)/', $column->type)) {
		$relationTableName = trim($foreignKeys[$column->name]);
		$relationClassName = $generator->generateClassName($relationTableName);
		echo "use ".$generator->replaceModel($relationClassName).";\n";
	}
}
if($redactorCondition) {?>

$redactorOptions = [
	'imageManagerJson' => ['/redactor/upload/image-json'],
	'imageUpload' => ['/redactor/upload/image'],
	'fileUpload' => ['/redactor/upload/file'],
	'plugins' => ['clips', 'fontcolor','imagemanager']
];
<?php }?>
?>

<?= "<?php "?>$form = ActiveForm::begin([
	'options' => [
		'class' => 'form-horizontal form-label-left',
		<?php echo $uploadCondition ? '' : '//';?>'enctype' => 'multipart/form-data',
	],
	'enableClientValidation' => <?php echo $uploadCondition ? 'false' : 'true';?>,
	'enableAjaxValidation' => <?php echo $uploadCondition ? 'false' : 'false';?>,
	//'enableClientScript' => true,
]); ?>

<?php echo "<?php "?>//echo $form->errorSummary($model);?>

<?php
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->autoIncrement || $column->isPrimaryKey || $column->comment == 'trigger' || in_array($column->name, array('creation_id','modified_id','updated_id','slug')) || ($column->dbType == 'tinyint(1)' && $column->name != 'permission') || $column->name[0] == '_')
		continue;
	if (in_array($column->name, $safeAttributes)) {
		if($column->comment != 'trigger' && !(in_array($column->name, array('creation_id','modified_id','updated_id','slug'))) && !($column->type == 'text' && $column->comment == 'file')) {
			echo "<?php " . $generator->generateActiveField($column->name) . "; ?>\n\n";
		} else if(in_array('file', $commentArray)) {
			echo $generator->generateActiveField($column->name)."\n\n";
		}
	}
}

foreach ($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;
	if($column->dbType == 'tinyint(1)' && !in_array($column->name, ['publish','headline']))
		echo "<?php " . $generator->generateActiveField($column->name) . "; ?>\n\n";
}

foreach ($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;
	if($column->dbType == 'tinyint(1)' && in_array($column->name, ['publish','headline']))
		echo "<?php " . $generator->generateActiveField($column->name) . "; ?>\n\n";
} ?>
<div class="ln_solid"></div>
<div class="form-group">
	<div class="col-md-6 col-sm-9 col-xs-12 col-md-offset-3">
<?= "\t\t<?php echo " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('Create') ?> : <?= $generator->generateString('Update') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']); ?>
	</div>
</div>

<?= "<?php " ?>ActiveForm::end(); ?>