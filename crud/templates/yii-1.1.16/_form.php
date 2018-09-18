<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

$uploadCondition = 0;
$htmlOptionCondition = 0;
$formDialogCondition = 0;
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if($column->dbType == 'text' && in_array('file', $commentArray))
		$uploadCondition = 1;
endforeach;

if($uploadCondition) {
	$htmlOptionCondition = 1;
	if($this->generateAction['create']['dialog'] || $this->generateAction['update']['dialog'])
		$htmlOptionCondition = 1;
}
if($this->generateAction['create']['dialog'] || $this->generateAction['update']['dialog'])
	$formDialogCondition = 1;

$labelClass = $formDialogCondition ? 'col-lg-4 col-md-4 col-sm-12' : 'col-lg-3 col-md-3 col-sm-12';
$boxFieldClass = $formDialogCondition ? 'col-lg-8 col-md-8 col-sm-12' : 'col-lg-6 col-md-9 col-sm-12';

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
<?php if($htmlOptionCondition):?>
	'htmlOptions' => array(
<?php if($uploadCondition):?>
		'enctype' => 'multipart/form-data',
<?php if($this->generateAction['create']['dialog'] || $this->generateAction['update']['dialog']):?>
		'on_post' => '',
<?php endif;
endif; ?>
	),
	/*
<?php else:?>
	/*
	'htmlOptions' => array(
		'enctype' => 'multipart/form-data',
		'on_post' => '',
	),
<?php endif;?>
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	*/
)); ?>

<?php if(!$this->generateAction['create']['dialog'] || !$this->generateAction['update']['dialog']):?>
	<?php echo "<?php ";?>//begin.Messages ?>
	<div id="ajax-message">
		<?php echo "<?php "; ?>echo $form->errorSummary($model); ?>
	</div>
	<?php echo "<?php ";?>//begin.Messages ?>

<?php endif;
if($this->generateAction['create']['dialog'] || $this->generateAction['update']['dialog']):?>
<div class="dialog-content">
<?php endif; ?>
	<fieldset>

<?php if($this->generateAction['create']['dialog'] || $this->generateAction['update']['dialog']):?>
		<?php echo "<?php ";?>//begin.Messages ?>
		<div id="ajax-message">
			<?php echo "<?php "; ?>echo $form->errorSummary($model); ?>
		</div>
		<?php echo "<?php ";?>//begin.Messages ?>

<?php endif; ?>
<?php foreach($columns as $column) {
	if($column->autoIncrement || $column->comment == 'trigger' || $column->name == 'slug' || $column->type==='boolean' || ($column->dbType == 'tinyint(1)' && ($column->defaultValue !== null && $column->name != 'permission')) || (in_array($column->name, array('creation_id','modified_id','updated_id')) && $column->comment != 'trigger'))
		continue;

	$commentArray = explode(',', $column->comment);
	$publicAttribute = $column->name;
	if(in_array('trigger[delete]', $commentArray))
		$publicAttribute = $column->name.'_i';
	else if($column->name == 'tag_id') {
		$relationName = $this->setRelation($column->name, true);
		$publicAttribute = $relationName.'_i';
	}
?>
		<div class="form-group row">
<?php if($column->name == 'license'):?>
			<label class="col-form-label col-lg-3 col-md-3 col-sm-12">
				<?php echo "<?php "; ?>echo $model->getAttributeLabel('<?php echo $column->name;?>');?> <span class="required">*</span><br/>
				<span><?php echo "<?php "; ?>echo Yii::t('phrase', 'Format: XXXX-XXXX-XXXX-XXXX');?></span>
			</label>
<?php else:?>
			<?php echo "<?php echo ".$this->generateActiveLabel($modelClass,$column, true)."; ?>\n"; ?>
<?php endif; ?>
			<div class="<?php echo $boxFieldClass;?>">
<?php if($column->name == 'permission'):?>
				<div class="small-px"><?php echo "<?php "; ?>echo Yii::t('phrase', 'Select whether or not you want to let the public (visitors that are not logged-in) to view the following sections of your social network. In some cases (such as Profiles, Blogs, and Albums), if you have given them the option, your users will be able to make their pages private even though you have made them publically viewable here. For more permissions settings, please visit the General Settings page.');?></div>
<?php endif; ?>
				<?php echo "<?php ".$this->generateActiveField($modelClass,$column)."; ?>\n"; ?>
				<?php echo "<?php "; ?>echo $form->error($model, '<?php echo $publicAttribute;?>'); ?>
<?php if($column->name == 'license'):?>
				<div class="small-px"><?php echo '<?php ';?>echo Yii::t('phrase', 'Enter the your license key that is provided to you when you purchased this plugin. If you do not know your license key, please contact support team.');?></div>
<?php elseif(!in_array($column->name, array('permission','meta_description','meta_keyword'))):?>
				<div class="small-px"><?php echo '<?php ';?>echo Yii::t('phrase', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.');?></div>
<?php endif;?>
			</div>
		</div>

<?php }
foreach($columns as $column) {
if($column->name == 'permission')
	continue;

if($column->type==='boolean' || ($column->dbType == 'tinyint(1)' && $column->defaultValue !== null)) {?>
		<div class="form-group row publish">
			<?php echo "<?php echo ".$this->generateActiveLabel($modelClass,$column, true)."; ?>\n"; ?>
			<div class="<?php echo $boxFieldClass;?>">
				<?php echo "<?php ".$this->generateActiveField($modelClass,$column)."; ?>\n"; 
				if($column->comment[0] != '"') {?>
				<?php echo "<?php echo ".$this->generateActiveLabel($modelClass,$column)."; ?>\n"; }?>
				<?php echo "<?php "; ?>echo $form->error($model, '<?php echo $column->name;?>'); ?>
			</div>
		</div>

<?php }
}?>
<?php if(!$this->generateAction['create']['dialog'] || !$this->generateAction['update']['dialog']):?>
		<div class="form-group row submit">
			<label class="col-form-label <?php echo $labelClass;?>">&nbsp;</label>
			<div class="<?php echo $boxFieldClass;?>">
				<?php echo "<?php "; ?>echo CHtml::submitButton($model->isNewRecord ? Yii::t('phrase', 'Create') : Yii::t('phrase', 'Save'), array('onclick' => 'setEnableSave()')); ?>
			</div>
		</div>

<?php endif; ?>
	</fieldset>
<?php if($this->generateAction['create']['dialog'] || $this->generateAction['update']['dialog']):?>
</div>
<div class="dialog-submit">
	<?php echo "<?php "; ?>echo CHtml::submitButton($model->isNewRecord ? Yii::t('phrase', 'Create') : Yii::t('phrase', 'Save'), array('onclick' => 'setEnableSave()')); ?>
	<?php echo "<?php "; ?>echo CHtml::button(Yii::t('phrase', 'Cancel'), array('id'=>'closed')); ?>
</div>
<?php endif; ?>
<?php echo "<?php "; ?>$this->endWidget(); ?>