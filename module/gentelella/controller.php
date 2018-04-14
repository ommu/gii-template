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
namespace <?= $generator->getControllerNamespace() ?>;

use app\components\Controller;

/**
<?php // FIX: unknown $controllerClass variabel?>
 * DefaultController<?php //echo $controllerClass."\n"; ?>
 * @var $this yii\web\View
 * version: 0.0.1
 *
 * Default controller for the `<?= $generator->moduleID ?>` module
 * Reference start
 * TOC :
 *	Index
 *
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @link <?php echo $yaml['link']."\n";?>
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @contact <?php echo $yaml['contact']."\n";?>
 *
 */
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
