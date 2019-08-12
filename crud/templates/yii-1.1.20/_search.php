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
 * @author Putra Sudaryanto <putra@ommu.co>
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

<?php echo "<?php ";?>$form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>
	<ul>
<?php foreach($columns as $column) {
	$field=$this->generateInputField($modelClass,$column);
	if(strpos($field,'password')!==false || $column->isPrimaryKey || $column->autoIncrement || $column->type==='boolean' || $column->dbType == 'tinyint(1)')
		continue;
		
	$commentArray = explode(',', $column->comment);
	$publicAttribute = $column->name;
	if($column->isForeignKey) {
		$relationName = $this->setRelation($column->name, true);
		$publicAttribute = $relationName.'_search';
		if(preg_match('/(smallint)/', $column->dbType))
			$publicAttribute = $column->name;
	} else if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
		$relationName = $this->setRelation($column->name, true);
		$publicAttribute = $relationName.'_search';
		if($column->name == 'tag_id')
			$publicAttribute = $relationName.'_i';
	} else {
		if(in_array('trigger[delete]', $commentArray))
			$publicAttribute = $column->name.'_i';
	}
?>
		<li>
			<?php echo "<?php echo \$model->getAttributeLabel('{$publicAttribute}'); ?>\n"; ?>
			<?php echo "<?php ".$this->generateActiveField($modelClass,$column,false)."; ?>\n"; ?>
		</li>

<?php }
foreach($columns as $column) {
	if($column->type==='boolean' || $column->dbType == 'tinyint(1)') {?>
		<li>
			<?php echo "<?php echo \$model->getAttributeLabel('{$column->name}'); ?>\n"; ?>
			<?php echo "<?php ".$this->generateActiveField($modelClass,$column,false)."; ?>\n"; ?>
		</li>

<?php }
} ?>
		<li class="submit">
			<?php echo "<?php ";?>echo CHtml::submitButton(Yii::t('phrase', 'Search')); ?>
		</li>
	</ul>
<?php echo "<?php ";?>$this->endWidget(); ?>