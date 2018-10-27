<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);

$tableSchema = $generator->tableSchema;

if(!empty($tableSchema->primaryKey))
	$primaryKey = $tableSchema->primaryKey[0];
else
	$primaryKey = key($tableSchema->columns);

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this yii\web\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->searchModelClass)."\n"; ?>
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
use yii\widgets\ActiveForm;
?>

<div class="search-form">
	<?= "<?php " ?>$form = ActiveForm::begin([
		'action' => ['index'],
		'method' => 'get',
	]); ?>
<?php
$count = 0;
foreach($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;
	if($column->isPrimaryKey || $column->autoIncrement || ($column->dbType == 'tinyint(1)' && $column->name != 'permission'))
		continue;
		
	echo "\t\t<?php echo ".$generator->generateActiveSearchField($column->name).";?>\n\n";
}
foreach($tableSchema->columns as $column) {
	if($column->name == 'publish')
		continue;
	if ($column->phpType === 'boolean' || $column->dbType == 'tinyint(1)')
		echo "\t\t<?php echo ".$generator->generateActiveSearchField($column->name).";?>\n\n";
}
foreach($tableSchema->columns as $column) {
	if ($column->phpType === 'boolean' || $column->dbType == 'tinyint(1)' && $column->name == 'publish')
		echo "\t\t<?php echo ".$generator->generateActiveSearchField($column->name).";?>\n\n";
}
?>
		<div class="form-group">
			<?= "<?php echo " ?>Html::submitButton(<?= $generator->generateString('Search') ?>, ['class' => 'btn btn-primary']) ?>
			<?= "<?php echo " ?>Html::resetButton(<?= $generator->generateString('Reset') ?>, ['class' => 'btn btn-default']) ?>
		</div>
	<?= "<?php " ?>ActiveForm::end(); ?>
</div>
