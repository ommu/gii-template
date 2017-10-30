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

<?php echo "<?php ";?>$form=$this->beginWidget('application.components.system.OActiveForm', array(
	'id'=>'<?php echo $this->class2id($this->modelClass);?>-form',
	'enableAjaxValidation'=>true,
	//'htmlOptions' => array('enctype' => 'multipart/form-data')
)); ?>

<?php echo "<?php ";?>//begin.Messages ?>
<div id="ajax-message">
	<?php echo "<?php "; ?>echo $form->errorSummary($model); ?>
</div>
<?php echo "<?php ";?>//begin.Messages ?>

<fieldset>

<?php
//print_r($this->tableSchema->columns);
foreach($this->tableSchema->columns as $column)
{
	if($column->autoIncrement || $column->comment == 'trigger' || $column->dbType == 'tinyint(1)' || (in_array($column->name, array('creation_id','modified_id','updated_id')) && $column->comment != 'trigger'))
		continue;

if(in_array($column->dbType, array('timestamp','datetime','date')) && $column->comment != 'trigger') {?>
<div class="clearfix">
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
		<div class="desc">
		<?php echo "<?php \n";?>
			$model-><?php echo $column->name;?> = !$model->isNewRecord ? (!in_array($model-><?php echo $column->name;?>, array('0000-00-00','1970-01-01')) ? date('d-m-Y', strtotime($model-><?php echo $column->name;?>)) : '') : '';
			//echo $form->textField($model,'<?php echo $column->name;?>');
			$this->widget('application.components.system.CJuiDatePicker',array(
				'model'=>$model,
				'attribute'=>'<?php echo $column->name;?>',
				//'mode'=>'datetime',
				'options'=>array(
					'dateFormat' => 'dd-mm-yy',
				),
				'htmlOptions'=>array(
					'class' => 'span-4',
				 ),
			)); ?>
			<?php echo "<?php "; ?>echo $form->error($model,'<?php echo $column->name;?>'); ?>
			<div class="small-px silent"><?php echo '<?php ';?>echo Yii::t('phrase', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vitae laoreet metus. Integer eros augue, viverra at lectus vel, dignissim sagittis erat. ');?></div>
		</div>
	</div>

<?php } else if($column->dbType == 'text') {?>
	<div class="clearfix">
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
		<div class="desc">
			<?php echo "<?php \n"?>
			//echo $form->textArea($model,'<?php echo $column->name;?>',array('rows'=>6, 'cols'=>50));
			$this->widget('application.vendor.yiiext.imperavi-redactor-widget.ImperaviRedactorWidget', array(
				'model'=>$model,
				'attribute'=>'<?php echo $column->name;?>',
				'options'=>array(
					'buttons'=>array(
						'html', 'formatting', '|', 
						'bold', 'italic', 'deleted', '|',
						'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
						'link', '|',
					),
				),
				'plugins' => array(
					'fontcolor' => array('js' => array('fontcolor.js')),
					'table' => array('js' => array('table.js')),
					'fullscreen' => array('js' => array('fullscreen.js')),
				),
			)); ?>
			<?php echo "<?php "; ?>echo $form->error($model,'<?php echo $column->name;?>'); ?>
			<div class="small-px silent"><?php echo '<?php ';?>echo Yii::t('phrase', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vitae laoreet metus. Integer eros augue, viverra at lectus vel, dignissim sagittis erat. ');?></div>
		</div>
	</div>

<?php } else {?>
	<div class="clearfix">
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
		<div class="desc">
			<?php echo "<?php echo ".$this->generateActiveField($this->modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php "; ?>echo $form->error($model,'<?php echo $column->name;?>'); ?>
			<div class="small-px silent"><?php echo '<?php ';?>echo Yii::t('phrase', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vitae laoreet metus. Integer eros augue, viverra at lectus vel, dignissim sagittis erat. ');?></div>
		</div>
	</div>

<?php }
}
//print_r($this->tableSchema->columns);
foreach($this->tableSchema->columns as $column)
{
if($column->dbType == 'tinyint(1)') {?>
	<div class="clearfix publish">
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
		<div class="desc">
			<?php echo "<?php echo \$form->checkBox(\$model,'{$column->name}'); ?>\n"; ?>
			<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php "; ?>echo $form->error($model,'<?php echo $column->name;?>'); ?>
		</div>
	</div>

<?php }
}
?>
	<?php echo '<?php'?> /*
	<div class="submit clearfix">
		<label>&nbsp;</label>
		<div class="desc">
			<?php echo "<?php "; ?>echo CHtml::submitButton(\$model->isNewRecord ? Yii::t('phrase', 'Create') : Yii::t('phrase', 'Save'), array('onclick' => 'setEnableSave()')); ?>
		</div>
	</div>
	*/?>

</fieldset>

<div class="dialog-content">
</div>
<div class="dialog-submit">
	<?php echo "<?php "; ?>echo CHtml::submitButton($model->isNewRecord ? Yii::t('phrase', 'Create') : Yii::t('phrase', 'Save') ,array('onclick' => 'setEnableSave()')); ?>
	<?php echo "<?php "; ?>echo CHtml::button(Yii::t('phrase', 'Cancel'), array('id'=>'closed')); ?>
</div>
<?php echo "<?php "; ?>$this->endWidget(); ?>