<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

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
$label = $inflector->singularize($feature);
$moduleAction = $this->ControllerID != 'admin' ? 'o/admin/manage' : 'manage';
if(!$this->generateAction['manage']['generate'] && $this->generateAction['update']['generate']) {
echo "\t\$this->breadcrumbs=array(
	\tYii::t('phrase', '$module')=>array('$moduleAction'),
	\tYii::t('phrase', '$label')=>array('index'),
	\tYii::t('phrase', 'Delete'),
\t);\n";
} else {
echo "\t\$this->breadcrumbs=array(
	\tYii::t('phrase', '$module')=>array('$moduleAction'),\n";
if($this->ControllerID != 'admin') 
	echo "\t\tYii::t('phrase', '$label')=>array('manage'),\n";
echo "\t\t\$model->{$breadcrumbRelationAttribute}=>array('view','id'=>\$model->{$table->primaryKey}),
	\tYii::t('phrase', 'Delete'),
\t);\n";
}
?>
?>

<?php echo "<?php ";?>$form=$this->beginWidget('CActiveForm', array(
	'id'=>'<?php echo $this->class2id($modelClass);?>-form',
	'enableAjaxValidation'=>true,
)); ?>

	<div class="dialog-content">
		<?php echo "<?php ";?>echo Yii::t('phrase', 'Are you sure you want to delete this item?');?>
	</div>
	<div class="dialog-submit">
		<?php echo "<?php ";?>echo CHtml::submitButton(Yii::t('phrase', 'Delete'), array('onclick' => 'setEnableSave()')); ?>
		<?php echo "<?php ";?>echo CHtml::button(Yii::t('phrase', 'Cancel'), array('id'=>'closed')); ?>
	</div>
	
<?php echo "<?php ";?>$this->endWidget(); ?>
