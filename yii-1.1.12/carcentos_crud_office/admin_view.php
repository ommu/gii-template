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
$nameColumn=$this->guessNameColumn($this->tableSchema->columns);
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\$this->breadcrumbs=array(
	'$label'=>array('adminmanage'),
	Yii::t('site', 'Detail $label'),
);\n";
?>

	$cs = Yii::app()->getClientScript();
	$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/office/grid-view.css');
?>

<?php 
echo "<? //begin.Messages ?>\n";
echo "<?php\n";
echo "	if(Yii::app()->user->hasFlash('success'))
		echo Utility::flashSuccess(Yii::app()->user->getFlash('success'));
?>\n";
echo "<? //end.Messages ?>";?>

<?php echo "<?php"; ?> $this->widget('application.components.system.BDetailView', array(
	'data'=>$model,
	'attributes'=>array(
<?php
foreach($this->tableSchema->columns as $column)
	echo "\t\t'".$column->name."',\n";
?>
	),
)); ?>
