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
	'action'=>Yii::app()->createUrl(\$this->route),
	'method'=>'get',
)); ?>\n"; ?>
	<ul>
<?php foreach($this->tableSchema->columns as $column): ?>
<?php
	$field=$this->generateInputField($this->modelClass,$column);
	if(strpos($field,'password')!==false)
		continue;
?>
		<li>
			<?php echo "<?php echo \$model->getAttributeLabel('{$column->name}'); ?><br/>\n"; ?>
			<?php echo "<?php echo ".$this->generateActiveField($this->modelClass,$column)."; ?>\n"; ?>
		</li>

<?php endforeach; ?>
		<li class="submit">
			<?php echo "<?php echo CHtml::submitButton('Search'); ?>\n"; ?>
		</li>
	</ul>
	<div class="clear"></div>
<?php echo "<?php \$this->endWidget(); ?>\n"; ?>
