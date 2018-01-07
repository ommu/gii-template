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
 * @link http://opensource.ommu.co
 *
 */

<?php
$label=$this->class2name($this->modelClass);
echo "\t\$this->breadcrumbs=array(
	\t'{$inflector->pluralize($label)}'=>array('manage'),
	\t'Manage',
\t);\n";
?>
	$this->menu=array(
		array(
			'label' => Yii::t('phrase', 'Filter'),
			'url' => array('javascript:void(0);'),
			'itemOptions' => array('class' => 'search-button'),
			'linkOptions' => array('title' => Yii::t('phrase', 'Filter')),
		),
		array(
			'label' => Yii::t('phrase', 'Grid Options'),
			'url' => array('javascript:void(0);'),
			'itemOptions' => array('class' => 'grid-button'),
			'linkOptions' => array('title' => Yii::t('phrase', 'Grid Options')),
		),
	);

?>

<?php echo "<?php ";?>//begin.Search ?>
<div class="search-form">
<?php echo "<?php ";?>$this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div>
<?php echo "<?php ";?>//end.Search ?>

<?php echo "<?php ";?>//begin.Grid Option ?>
<div class="grid-form">
<?php echo "<?php ";?>$this->renderPartial('_option_form',array(
	'model'=>$model,
	'gridColumns'=>Utility::getActiveDefaultColumns($columns),
)); ?>
</div>
<?php echo "<?php ";?>//end.Grid Option ?>

<div id="partial-<?php echo $this->class2id($this->modelClass); ?>">
	<?php echo "<?php ";?>//begin.Messages ?>
	<div id="ajax-message">
	<?php echo "<?php \n";?>
	if(Yii::app()->user->hasFlash('error'))
		echo Utility::flashError(Yii::app()->user->getFlash('error'));
	if(Yii::app()->user->hasFlash('success'))
		echo Utility::flashSuccess(Yii::app()->user->getFlash('success'));
	?>
	</div>
	<?php echo "<?php ";?>//begin.Messages ?>

	<div class="boxed">
		<?php echo "<?php ";?>//begin.Grid Item ?>
		<?php echo "<?php"; ?> 
			$columnData   = $columns;
			array_push($columnData, array(
				'header' => Yii::t('phrase', 'Options'),
				'class'=>'CButtonColumn',
				'buttons' => array(
					'view' => array(
						'label' => Yii::t('phrase', 'View <?php echo $inflector->singularize($label);?>'),
						'imageUrl' => false,
						'options' => array(
							'class' => 'view',
						),
						'url' => 'Yii::app()->controller->createUrl(\'view\',array(\'id\'=>$data->primaryKey))'),
					'update' => array(
						'label' => Yii::t('phrase', 'Update <?php echo $inflector->singularize($label);?>'),
						'imageUrl' => false,
						'options' => array(
							'class' => 'update'
						),
						'url' => 'Yii::app()->controller->createUrl(\'edit\',array(\'id\'=>$data->primaryKey))'),
					'delete' => array(
						'label' => Yii::t('phrase', 'Delete <?php echo $inflector->singularize($label);?>'),
						'imageUrl' => false,
						'options' => array(
							'class' => 'delete'
						),
						'url' => 'Yii::app()->controller->createUrl(\'delete\',array(\'id\'=>$data->primaryKey))')
				),
				'template' => '{view}|{update}|{delete}',
			));

			$this->widget('application.libraries.core.components.system.OGridView', array(
				'id'=>'<?php echo $this->class2id($this->modelClass); ?>-grid',
				'dataProvider'=>$model->search(),
				'filter'=>$model,
				'afterAjaxUpdate' => 'reinstallDatePicker',
				'columns' => $columnData,
				'pager' => array('header' => ''),
			));
		?>
		<?php echo "<?php ";?>//end.Grid Item ?>
	</div>
</div>