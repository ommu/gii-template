<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php\n"; ?>
/* @var $this <?php echo $this->getControllerClass(); ?> */
/* @var $model <?php echo $this->getModelClass(); ?> */
/* @var $form CActiveForm */
?>

<?php echo "<?php \$form=\$this->beginWidget('CActiveForm', array(
	'id'=>'".$this->class2id($this->modelClass)."-form',
	'enableAjaxValidation'=>true,
	//'htmlOptions' => array('enctype' => 'multipart/form-data')
)); ?>\n"; ?>

	<?php echo "<div id=\"ajax-message\"><?php echo \$form->errorSummary(\$model); ?></div>\n"; ?>

	<fieldset>
<?php
foreach($this->tableSchema->columns as $column)
{
	if($column->autoIncrement)
		continue;
?>
	<div>
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
		<div class="desc">
			<?php echo "<?php echo ".$this->generateActiveField($this->modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php echo \$form->error(\$model,'{$column->name}'); ?>\n"; ?>
			<?php echo "<?php /*<div class=\"small-px silent\"></div>*/?>\n";?>
		</div>
		<div class="clear"></div>
	</div>

<?php
}
?>
	<div class="submit">
		<label>&nbsp;</label>
		<div class="desc">
			<?php echo "<?php echo CHtml::submitButton(\$model->isNewRecord ? 'Create' : 'Save'); ?>\n"; ?>
		</div>
		<div class="clear"></div>
	</div>
	</fieldset>
<?php echo "<?php \$this->endWidget(); ?>\n"; ?>

