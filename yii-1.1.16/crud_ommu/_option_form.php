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

<?php echo "<?php echo CHtml::beginForm(Yii::app()->createUrl(\$this->route), 'get', array(
	'name' => 'gridoption',
));
\$columns   = array();
\$exception = array('id');
foreach(\$model->metaData->columns as \$key => \$val) {
	if(!in_array(\$key, \$exception)) {
		\$columns[\$key] = \$key;
	}
}
?>\n"; ?>
<ul>
	<?php echo "<?php foreach(\$columns as \$val): ?>\n";?>
	<li>
		<?php echo "<?php echo CHtml::checkBox('GridColumn['.\$val.']'); ?>\n"; ?>
		<?php echo "<?php echo CHtml::label(\$val, 'GridColumn_'.\$val); ?>\n";?>
	</li>
	<?php echo "<?php endforeach; ?>\n";?>
</ul>
<?php echo "<?php echo CHtml::endForm(); ?>\n"; ?>
