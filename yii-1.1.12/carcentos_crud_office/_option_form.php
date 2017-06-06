<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php\n"; ?>
/* @var $this <?php echo $this->getControllerClass(); ?> */
/* @var $model <?php echo $this->getModelClass(); ?> */
/* @var $form CActiveForm */

	$cs = Yii::app()->getClientScript();
$js=<<<EOP
	$('form[name="gridoption"] :checkbox').live('click', function(){
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
	$ukey = md5(uniqid(mt_rand(), true));
	$cs->registerScript($ukey, $js);
?>

<?php echo "<?php echo CHtml::beginForm(Yii::app()->createUrl(\$this->route), 'GET', array(
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
	<?php echo "<?php foreach(\$columns as \$val): ?>";?>
	<li>
		<?php echo "<?php echo CHtml::checkBox('GridColumn['.\$val.']'); ?>\n"; ?>
		<?php echo "<?php echo CHtml::label(\$val, 'GridColumn_'.\$val); ?>\n";?>
	</li>
	<?php echo "<?php endforeach; ?>";?>
</ul>
<div class="clear"></div>
<?php echo "<?php echo CHtml::endForm(); ?>\n"; ?>
