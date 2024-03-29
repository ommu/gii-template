<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php\n"; ?>
	/* @var $this <?php echo $this->getControllerClass(); ?> */
	/* @var $model <?php echo $this->getModelClass(); ?> */

<?php
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\$this->breadcrumbs=array(
	'$label'=>array('adminmanage'),
	Yii::t('site', 'Kelola $label'),
);\n";
?>

	$cs = Yii::app()->getClientScript();
$js=<<<EOP
	$('.search-form form').submit(function(){
		$.fn.yiiGridView.update('<?php echo $this->class2id($this->modelClass); ?>-grid', {
			data: $(this).serialize()
		});
		return false;
	});
EOP;
	$cs->registerScript('search', $js, CClientScript::POS_END);

	$this->menu=array(
		array(
			'label' => 'Filter', 
			'url' => array('javascript:void(0);'),
			'itemOptions' => array('class' => 'filter-button'),
			'linkOptions' => array('title' => 'Filter'),
		),
		array(
			'label' => 'Grid Options', 
			'url' => array('javascript:void(0);'),
			'itemOptions' => array('class' => 'grid-button'),
			'linkOptions' => array('title' => 'Grid Options'),
		),
	);

?>

<div id="partial-<?php echo $this->class2id($this->modelClass); ?>">
	<?php echo "<? //begin.Search ?>\n";?>
	<div class="search-form">
	<?php echo "<?php \$this->renderPartial('_search', array(
		'model'=>\$model,
	)); ?>\n"; ?>
	</div>
	<?php echo "<? //end.Search ?>\n";?>

	<?php echo "<? //begin.Grid Option ?>\n";?>
	<div class="grid-option">
	<?php echo "<?php \$this->renderPartial('_option_form', array(
		'model'=>\$model,
	)); ?>\n"; ?>
	</div>
	<?php echo "<? //end.Grid Option ?>\n";?>

	<?php 
	echo "<? //begin.Messages ?>\n";
	echo "<div id=\"ajax-message\">\n";
	echo "<?php\n";
	echo "	if(Yii::app()->user->hasFlash('error'))
			echo Utility::flashError(Yii::app()->user->getFlash('error'));
		if(Yii::app()->user->hasFlash('success'))
			echo Utility::flashSuccess(Yii::app()->user->getFlash('success'));
	?>\n";
	echo "</div>\n";
	echo "<? //begin.Messages ?>\n";?>

	<?php echo "<? //begin.Grid Item ?>\n";?>
	<?php echo "<?php"; ?> 
	$columnData = $columns;
	array_push($columnData, array(
		'header' => 'Option',
		'class'=>'CButtonColumn',
		'buttons' => array(
			'view' => array(
				'label' => 'view',
				'options' => array(
					//'rel' => 500, 
					'class' => 'view'
				),
				//'click' => 'dialogUpdate',
				'url' => 'Yii::app()->controller->createUrl("adminview", array("id"=>$data->primaryKey))'),
			'update' => array(
				'label' => 'update',
				'options' => array(
					//'rel' => 500, 
					'class' => 'update'
				),
				//'click' => 'dialogUpdate',
				'url' => 'Yii::app()->controller->createUrl("adminedit", array("id"=>$data->primaryKey))'),
			'delete' => array(
				'label' => 'delete',
				'options' => array(
					'class' => 'delete',
					'rel' => 350, 
				),
				'click' => 'dialogUpdate',
				'url' => 'Yii::app()->controller->createUrl("admindelete", array("id"=>$data->primaryKey))')
		),
		'template' => '{view}&nbsp;{update}&nbsp;{delete}',
	));

	$this->widget('application.libraries.core.components.system.BGridView', array(
		'id'=>'<?php echo $this->class2id($this->modelClass); ?>-grid',
		'dataProvider'=>$model->search(),
		'filter'=>$model,
		'columns' => $columnData,
		'pager' => array('header' => ''),
	));

	?>
	<?php echo "<? //end.Grid Item ?>\n";?>
</div>