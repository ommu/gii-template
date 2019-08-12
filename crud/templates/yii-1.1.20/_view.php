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
 * @var $data <?php echo $this->getModelClass()."\n"; ?>
 *
 * @author Putra Sudaryanto <putra@ommu.co>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (www.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($this->useModified):?>
 * @link <?php echo $this->linkSource."\n";?>
<?php endif; ?>
 * @link <?php echo $this->linkSource."\n";?>
 *
 */
?>

<div class="view">
<?php
	echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$table->primaryKey}')); ?>:</b>\n";
	echo "\t<?php echo CHtml::link(CHtml::encode(\$data->{$table->primaryKey}), array('view', 'id'=>\$data->{$table->primaryKey})); ?>\n\t<br />\n\n";
	$count=0;
	foreach($columns as $column) {
		$commentArray = explode(',', $column->comment);

		if($column->isPrimaryKey)
			continue;

		echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$column->name}')); ?>:</b>\n";
		if($column->isForeignKey || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
			$smallintCondition = 0;
			if(preg_match('/(smallint)/', $column->dbType))
				$smallintCondition = 1;
			$relationName = $this->setRelation($column->name, true);
			$publicAttribute = $relationName.'_search';
			$relationAttribute = 'displayname';
			if($column->name == 'member_id')
				$relationAttribute = 'member_name';
			else if($column->name == 'tag_id') {
				$publicAttribute = $relationName.'_i';
				$relationAttribute = 'body';
			}
			if($column->isForeignKey) {
				$relationTableName = trim($foreignKeys[$column->name]);
				$relationAttribute = $this->tableRelationAttribute($relationTableName, '->');
			}
			if($smallintCondition)
				$publicAttribute = $column->name;
			
			echo "\t<?php echo CHtml::encode(\$data->{$relationName}->{$relationAttribute}); ?>\n\t<br />\n\n";
		} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
			if(in_array($column->dbType, array('timestamp','datetime')))
				echo "\t<?php echo CHtml::encode(\$this->dateFormat(\$data->{$column->name})); ?>\n\t<br />\n\n";
			else
				echo "\t<?php echo CHtml::encode(\$this->dateFormat(\$data->{$column->name}, 'full', false)); ?>\n\t<br />\n\n";	
		} else 
			echo "\t<?php echo CHtml::encode(\$data->{$column->name}); ?>\n\t<br />\n\n";
	}
?>
</div>