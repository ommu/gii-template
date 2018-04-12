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

/**
 * <?php echo $controllerClass."\n"; ?>
 * version: 0.0.1
 *
 * @copyright Copyright(c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @link    <?php echo $yaml['link']."\n";?>
 * @author  <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @created <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @contact <?php echo $yaml['contact']."\n";?>
 *
 */
class <?= $controllerClass ?> extends <?= $controller. "\n" ?>
{
	public $modelClass = '<?= $generator->modelClass ?>';
	public static $authType = <?= $generator->authType ?>;
}
