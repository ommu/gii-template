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
 * @author Putra Sudaryanto <putra@sudaryanto.id>
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
echo "\t\t\$model->$breadcrumbRelationAttribute,
\t);\n";
?>
?>

<?php echo "<?php ";?>//begin.Messages ?>
<div id="ajax-message">
<?php echo "<?php ";?>if(Yii::app()->user->hasFlash('success'))
	echo $this->flashMessage(Yii::app()->user->getFlash('success'), 'success');?>
</div>
<?php echo "<?php ";?>//end.Messages ?>

<?php if($this->generateAction['view']['dialog']):?>
<div class="dialog-content">
<?php else: ?>
<div class="box">
<?php endif; ?>
	<?php echo "<?php ";?>echo $this->renderPartial('_detail', array('model'=>$model)); ?>
<?php if($this->generateAction['view']['dialog']):?>
</div>
<div class="dialog-submit">
	<?php echo "<?php ";?>echo CHtml::button(Yii::t('phrase', 'Close'), array('id'=>'closed')); ?>
</div>
<?php else: ?>
</div>
<?php endif; ?>