<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;
$foreignKeys = $this->foreignKeys($table->foreignKeys);

echo "<?php\n"; ?>
/**
 * <?php echo $inflector->pluralize($this->class2name($modelClass)); ?> (<?php echo $this->class2id($modelClass); ?>)
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
$label=$inflector->pluralize($this->class2name($modelClass));
echo "\t\$this->breadcrumbs=array(
	\t'$label'=>array('manage'),
	\t\$model->$breadcrumbRelationAttribute,
\t);\n";
?>
?>

<?php echo "<?php ";?>//begin.Messages ?>
<?php echo "<?php \n";?>
if(Yii::app()->user->hasFlash('success')) 
	echo $this->flashMessage(Yii::app()->user->getFlash('success'), 'success'); 
?>
<?php echo "<?php ";?>//end.Messages ?>

<?php if($this->generateCode['view']['dialog']):?>
<div class="dialog-content">
<?php else: ?>
<div class="box">
<?php endif; ?>
<?php echo "<?php"; ?> $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
<?php
foreach($columns as $name=>$column) {
	$commentArray = explode(',', $column->comment);
	if($column->isPrimaryKey) {
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$column->name',\n";
		echo "\t\t\t'value'=>\$model->$column->name,\n";
		echo "\t\t),\n";
	} else if($column->type==='boolean' || $column->dbType == 'tinyint(1)') {
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$column->name',\n";
		if($column->dbType == 'tinyint(1)' && $column->defaultValue === null) {
			echo "\t\t\t'value'=>\$model->$column->name ? \$model->$column->name : '-',\n";
		} else {
			$publish = 'Publish,Unpublish';
			if($column->comment != '')
				$publish = $column->comment;
			$publishArray = explode(',', $publish);
			echo "\t\t\t'value'=>\$model->$column->name ? Yii::t('phrase', '$publishArray[0]') : Yii::t('phrase', '$publishArray[1]'),\n";
			//echo "\t\t\t'value'=>\$model->$column->name ? CHtml::image(Yii::app()->theme->baseUrl.'/images/icons/publish.png') : CHtml::image(Yii::app()->theme->baseUrl.'/images/icons/unpublish.png'),\n";
			//echo "\t\t\t'type'=>'raw',\n";
		}
		echo "\t\t),\n";
	} else if($column->isForeignKey || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
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
		if($relationName == 'category')
			$publicAttribute = $column->name;

		$relationAttribute = join('->', array($relationName, $relationAttribute));

		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$publicAttribute',\n";
		echo "\t\t\t'value'=>\$model->$relationAttribute ? \$model->$relationAttribute : '-',\n";
		echo "\t\t),\n";
	} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$column->name',\n";
		if(in_array($column->dbType, array('timestamp','datetime'))) {
			echo "\t\t\t'value'=>!in_array(\$model->$column->name, array('0000-00-00 00:00:00','1970-01-01 00:00:00','0002-12-02 07:07:12','-0001-11-30 00:00:00')) ? \$this->dateFormat(\$model->$column->name) : '-',\n";
		} else {
			echo "\t\t\t'value'=>!in_array(\$model->$column->name, array('0000-00-00','1970-01-01','0002-12-02','-0001-11-30')) ? \$this->dateFormat(\$model->$column->name, 'full', false) : '-',\n";
		}
		echo "\t\t),\n";
	} else {
		$translateCondition = 0;
		$publicAttribute = $column->name;
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $column->name.'_i';
			$relationName = $this->i18nRelation($column->name);
			$translateCondition = 1;
		}
		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$publicAttribute',\n";
if($translateCondition)
	echo "\t\t\t'value'=>\$model->$column->name ? \$model->{$relationName}->message : '-',\n";
else {
	if($column->dbType == 'text' && in_array('file', $commentArray)) {
		if($this->uploadPathSubfolder)
			echo "\t\t\t'value'=>\$model->$column->name ? CHtml::link(\$model->$column->name, join('/', array(Yii::app()->request->baseUrl, $modelClass::getUploadPath(false), \$model->$table->primaryKey, \$model->$column->name), array('target'=>'_blank')) : '-',\n";
		else
			echo "\t\t\t'value'=>\$model->$column->name ? CHtml::link(\$model->$column->name, join('/', array(Yii::app()->request->baseUrl, $modelClass::getUploadPath(false), \$model->$column->name), array('target'=>'_blank')) : '-',\n";
	} else
		echo "\t\t\t'value'=>\$model->$publicAttribute ? \$model->$column->name : '-',\n";
}
if((in_array($column->dbType, array('text')) && (in_array('file', $commentArray) || in_array('redactor', $commentArray))) && $column->name != 'slug')
	echo "\t\t\t'type' => 'raw',\n";
		echo "\t\t),\n";
	}
}
?>
	),
)); ?>
<?php if($this->generateCode['view']['dialog']):?>
</div>
<div class="dialog-submit">
	<?php echo "<?php ";?>echo CHtml::button(Yii::t('phrase', 'Close'), array('id'=>'closed')); ?>
</div>
<?php else: ?>
</div>
<?php endif; ?>