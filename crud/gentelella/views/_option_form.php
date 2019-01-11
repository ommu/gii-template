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
use yii\helpers\Url;

$js = <<<JS
	$('form[name="gridoption"] :checkbox').click(function() {
		var url = $('form[name="gridoption"]').attr('action');
		var data = $('form[name="gridoption"] :checked').serialize();
		$.ajax({
			url: url,
			data: data,
			success: function(response) {
				//$("#w0").yiiGridView("applyFilter");
				//$.pjax({container: '#w0'});
				return false;
			}
		});
	});
JS;
	$this->registerJs($js, \app\components\View::POS_READY);
?>

<div class="grid-form">
	<?= "<?php echo " ?>Html::beginForm(Url::to(['/'.$route]), 'get', ['name' => 'gridoption']);
		$columns = [];
		foreach($model->templateColumns as $key => $column) {
			if($key == '_no')
				continue;
			$columns[$key] = $key;
		}
	?>
		<ul>
		<?= "\t<?php " ?>foreach($columns as $key => $val): ?> 
			<li>
		<?= "\t\t<?php echo " ?>Html::checkBox(sprintf("GridColumn[%s]", $key), in_array($key, $gridColumns) ? true : false, ['id'=>'GridColumn_'.$key]); ?>
		<?= "\t\t<?php echo " ?>Html::label($model->getAttributeLabel($val), 'GridColumn_'.$val); ?>
			</li>
		<?= "\t<?php " ?>endforeach; ?>
		</ul>
	<?= "<?php echo " ?>Html::endForm(); ?>
</div>