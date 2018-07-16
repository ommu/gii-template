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
?>

<?php echo "<?php ";?>$form=$this->beginWidget('application.libraries.yii-traits.system.OActiveForm', array(
	'id'=>'<?php echo $this->class2id($modelClass);?>-form',
	'enableAjaxValidation'=>true,
	/*
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	'htmlOptions' => array(
		'enctype' => 'multipart/form-data',
	),
	*/
)); ?>

<?php echo "<?php ";?>//begin.Messages ?>
<div id="ajax-message">
	<?php echo "<?php "; ?>echo $form->errorSummary($model); ?>
</div>
<?php echo "<?php ";?>//begin.Messages ?>

<fieldset>

<?php
//print_r($columns);
foreach($columns as $column)
{
	if($column->autoIncrement || $column->comment == 'trigger' || $column->name == 'slug' || $column->type==='boolean' || ($column->dbType == 'tinyint(1)' && $column->defaultValue !== null) || (in_array($column->name, array('creation_id','modified_id','updated_id')) && $column->comment != 'trigger'))
		continue;
	$columnName = $column->name;
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray))
		$columnName = $columnName.'_i';
?>
	<div class="form-group row">
		<?php echo "<?php echo ".$this->generateActiveLabel($modelClass,$column, true)."; ?>\n"; ?>
		<div class="col-lg-8 col-md-9 col-sm-12">
			<?php echo "<?php ".$this->generateActiveField($modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php "; ?>echo $form->error($model, '<?php echo $columnName;?>'); ?>
			<div class="small-px silent"><?php echo '<?php ';?>echo Yii::t('phrase', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vitae laoreet metus. Integer eros augue, viverra at lectus vel, dignissim sagittis erat. ');?></div>
		</div>
	</div>

<?php
}
//print_r($columns);
foreach($columns as $column)
{
if($column->type==='boolean' || ($column->dbType == 'tinyint(1)' && $column->defaultValue !== null)) {?>
	<div class="form-group row publish">
		<?php echo "<?php echo ".$this->generateActiveLabel($modelClass,$column, true)."; ?>\n"; ?>
		<div class="col-lg-8 col-md-9 col-sm-12">
			<?php echo "<?php ".$this->generateActiveField($modelClass,$column)."; ?>\n"; ?>
			<?php echo "<?php echo ".$this->generateActiveLabel($modelClass,$column)."; ?>\n"; ?>
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
	<?php echo "<?php "; ?>echo CHtml::submitButton($model->isNewRecord ? Yii::t('phrase', 'Create') : Yii::t('phrase', 'Save'), array('onclick' => 'setEnableSave()')); ?>
	<?php echo "<?php "; ?>echo CHtml::button(Yii::t('phrase', 'Cancel'), array('id'=>'closed')); ?>
</div>
<?php echo "<?php "; ?>$this->endWidget(); ?>