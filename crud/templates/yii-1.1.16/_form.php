<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;
?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $inflector->pluralize($this->class2name($this->modelClass)); ?> (<?php echo $this->class2id($this->modelClass); ?>)
 * @var $this <?php echo $this->getControllerClass()."\n"; ?>
 * @var $model <?php echo $this->getModelClass()."\n"; ?>
 * @var $form CActiveForm
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (opensource.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @link http://opensource.ommu.co
 *
 */
?>

<?php echo "<?php ";?>$form=$this->beginWidget('application.libraries.core.components.system.OActiveForm', array(
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
	if($column->autoIncrement || $column->comment == 'trigger' || $column->type==='boolean' || ($column->dbType == 'tinyint(1)' && $column->defaultValue !== null) || (in_array($column->name, array('creation_id','modified_id','updated_id')) && $column->comment != 'trigger'))
		continue;
?>
	<div class="form-group row">
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column, true)."; ?>\n"; ?>
		<div class="col-lg-8 col-md-9 col-sm-12">
			<?php echo "<?php ".$this->generateActiveField($this->modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php "; ?>echo $form->error($model, '<?php echo $column->name;?>'); ?>
			<div class="small-px silent"><?php echo '<?php ';?>echo Yii::t('phrase', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vitae laoreet metus. Integer eros augue, viverra at lectus vel, dignissim sagittis erat. ');?></div>
		</div>
	</div>

<?php
}
//print_r($this->tableSchema->columns);
foreach($this->tableSchema->columns as $column)
{
if($column->type==='boolean' || ($column->dbType == 'tinyint(1)' && $column->defaultValue !== null)) {?>
	<div class="form-group row publish">
		<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column, true)."; ?>\n"; ?>
		<div class="col-lg-8 col-md-9 col-sm-12">
			<?php echo "<?php ".$this->generateActiveField($this->modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php echo ".$this->generateActiveLabel($this->modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php "; ?>echo $form->error($model, '<?php echo $column->name;?>'); ?>
		</div>
	</div>

<?php }
}
?>
	<?php echo '<?php'?> /*
	<div class="form-group row submit">
		<label class="col-form-label col-lg-4 col-md-3 col-sm-12">&nbsp;</label>
		<div class="col-lg-8 col-md-9 col-sm-12">
			<?php echo "<?php "; ?>echo CHtml::submitButton($model->isNewRecord ? Yii::t('phrase', 'Create') : Yii::t('phrase', 'Save'), array('onclick' => 'setEnableSave()')); ?>
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