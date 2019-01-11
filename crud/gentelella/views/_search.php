<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);

$tableSchema = $generator->tableSchema;
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);

$getFunctionCondition = 0;
foreach ($tableSchema->columns as $column) {
	if($column->comment != '' && $column->comment[0] == '"')
		$getFunctionCondition = 1;
}

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this app\components\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->searchModelClass)."\n"; ?>
 * @var $form app\components\ActiveForm
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
use app\components\ActiveForm;
<?php echo $getFunctionCondition ? "use ".ltrim($generator->modelClass).";\n" : '';?>
<?php foreach ($tableSchema->columns as $column) {
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && preg_match('/(smallint)/', $column->type)) {
		$relationTableName = trim($foreignKeys[$column->name]);
		$relationClassName = $generator->generateClassName($relationTableName);
		echo "use ".$generator->replaceModel($relationClassName).";\n";
	}
}?>
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search search-form">

	<?= "<?php " ?>$form = ActiveForm::begin([
		'action' => ['index'],
		'method' => 'get',
<?php if ($generator->enablePjax): ?>
		'options' => [
			'data-pjax' => 1
		],
<?php endif; ?>
	]); ?>

<?php
foreach($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;
	if($column->autoIncrement || $column->isPrimaryKey || $column->phpType === 'boolean' || ($column->dbType == 'tinyint(1)' && $column->name != 'permission'))
		continue;
		
	echo "\t\t<?php ".$generator->generateActiveSearchField($column->name).";?>\n\n";
}
foreach($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;
	if ($column->phpType === 'boolean' || ($column->dbType == 'tinyint(1)' && !in_array($column->name, ['publish','headline','permission'])))
		echo "\t\t<?php ".$generator->generateActiveSearchField($column->name).";?>\n\n";
}
foreach($tableSchema->columns as $column) {
	if ($column->dbType == 'tinyint(1)' && $column->name == 'headline')
		echo "\t\t<?php ".$generator->generateActiveSearchField($column->name).";?>\n\n";
}
foreach($tableSchema->columns as $column) {
	if ($column->dbType == 'tinyint(1)' && $column->name == 'publish')
		echo "\t\t<?php ".$generator->generateActiveSearchField($column->name).";?>\n\n";
}
?>
		<div class="form-group">
			<?= "<?php echo " ?>Html::submitButton(<?= $generator->generateString('Search') ?>, ['class' => 'btn btn-primary']) ?>
			<?= "<?php echo " ?>Html::resetButton(<?= $generator->generateString('Reset') ?>, ['class' => 'btn btn-default']) ?>
		</div>

	<?= "<?php " ?>ActiveForm::end(); ?>

</div>