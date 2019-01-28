<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);

$functionLabel = ucwords(Inflector::pluralize($generator->shortLabel($modelClass)));

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this app\components\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->modelClass)."\n"; ?>
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

use yii\helpers\Url;

$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($functionLabel) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model-><?= $generator->getNameAttribute() ?>, 'url' => ['view', <?= $urlParams ?>]];
$this->params['breadcrumbs'][] = <?= $generator->generateString('Update') ?>;

$this->params['menu']['content'] = [
	['label' => <?= $generator->generateString('Back To Manage') ?>, 'url' => Url::to(['index']), 'icon' => 'table'],
	['label' => <?= $generator->generateString('Detail') ?>, 'url' => Url::to(['view', <?= $urlParams ?>]), 'icon' => 'eye'],
	['label' => <?= $generator->generateString('Update') ?>, 'url' => Url::to(['update', <?= $urlParams ?>]), 'icon' => 'pencil'],
	['label' => <?= $generator->generateString('Delete') ?>, 'url' => Url::to(['delete', <?= $urlParams ?>]), 'htmlOptions' => ['data-confirm'=><?= $generator->generateString('Are you sure you want to delete this item?') ?>, 'data-method'=>'post'], 'icon' => 'trash'],
];
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-update">

<?= "<?php echo " ?>$this->render('_form', [
	'model' => $model,
]); ?>

</div>