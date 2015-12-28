<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $this->pluralize($this->class2name($this->modelClass)); ?> (<?php echo $this->class2id($this->modelClass); ?>)
 * @var $this <?php echo $this->getControllerClass()."\n"; ?>
 * @var $model <?php echo $this->getModelClass()."\n"; ?>
 * @var $form CActiveForm
 *
 * @author Putra Sudaryanto <putra.sudaryanto@gmail.com>
 * @copyright Copyright (c) 2015 Ommu Platform (ommu.co)
 * @link http://company.ommu.co
 * @contect (+62)856-299-4114
 *
 */

<?php
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\t\$this->breadcrumbs=array(
	\t'$label'=>array('manage'),
	\t'Publish',
\t);\n";
?>
?>

<?php echo "<?php \$form=\$this->beginWidget('application.components.system.OActiveForm', array(
	'id'=>'".$this->class2id($this->modelClass)."-form',
	'enableAjaxValidation'=>true,
	//'htmlOptions' => array('enctype' => 'multipart/form-data')
)); ?>\n"; ?>

	<div class="dialog-content">
		<?php echo "<?php echo \$model->publish == 1 ? Phrase::trans(282,0) : Phrase::trans(281,0)?>\n";?>
		<?php echo "<?php //echo \$model->actived == 1 ? Phrase::trans(280,0) : Phrase::trans(279,0)?>\n";?>
		<?php echo "<?php //echo \$model->enabled == 1 ? Phrase::trans(286,0) : Phrase::trans(285,0)?>\n";?>
		<?php echo "<?php //echo \$model->status == 1 ? Phrase::trans(294,0) : Phrase::trans(293,0)?>\n";?>
	</div>
	<div class="dialog-submit">
		<?php echo "<?php echo CHtml::submitButton(\$title, array('onclick' => 'setEnableSave()')); ?>\n";?>
		<?php echo "<?php echo CHtml::button(Phrase::trans(174,0), array('id'=>'closed')); ?>\n";?>
	</div>
	
<?php echo "<?php \$this->endWidget(); ?>\n"; ?>