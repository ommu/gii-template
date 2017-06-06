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
 * version: 0.0.1
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (opensource.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @link http://opensource.ommu.co
 * @contact (+62)856-299-4114
 *
 */
?>

<?php echo "<?php \$form=\$this->beginWidget('application.components.system.OActiveForm', array(
	'id'=>'".$this->class2id($this->modelClass)."-form',
	'enableAjaxValidation'=>true,
	//'htmlOptions' => array('enctype' => 'multipart/form-data')
)); ?>\n"; ?>

<?php 
echo "<?php //begin.Messages ?>\n";?>
<div id="ajax-message">
	<?php echo "<?php echo \$form->errorSummary(\$model); ?>\n"; ?>
</div>
<?php 
echo "<?php //begin.Messages ?>\n";?>

<fieldset>

<?php
//print_r($this->tableSchema->columns);
foreach($this->tableSchema->columns as $column)
{
	if($column->autoIncrement)
		continue;
?>
<?php if($column->dbType == 'tinyint(1)') {?>
	<div class="clearfix publish">
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
		<div class="desc">
			<?php echo "<?php echo \$form->checkBox(\$model,'{$column->name}'); ?>\n"; ?>
			<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php echo \$form->error(\$model,'{$column->name}'); ?>\n"; ?>
			<?php echo "<?php /*<div class=\"small-px silent\"></div>*/?>\n";?>
		</div>
	</div>

<?php } else if(in_array($column->dbType, array('timestamp','datetime','date')) && $column->comment != 'trigger') {?>
	<div class="clearfix">
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
		<div class="desc">
			<?php echo "<?php\n"; ?>
			<?php echo "\$model->{$column->name} = !\$model->isNewRecord ? (!in_array(\$model->{$column->name}, array('0000-00-00','1970-01-01')) ? date('d-m-Y', strtotime(\$model->{$column->name})) : '') : '';\n"; ?>
			<?php echo "//echo \$form->textField(\$model,'{$column->name}');\n"; ?>
			<?php echo "\$this->widget('application.components.system.CJuiDatePicker',array(\n"; ?>
				<?php echo "'model'=>\$model,\n"; ?>
				<?php echo "'attribute'=>'{$column->name}',\n"; ?>
				<?php echo "//'mode'=>'datetime',\n"; ?>
				<?php echo "'options'=>array(\n"; ?>
					<?php echo "'dateFormat' => 'dd-mm-yy',\n"; ?>
				<?php echo "),\n"; ?>
				<?php echo "'htmlOptions'=>array(\n"; ?>
					<?php echo "'class' => 'span-4',\n"; ?>
				 <?php echo "),\n"; ?>
			<?php echo ")); ?>\n"; ?>
			<?php echo "<?php echo \$form->error(\$model,'{$column->name}'); ?>\n"; ?>
			<?php echo "<?php /*<div class=\"small-px silent\"></div>*/?>\n";?>
		</div>
	</div>

<?php } else {?>
	<div class="clearfix">
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
		<div class="desc">
			<?php echo "<?php echo ".$this->generateActiveField($this->modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php echo \$form->error(\$model,'{$column->name}'); ?>\n"; ?>
			<?php echo "<?php /*<div class=\"small-px silent\"></div>*/?>\n";?>
		</div>
	</div>

<?php }?>
<?php
}
?>
	<div class="submit clearfix">
		<label>&nbsp;</label>
		<div class="desc">
			<?php echo "<?php echo CHtml::submitButton(\$model->isNewRecord ? Yii::t('phrase', 'Create') : Yii::t('phrase', 'Save'), array('onclick' => 'setEnableSave()')); ?>\n"; ?>
		</div>
	</div>

</fieldset>
<?php echo "<?php /*\n"; ?>
<?php echo "<div class=\"dialog-content\">\n"; ?>
<?php echo "</div>\n"; ?>
<?php echo "<div class=\"dialog-submit\">\n"; ?>
<?php echo "\t<?php echo CHtml::submitButton(\$model->isNewRecord ? Yii::t('phrase', 'Create') : Yii::t('phrase', 'Save') ,array('onclick' => 'setEnableSave()')); ?>\n"; ?>
<?php echo "\t<?php echo CHtml::button(Yii::t('phrase', 'Cancel'), array('id'=>'closed')); ?>\n"; ?>
<?php echo "</div>\n"; ?>
<?php echo "*/?>\n"; ?>
<?php echo "<?php \$this->endWidget(); ?>\n"; ?>


