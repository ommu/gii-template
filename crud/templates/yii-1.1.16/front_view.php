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
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (www.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($this->useModified):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php endif; ?>
 * @link <?php echo $this->linkSource."\n";?>
 *
 */

<?php
$modelClass = $this->modelClass;
if(preg_match('/Core/', $modelClass))
	$modelClass = preg_replace('(Core)', '', $modelClass);
else
	$modelClass = preg_replace('(Ommu)', '', $modelClass);
$label=$inflector->pluralize($this->class2name($modelClass));
$nameColumn=$this->getTableAttribute($this->tableSchema->columns);
echo "\t\$this->breadcrumbs=array(
	\t'$label'=>array('manage'),
	\t\$model->{$nameColumn},
\t);\n";
?>
?>

<div class="box">
<?php echo "<?php"; ?> $this->widget('application.libraries.core.components.system.FDetailView', array(
	'data'=>$model,
	'attributes'=>array(
<?php

//print_r($this->tableSchema->columns);
$tableSchema = $this->getTableSchema();
$primaryKey = $tableSchema->primaryKey;
foreach($this->tableSchema->columns as $name=>$column)
	if($name == $this->tableSchema->primaryKey) {
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
	} else if($column->isForeignKey == '1' || (in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
		$arrayName = explode('_',$name);
		$relationName = $arrayName[0];
		$columnName = 'displayname';
		if($column->isForeignKey == '1')
			$columnName = 'column_name_relation';
		if($name == 'tag_id')
			$columnName = 'body';
		if($relationName == 'cat')
			$relationName = 'category';
		if($name == 'member_id') {
			$relationName = 'member_view';
			$columnName = 'member_name';
		}
		$publicAttribute = $relationName.'_search';
		if($relationName == 'category')
			$publicAttribute = $name;

		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$publicAttribute',\n";
if($name == 'tag_id')
	echo "\t\t\t'value'=>\$model->$publicAttribute ? str_replace('-', ' ', \$model->$relationName->$columnName) : '-',\n";
else
	echo "\t\t\t'value'=>\$model->$publicAttribute ? \$model->$relationName->$columnName : '-',\n";
		echo "\t\t),\n";
	} else if($column->dbType == 'text') {
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$name',\n";
if($column->comment == 'file') {
	if($this->uploadPathSubfolderStatus):
		$CHtml = "Yii::app()->request->baseUrl.'/{$this->uploadPathDirectorySource}/'.\$model->$primaryKey.'/'.\$model->$name";
	else:
		$CHtml = "Yii::app()->request->baseUrl.'/{$this->uploadPathDirectorySource}/'.\$model->$name";
	endif;
	echo "\t\t\t'value'=>\$model->$name ? CHtml::link(\$model->$name, $CHtml, array('target' => '_blank')) : '-',\n";
} else
	echo "\t\t\t'value'=>\$model->$name ? \$model->$name : '-',\n";
		echo "\t\t\t'type'=>'raw',\n";
		echo "\t\t),\n";
	} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
		if(in_array($column->dbType, array('timestamp','datetime'))) {
			echo "\t\tarray(\n";
			echo "\t\t\t'name'=>'$name',\n";
			echo "\t\t\t'value'=>!in_array(\$model->$name, array('0000-00-00 00:00:00','1970-01-01 00:00:00','0002-12-02 07:07:12','-0001-11-30 00:00:00')) ? Utility::dateFormat(\$model->$name, true) : '-',\n";
			echo "\t\t),\n";
		} else {
			echo "\t\tarray(\n";
			echo "\t\t\t'name'=>'$name',\n";
			echo "\t\t\t'value'=>!in_array(\$model->$name, array('0000-00-00','1970-01-01','0002-12-02','-0001-11-30')) ? Utility::dateFormat(\$model->$name) : '-',\n";
			echo "\t\t),\n";
		}
	} else {
		$i18n = 0;
		$columnName = $name;
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)) {
			$columnName = $columnName.'_i';
			$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : (preg_match('/(desc|description)/', $name) ? ($name != 'description' ? 'description' : $name.'Rltn') : $name.'Rltn');
			$i18n = 1;
		}
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$columnName',\n";
if($i18n)
	echo "\t\t\t'value'=>\$model->$name ? \$model->{$publicAttributeRelation}->message : '-',\n";
else
	echo "\t\t\t'value'=>\$model->$columnName ? \$model->$name : '-',\n";
		echo "\t\t),\n";
	}
?>
	),
)); ?>
</div>