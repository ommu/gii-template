<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this yii\web\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->searchModelClass)."\n"; ?>
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
?>

<div class="search-form">
	<?= "<?php " ?>$form = ActiveForm::begin([
		'action' => ['index'],
		'method' => 'get',
	]); ?>
<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
	//if (++$count < 6) {
		echo "\t\t<?= " . $generator->generateActiveSearchField($attribute) . " ?>\n\n";
	/*
	} else {
		echo "\t\t<?php // echo " . $generator->generateActiveSearchField($attribute) . " ?>\n\n";
	}
	*/
}
?>
		<div class="form-group">
			<?= "<?php echo " ?>Html::submitButton(<?= $generator->generateString('Search') ?>, ['class' => 'btn btn-primary']) ?>
			<?= "<?php echo " ?>Html::resetButton(<?= $generator->generateString('Reset') ?>, ['class' => 'btn btn-default']) ?>
		</div>
	<?= "<?php " ?>ActiveForm::end(); ?>
</div>
