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
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (opensource.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @link http://opensource.ommu.co
 *
 */

	$cs = Yii::app()->getClientScript();
$js=<<<EOP
	$('form[name="gridoption"] :checkbox').click(function(){
		var url = $('form[name="gridoption"]').attr('action');
		$.ajax({
			url: url,
			data: $('form[name="gridoption"] :checked').serialize(),
			success: function(response) {
				$.fn.yiiGridView.update('<?php echo $this->class2id($this->modelClass); ?>-grid', {
					data: $('form[name="gridoption"]').serialize()
				});
				return false;
			}
		});
	});
EOP;
	$cs->registerScript('grid-option', $js, CClientScript::POS_END);
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
