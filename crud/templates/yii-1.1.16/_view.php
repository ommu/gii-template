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
 * @var $data <?php echo $this->getModelClass()."\n"; ?>
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (opensource.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($this->modifiedStatus):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php endif; ?>
 * @link <?php echo $this->linkSource."\n";;?>
 *
 */
?>

<div class="view">
<?php
	echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$this->tableSchema->primaryKey}')); ?>:</b>\n";
	echo "\t<?php echo CHtml::link(CHtml::encode(\$data->{$this->tableSchema->primaryKey}), array('view', 'id'=>\$data->{$this->tableSchema->primaryKey})); ?>\n\t<br />\n\n";
	$count=0;
	foreach($this->tableSchema->columns as $column)
	{
		if($column->isPrimaryKey)
			continue;
			
		echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$column->name}')); ?>:</b>\n";
		if($column->isForeignKey == '1' || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id')))) {
			$arrayName = explode('_', $column->name);
			$cName = 'displayname';
			if($column->isForeignKey == '1')
				$cName = 'column_name_relation';
			$cRelation = $arrayName[0];
			if($cRelation == 'cat')
				$cRelation = 'category';
			echo "\t<?php echo CHtml::encode(\$data->{$cRelation}->{$cName}); ?>\n\t<br />\n\n";
		} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
			if(in_array($column->dbType, array('timestamp','datetime')))
				echo "\t<?php echo CHtml::encode(Utility::dateFormat(\$data->{$column->name}, true)); ?>\n\t<br />\n\n";
			else
				echo "\t<?php echo CHtml::encode(Utility::dateFormat(\$data->{$column->name})); ?>\n\t<br />\n\n";	
		} else 
			echo "\t<?php echo CHtml::encode(\$data->{$column->name}); ?>\n\t<br />\n\n";
	}
?>
</div>