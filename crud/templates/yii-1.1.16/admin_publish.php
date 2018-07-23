<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

$action = '';
foreach($this->generateAction as $key=>$val) {
	if(preg_match('/('.$fileName.')/', $val['file']))
		$action = $key;
}

$publish = $column[$action]->comment;
if($action == 'publish' && $column[$action]->comment == '')
	$publish = 'Publish,Unpublish';
if($action == 'headline' && $column[$action]->comment == '')
	$publish = 'Headline,Unheadline';
$publishArray = explode(',', $publish);

echo "<?php\n"; ?>
/**
 * <?php echo $inflector->pluralize($this->class2name($modelClass)); ?> (<?php echo $this->class2id($modelClass); ?>)
 * @var $this <?php echo $this->getControllerClass()."\n"; ?>
 * @var $model <?php echo $this->getModelClass()."\n"; ?>
 * @var $form CActiveForm
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (www.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($this->useModified):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php endif; ?>
 * @link <?php echo $this->linkSource."\n";?>
 *
 */

<?php
$label=$inflector->pluralize($this->class2name($modelClass));
echo "\t\$this->breadcrumbs=array(
	\t'$label'=>array('manage'),
	\t\$model->{$breadcrumbRelationAttribute}=>array('view','id'=>\$model->{$table->primaryKey}),
	\t'".ucwords($action)."',
\t);\n";
?>
?>

<?php echo "<?php ";?>$form=$this->beginWidget('application.libraries.yii-traits.system.OActiveForm', array(
	'id'=>'<?php echo $this->class2id($modelClass);?>-form',
	'enableAjaxValidation'=>true,
)); ?>

	<div class="dialog-content">
		<?php echo "<?php ";?>echo $model-><?php echo $action;?> == 1 ? Yii::t('phrase', 'Are you sure you want to <?php echo strtolower($publishArray[1]);?> this item?') : Yii::t('phrase', 'Are you sure you want to <?php echo strtolower($publishArray[0]);?> this item?')?>
	</div>
	<div class="dialog-submit">
		<?php echo "<?php ";?>echo CHtml::submitButton($title, array('onclick' => 'setEnableSave()')); ?>
		<?php echo "<?php ";?>echo CHtml::button(Yii::t('phrase', 'Cancel'), array('id'=>'closed')); ?>
	</div>
	
<?php echo "<?php ";?>$this->endWidget(); ?>