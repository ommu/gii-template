<?php
/**
 * This is the template for generating a controller class file.
 */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\controller\Generator */

$controller = StringHelper::basename($generator->baseClass);
$controllerClass = StringHelper::basename($generator->controllerClass);

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo $controllerClass."\n"; ?>
 * @var $this yii\web\View
 *
 * Reference start
 * TOC :
<?php foreach ($generator->getActionIDs() as $action): ?>
 *	<?= Inflector::id2camel($action)."\n" ?>
<?php endforeach; ?>
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

namespace <?= $generator->getControllerNamespace() ?>;

use Yii;
use <?= ltrim($generator->baseClass, '\\') ?>;
<?php if($generator->attachRBACFilter): ?>
use mdm\admin\components\AccessControl;
<?php endif; ?>

class <?= $controllerClass ?> extends <?= $controller. "\n" ?>
{
<?php if($generator->attachRBACFilter): ?>
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
			],
		];
	}
	
<?php endif; ?>
<?php foreach ($generator->getActionIDs() as $action): ?>
	/**
	 * @inheritdoc
	 */
	public function action<?= Inflector::id2camel($action) ?>()
	{
		$this->view->title = <?php echo $generator->generateString(Inflector::pluralize($controllerClass));?>;
		$this->view->description = '';
		$this->view->keywords = '';
		return $this->render('<?= $action ?>');
	}

<?php endforeach; ?>
}
