<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $this->pluralize($this->class2name($this->modelClass)); ?> (<?php echo $this->class2id($this->modelClass); ?>)
 * @var $this <?php echo $this->getControllerClass()."\n"; ?>
 * @var $model <?php echo $this->getModelClass()."\n"; ?>
 * @var $form CActiveForm
 * version: 0.0.1
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (opensource.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @link http://opensource.ommu.co
 * @contact (+62)856-299-4114
 *
 */
?>

<?php echo "<?php \$form=\$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl(\$this->route),
	'method'=>'get',
)); ?>\n"; ?>
	<ul>
<?php foreach($this->tableSchema->columns as $column): ?>
<?php
	$field=$this->generateInputField($this->modelClass,$column);
	if(strpos($field,'password')!==false)
		continue;
?>
		<li>
			<?php echo "<?php echo \$model->getAttributeLabel('{$column->name}'); ?><br/>\n"; ?>
			<?php echo "<?php echo \$form->textField(\$model,'{$column->name}'); ?><br/>\n"; ?>
			<?php /* echo "<?php echo ".$this->generateActiveField($this->modelClass,$column)."; ?>\n"; */ ?>
		</li>

<?php endforeach; ?>
		<li class="submit">
			<?php echo "<?php echo CHtml::submitButton(Yii::t('phrase', 'Search')); ?>\n"; ?>
		</li>
	</ul>
<?php echo "<?php \$this->endWidget(); ?>\n"; ?>
