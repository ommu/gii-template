<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\module\Generator */

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * @var $this yii\web\View
 * @var $this <?php echo ltrim($generator->getControllerNamespace().'\DefaultController')."\n"; ?>
 * version: 0.0.1
 *
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @link <?php echo $yaml['link']."\n";?>
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @contact <?php echo $yaml['contact']."\n";?>
 *
 */
 
use yii\helpers\Html;
<?= "?>\n" ?>

<div class="col-md-12 col-sm-12 col-xs-12">
	<div class="x_panel">
		<div class="x_title">
			<h2><?= "<?php echo "?>Html::encode($this->title); ?><small><?= "<?php echo " ?>$this->context->action->uniqueId ?></small></h2>
			<ul class="nav navbar-right panel_toolbox">
				<li><a href="#" title="<?php echo "<?php echo {$generator->generateString('Toggle')};?>";?>" class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
				<li class="dropdown">
					<a href="#" title="<?php echo "<?php echo {$generator->generateString('Options')};?>";?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
					<ul class="dropdown-menu" role="menu">
						<li><a href="#" title="<?php echo "<?php echo {$generator->generateString('Grid Options')};?>";?>"><?php echo "<?php echo {$generator->generateString('Grid Options')};?>";?></a></li>
					</ul>
				</li>
				<li><a href="#" title="<?php echo "<?php echo {$generator->generateString('Close')};?>";?>" class="close-link"><i class="fa fa-close"></i></a></li>
			</ul>
			<div class="clearfix"></div>
		</div>
		<div class="x_content">
			<p>
				This is the view content for action "<?= "<?php echo " ?>$this->context->action->id ?>".
				The action belongs to the controller "<?= "<?php echo " ?>get_class($this->context) ?>"
				in the "<?= "<?php echo " ?>$this->context->module->id ?>" module.
			</p>
			<p>
				You may customize this page by editing the following file:<br>
				<code><?= "<?php echo " ?>__FILE__ ?></code>
			</p>
		</div>
	</div>
</div>

<div class="<?= $generator->moduleID . '-default-index' ?>"></div>