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
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (opensource.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($this->modifiedStatus):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php endif; ?>
 * @link <?php echo $this->linkSource."\n";?>
 *
 */

<?php
$nameColumn=$this->guessNameColumn($this->tableSchema->columns);
$label=$inflector->pluralize($this->class2name($this->modelClass));
echo "\t\$this->breadcrumbs=array(
	\t'$label'=>array('manage'),
	\t\$model->{$nameColumn},
\t);\n";
?>
?>

<?php echo "<?php ";?>//begin.Messages ?>
<?php echo "<?php \n";?>
if(Yii::app()->user->hasFlash('success')) 
	echo Utility::flashSuccess(Yii::app()->user->getFlash('success')); 
?>
<?php echo "<?php ";?>//end.Messages ?>

<?php echo "<?php"; ?> $this->widget('application.libraries.core.components.system.FDetailView', array(
	'data'=>$model,
	'attributes'=>array(
<?php

//print_r($this->tableSchema->columns);
foreach($this->tableSchema->columns as $name=>$column)
	if($column->name == $this->tableSchema->primaryKey) {
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$name',\n";
		echo "\t\t\t'value'=>\$model->$name,\n";
		echo "\t\t),\n";
	} else if($column->type==='boolean' || $column->dbType == 'tinyint(1)') {
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$name',\n";
		if($column->dbType == 'tinyint(1)' && $column->defaultValue === null) {
			echo "\t\t\t'value'=>\$model->$name ? \$model->$name : '-',\n";
		} else {
			echo "\t\t\t//'value'=>\$model->$name == '1' ? Yii::t('phrase', 'Yes') : Yii::t('phrase', 'No'),\n";
			echo "\t\t\t'value'=>\$model->$name == '1' ? CHtml::image(Yii::app()->theme->baseUrl.'/images/icons/publish.png') : CHtml::image(Yii::app()->theme->baseUrl.'/images/icons/unpublish.png'),\n";
			echo "\t\t\t'type'=>'raw',\n";
		}
		echo "\t\t),\n";
	} else if($column->isForeignKey == '1' || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id')))) {
		$relationArray = explode('_',$column->name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_search';
		$relationAttribute = 'displayname';
		if($column->isForeignKey == '1') {
			$relationName = setRelationName($name, true);
			if($relationName == 'cat')
				$relationName = 'category';
			$publicAttribute = $relationName.'_search';
			$relationAttribute = 'column_name_relation';
		}
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$name',\n";	
		echo "\t\t\t'value'=>\$model->$name ? \$model->{$relationName}->{$relationAttribute} : '-',\n";
		echo "\t\t),\n";		
	} else if($column->dbType == 'text') {
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$name',\n";
		echo "\t\t\t'value'=>\$model->$name ? \$model->$name : '-',\n";
		echo "\t\t\t//'value'=>\$model->$name ? CHtml::link(\$model->$name, Yii::app()->request->baseUrl.'/public/visit/'.\$model->$name, array('target' => '_blank')) : '-',\n";
		echo "\t\t\t'type'=>'raw',\n";
		echo "\t\t),\n";
	} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
		if(in_array($column->dbType, array('timestamp','datetime'))) {
			echo "\t\tarray(\n";
			echo "\t\t\t'name'=>'$name',\n";
			echo "\t\t\t'value'=>!in_array(\$model->$name, array('0000-00-00 00:00:00','1970-01-01 00:00:00')) ? Utility::dateFormat(\$model->$name, true) : '-',\n";
			echo "\t\t),\n";
		} else {
			echo "\t\tarray(\n";
			echo "\t\t\t'name'=>'$name',\n";
			echo "\t\t\t'value'=>!in_array(\$model->$name, array('0000-00-00','1970-01-01')) ? Utility::dateFormat(\$model->$name) : '-',\n";
			echo "\t\t),\n";			
		}
	} else {
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$name',\n";	
		echo "\t\t\t'value'=>\$model->$name ? \$model->$name : '-',\n";
		echo "\t\t),\n";
	}
?>
	),
)); ?>

<div class="box">
</div>
<div class="dialog-content">
</div>
<div class="dialog-submit">
	<?php echo "<?php ";?>echo CHtml::button(Yii::t('phrase', 'Close'), array('id'=>'closed')); ?>
</div>