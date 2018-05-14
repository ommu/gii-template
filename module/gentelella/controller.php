<?php
/**
 * This is the template for generating a controller class within a module.
 */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\module\Generator */

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
<?php // FIX: unknown $controllerClass variabel?>
 * DefaultController<?php echo "\n";//echo $controllerClass."\n"; ?>
 * @var $this yii\web\View
 *
 * Default controller for the `<?= $generator->moduleID ?>` module
 * Reference start
 * TOC :
 *	Index
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

use app\components\Controller;

class DefaultController extends Controller
{
	/**
	 * Renders the index view for the module
	 * @return string
	 */
	public function actionIndex()
	{
		$this->view->title = <?php echo $generator->generateString(Inflector::pluralize($generator->moduleID));?>;
		$this->view->description = '';
		$this->view->keywords = '';
		return $this->render('index');
	}
}
