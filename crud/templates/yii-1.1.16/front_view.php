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
$label = $inflector->singularize($feature);
echo "\t\$this->breadcrumbs=array(
	\tYii::t('phrase', '$module'),
	\tYii::t('phrase', '$label'),
	\t\$model->$breadcrumbRelationAttribute,
\t);\n";
?>
?>

<div class="box">
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
		$publishCondition = 0;
		$publish = $column->comment;
		if($column->name == 'publish' && $column->comment == '') {
			$publishCondition = 1;
			$publish = 'Publish,Unpublish';
		}
		if($column->name == 'headline' && $column->comment == '')
			$publish = 'Headline,Unheadline';
		$publishArray = explode(',', $publish);

		echo "\t\tarray(\n";
		echo "\t\t\t'name'=>'$column->name',\n";
		if($publish == '') {
			echo "\t\t\t'value'=>\$this->parseYesNo(\$model->$column->name),\n";
			echo "\t\t\t'type'=>'raw',\n";
		} else {
			if(in_array($column->name, array('publish','headline')) || $column->comment != '') {
				if($this->generateAction[$column->name]['generate']) {
					if($publishCondition)
						echo "\t\t\t'value'=>\$this->quickAction(Yii::app()->controller->createUrl('$column->name', array('id'=>\$model->$table->primaryKey)), \$model->$column->name),\n";
					else {
						if($column->comment[0] == '"') {
							$functionName = ucfirst($inflector->singularize($inflector->id2camel($column->name, '_')));
							echo "\t\t\t'value'=>\$this->quickAction(Yii::app()->controller->createUrl('$column->name', array('id'=>\$model->$table->primaryKey)), \$model->$column->name, $modelClass::get$functionName()),\n";
						} else
							echo "\t\t\t'value'=>\$this->quickAction(Yii::app()->controller->createUrl('$column->name', array('id'=>\$model->$table->primaryKey)), \$model->$column->name, '$publish'),\n";
					}
				} else
					echo "\t\t\t'value'=>\$model->$column->name ? Yii::t('phrase', '$publishArray[0]') : Yii::t('phrase', '$publishArray[1]'),\n";
			}
			echo "\t\t\t'type'=>'raw',\n";
		}
		//echo "\t\t\t'value'=>\$model->$column->name ? CHtml::image(Yii::app()->theme->baseUrl.'/images/icons/publish.png') : CHtml::image(Yii::app()->theme->baseUrl.'/images/icons/unpublish.png'),\n";
		echo "\t\t),\n";
	} else if($column->isForeignKey || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
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
		if($relationName == 'category' || $smallintCondition)
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
			echo "\t\t\t'value'=>\$model->$column->name ? CHtml::link(\$model->$column->name, join('/', array(Yii::app()->request->baseUrl, $modelClass::getUploadPath(false), \$model->$table->primaryKey, \$model->$column->name), array('target'=>'_blank'))) : '-',\n";
		else
			echo "\t\t\t'value'=>\$model->$column->name ? CHtml::link(\$model->$column->name, join('/', array(Yii::app()->request->baseUrl, $modelClass::getUploadPath(false), \$model->$column->name), array('target'=>'_blank'))) : '-',\n";
	} else
		echo "\t\t\t'value'=>\$model->$publicAttribute ? \$model->$column->name : '-',\n";
}
if((in_array($column->dbType, array('text')) && (in_array('file', $commentArray) || in_array('redactor', $commentArray))) && $column->name != 'slug')
	echo "\t\t\t'type'=>'raw',\n";
		echo "\t\t),\n";
	}
}
?>
	),
)); ?>
</div>