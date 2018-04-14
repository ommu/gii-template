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
namespace <?= $generator->getControllerNamespace() ?>;

use <?= ltrim($generator->baseClass, '\\') ?>;
<?php if($generator->integrateWithRbac): ?>
use \mdm\admin\components\AccessControl;
<?php endif; ?>

/**
 * <?php echo $controllerClass."\n"; ?>
 * @var $this yii\web\View
 * version: 0.0.1
 *
 * Reference start
 * TOC :
<?php foreach ($generator->getActionIDs() as $action): ?>
 *	<?= Inflector::id2camel($action)."\n" ?>
<?php endforeach; ?>
 *
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @link <?php echo $yaml['link']."\n";?>
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @contact <?php echo $yaml['contact']."\n";?>
 *
 */
class <?= $controllerClass ?> extends <?= $controller. "\n" ?>
{
<?php if($generator->integrateWithRbac): ?>
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
