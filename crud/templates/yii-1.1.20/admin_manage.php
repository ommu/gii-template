<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

echo "<?php\n"; ?>
/**
 * <?php echo $inflector->pluralize($this->class2name($modelClass)); ?> (<?php echo $this->class2id($modelClass); ?>)
 * @var $this <?php echo $this->getControllerClass()."\n"; ?>
 * @var $model <?php echo $this->getModelClass()."\n"; ?>
 *
 * @author Putra Sudaryanto <putra@ommu.co>
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
$manageAction = $feature != '' ? 'o/admin/manage' : 'manage';
echo "\t\$this->breadcrumbs=array(
	\tYii::t('phrase', '$module')=>array('$manageAction'),\n";
if($feature != '')
	echo "\t\tYii::t('phrase', '$label')=>array('manage'),\n";
echo "\t\tYii::t('phrase', 'Manage'),
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
<?php echo "<?php ";?>$this->renderPartial('_search', array(
	'model'=>$model,
)); ?>
</div>
<?php echo "<?php ";?>//end.Search ?>

<?php echo "<?php ";?>//begin.Grid Option ?>
<div class="grid-form">
<?php echo "<?php ";?>$this->renderPartial('_option_form', array(
	'model'=>$model,
	'gridColumns'=>$this->activeDefaultColumns($columns),
)); ?>
</div>
<?php echo "<?php ";?>//end.Grid Option ?>

<div id="partial-<?php echo $this->class2id($modelClass); ?>">
	<?php echo "<?php ";?>//begin.Messages ?>
	<div id="ajax-message">
	<?php echo "<?php\n";?>
	if(Yii::app()->user->hasFlash('error'))
		echo $this->flashMessage(Yii::app()->user->getFlash('error'), 'error');
	if(Yii::app()->user->hasFlash('success'))
		echo $this->flashMessage(Yii::app()->user->getFlash('success'), 'success');
	?>
	</div>
	<?php echo "<?php ";?>//begin.Messages ?>

	<div class="boxed">
		<?php echo "<?php ";?>//begin.Grid Item ?>
		<?php echo "<?php"; ?> 
			$columnData   = $columns;
			array_push($columnData, array(
				'header' => Yii::t('phrase', 'Options'),
				'class' => 'CButtonColumn',
				'buttons' => array(
					'view' => array(
						'label' => Yii::t('phrase', 'Detail'),
						'imageUrl' => Yii::app()->params['grid-view']['buttonImageUrl'],
						'options' => array(
							'class' => 'view',
							'title' => Yii::t('phrase', 'Detail <?php echo $feature != '' ? $label : $module;?>'),
						),
						'url' => 'Yii::app()->controller->createUrl(\'view\', array(\'id\'=>$data->primaryKey))'),
					'update' => array(
						'label' => Yii::t('phrase', 'Update'),
						'imageUrl' => Yii::app()->params['grid-view']['buttonImageUrl'],
						'options' => array(
							'class' => 'update',
							'title' => Yii::t('phrase', 'Update <?php echo $feature != '' ? $label : $module;?>'),
						),
						'url' => 'Yii::app()->controller->createUrl(\'edit\', array(\'id\'=>$data->primaryKey))'),
					'delete' => array(
						'label' => Yii::t('phrase', 'Delete'),
						'imageUrl' => Yii::app()->params['grid-view']['buttonImageUrl'],
						'options' => array(
							'class' => 'delete',
							'title' => Yii::t('phrase', 'Delete <?php echo $feature != '' ? $label : $module;?>'),
						),
						'url' => 'Yii::app()->controller->createUrl(\'delete\', array(\'id\'=>$data->primaryKey))'),
				),
				'template' => '{view}|{update}|{delete}',
			));

			$this->widget('application.libraries.yii-traits.system.OGridView', array(
				'id'=>'<?php echo $this->class2id($modelClass); ?>-grid',
				'dataProvider'=>$model->search(),
				'filter'=>$model,
				'columns'=>$columnData,
				'template'=>Yii::app()->params['grid-view']['gridTemplate'],
				'pager'=>array('header'=>''),
				'afterAjaxUpdate'=>'reinstallDatePicker',
			));
		?>
		<?php echo "<?php ";?>//end.Grid Item ?>
	</div>
</div>