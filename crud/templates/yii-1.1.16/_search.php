<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

/* 
* set name relation with underscore
*/
function setRelationName($names, $column=false) {
	$patterns = array();
	$patterns[0] = '(_ommu)';
	$patterns[1] = '(_core)';
	
	if($column == false) {
		$char=range("A","Z");
		foreach($char as $val) {
			if(strpos($names, $val) !== false) {
				$names = str_replace($val, '_'.strtolower($val), $names);
			}
		}
	} else
		$names = rtrim($names, 'id');

	$return = trim(preg_replace($patterns, '', $names), '_');
	$return = array_map('strtolower', explode('_', $return));
	//print_r($return);

	if(count($return) != 1)
		return end($return);
	else {
		if(is_array($return))
			return implode('', $return);
		else
			return $return;
	}
}
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

<?php echo "<?php ";?>$form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>
	<ul>
<?php foreach($this->tableSchema->columns as $column):
	$field=$this->generateInputField($this->modelClass,$column);
	if(strpos($field,'password')!==false || $column->isPrimaryKey || $column->autoIncrement || $column->type==='boolean' || $column->dbType == 'tinyint(1)')
		continue;
		
	$columnName = $column->name;
	if($column->isForeignKey == '1') {
		$relationName = setRelationName($column->name, true);
		if($relationName == 'cat')
			$relationName = 'category';
		$columnName = $relationName.'_search';
	} else if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
		$relationArray = explode('_',$column->name);
		$relationName = $relationArray[0];
		$columnName = $relationName.'_search';
	}
?>
		<li>
			<?php echo "<?php echo \$model->getAttributeLabel('{$columnName}'); ?>\n"; ?>
			<?php echo "<?php ".$this->generateActiveField($this->modelClass,$column,false)."; ?>\n"; ?>
		</li>

<?php endforeach;
foreach($this->tableSchema->columns as $column):
	if($column->type==='boolean' || $column->dbType == 'tinyint(1)'): ?>
		<li>
			<?php echo "<?php echo \$model->getAttributeLabel('{$column->name}'); ?>\n"; ?>
			<?php echo "<?php ".$this->generateActiveField($this->modelClass,$column,false)."; ?>\n"; ?>
		</li>

<?php endif;
endforeach; ?>
		<li class="submit">
			<?php echo "<?php ";?>echo CHtml::submitButton(Yii::t('phrase', 'Search')); ?>
		</li>
	</ul>
<?php echo "<?php ";?>$this->endWidget(); ?>
