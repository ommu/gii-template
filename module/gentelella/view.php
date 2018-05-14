<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\module\Generator */

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * @var $this yii\web\View
 * @var $this <?php echo ltrim($generator->getControllerNamespace().'\DefaultController')."\n"; ?>
 *
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($generator->useModified):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @modified by <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
<?php endif; ?>
 * @link <?php echo $generator->link."\n";?>
 *
 */
 
use yii\helpers\Html;
<?= "?>\n" ?>

<p>
	This is the view content for action "<?= "<?php echo " ?>$this->context->action->id ?>".
	The action belongs to the controller "<?= "<?php echo " ?>get_class($this->context) ?>"
	in the "<?= "<?php echo " ?>$this->context->module->id ?>" module.
</p>
<p>
	You may customize this page by editing the following file:<br>
	<code><?= "<?php echo " ?>__FILE__ ?></code>
</p>

<div class="<?= $generator->moduleID . '-default-index' ?>"></div>