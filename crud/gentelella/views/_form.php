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
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}
$tableSchema = $generator->tableSchema;
$columns = $tableSchema->columns;

$textareaCondition = 0;
$datepickerCondition = 0;
foreach ($columns as $key => $column) {
    if($column->type === 'text')
        $textareaCondition = 1;
    if(in_array($column->dbType, array('timestamp','datetime','date')) && $column->comment != 'trigger')
        $datepickerCondition = 1;
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
use yii\widgets\ActiveForm;
<?php if($datepickerCondition && $generator->useJuiDatePicker): ?>
use yii\jui\DatePicker;
<?php endif; ?>
<?php if($textareaCondition): ?>
use yii\redactor\widgets\Redactor;

$redactorOptions = [
    'imageManagerJson' => ['/redactor/upload/image-json'],
    'imageUpload'     => ['/redactor/upload/image'],
    'fileUpload'       => ['/redactor/upload/file'],
    'plugins'         => ['clips', 'fontcolor','imagemanager']
];
<?php endif; ?>
?>

<?= "<?php "?>$form = ActiveForm::begin([
    'options' => [
        'class' => 'form-horizontal form-label-left',
        //'enctype' => 'multipart/form-data',
    ],
    'enableClientValidation' => true,
    'enableAjaxValidation'   => false,
    //'enableClientScript'     => true,
]); ?>

<?php echo "<?php "?>//echo $form->errorSummary($model);?>

<?php 
//echo '<pre>';
//print_r($columns);
foreach ($columns as $key => $column) {
    if($column->autoIncrement || $column->isPrimaryKey)
        continue;
        
    if (in_array($key, $safeAttributes)) {
        if($column->comment != 'trigger' && !in_array($column->name, ['slug']) && !(in_array($column->name, array('creation_id','modified_id','updated_id')) && $column->comment != 'trigger')) {
            if(!in_array($column->name, ['publish','headline']))
                echo "<?php echo " . $generator->generateActiveField($key) . "; ?>\n\n";
        }
    }
}
foreach ($columns as $key => $column) {
    if(in_array($column->name, ['publish','headline']))
        echo "<?php echo " . $generator->generateActiveField($key) . "; ?>\n\n";
} ?>
<div class="ln_solid"></div>
<div class="form-group">
    <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
<?= "\t\t<?php echo " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('Create') ?> : <?= $generator->generateString('Update') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']); ?>
    </div>
</div>

<?= "<?php " ?>ActiveForm::end(); ?>