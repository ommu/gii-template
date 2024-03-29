<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

$uploadCondition = 0;
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if($column->dbType == 'text' && in_array('file', $commentArray))
		$uploadCondition = 1;
endforeach;

echo "<?php\n"; ?>
/**
 * <?php echo $inflector->pluralize($this->class2name($modelClass)); ?> (<?php echo $this->class2id($modelClass); ?>)
 * @var $this <?php echo $this->getControllerClass()."\n"; ?>
 * @var $model <?php echo $this->getModelClass()."\n"; ?>
 * @var $form CActiveForm
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
echo "\t\tYii::t('phrase', 'Create'),
\t);\n";
?>
?>

<?php if(!$this->generateAction['create']['dialog']):?>
<div class="form"<?php echo !$uploadCondition ? ' name="post-on"' : '';?>>
	<?php echo "<?php ";?>echo $this->renderPartial('_form', array('model'=>$model)); ?>
</div>
<?php else:
	echo "<?php ";?>echo $this->renderPartial('_form', array('model'=>$model)); ?>
<?php endif; ?>