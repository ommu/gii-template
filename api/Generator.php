<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\libraries\gii\api;

use Yii;
use yii\gii\CodeFile;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * This generator will generate a controller and one or a few action view files.
 *
 * @property array $actionIDs An array of action IDs entered by the user. This property is read-only.
 * @property string $controllerFile The controller class file path. This property is read-only.
 * @property string $controllerID The controller ID. This property is read-only.
 * @property string $controllerNamespace The namespace of the controller class. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \app\libraries\gii\Generator
{
    /**
     * @var string the controller class name
     */
    public $controllerClass;
    /**
     * @var string the controller's view path
     */
    public $viewPath;
    /**
     * @var string the base class of the controller
     */
    public $baseClass = '\app\components\api\ActiveController';
    public $modelClass;
    public $authType;
    public $rateLimit;
    public $rateLimitInSecond;
    public $nonCrudApi;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'API Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator helps you to quickly generate a new api controller class with crud functionality and authentication.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['controllerClass', 'baseClass', 'modelClass'], 'filter', 'filter' => 'trim'],
            [['controllerClass', 'baseClass'], 'required'],
            ['controllerClass', 'match', 'pattern' => '/^[\w\\\\]*Controller$/', 
                'message' => 'Only word characters and backslashes are allowed, and the class name must end with "Controller".'],
            ['controllerClass', 'validateNewClass'],
            ['baseClass', 'match', 'pattern' => '/^[\w\\\\]*$/', 
                'message' => 'Only word characters and backslashes are allowed.'],
            [['authType', 'nonCrudApi'], 'safe'],
            [['nonCrudApi'], 'boolean'],
            [['modelClass'], 'required', 'when' => function($model) {
                return $model->nonCrudApi == true;
            }],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'baseClass' => 'Base Class',
            'controllerClass' => 'Controller Class',
            'authType' => 'Authentication Type',
            'modelClass' => 'Model Class',
            'nonCrudApi' => 'Non CRUD API',
        ];
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return [
            'controller.php',
        ];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return ['baseClass'];
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return [
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>app\controllers\v1\PostController</code>),
                and class name should be in CamelCase ending with the word <code>Controller</code>. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'baseClass' => 'This is the class that the new controller class will extend from. Please make sure the class exists and can be autoloaded.',
            'modelClass' => 'Resource model that want to be api (e.g. <code>app\models\Post</code>)',
            'authType' => 'Authentication type that is to use to accessing resource.',
        ];
    }

    /**
     * @inheritdoc
     */
    public function successMessage()
    {
        $actions = $this->getActionIDs();
        if (in_array('index', $actions)) {
            $route = $this->getControllerID() . '/index';
        } else {
            $route = $this->getControllerID() . '/' . reset($actions);
        }
        $link = Html::a('try it now', Yii::$app->getUrlManager()->createUrl($route), ['target' => '_blank']);

        return "The controller has been generated successfully. You may $link.";
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];

        $files[] = new CodeFile(
            $this->getControllerFile(),
            $this->render('controller.php')
        );
        
        return $files;
    }

    /**
     * Normalizes [[actions]] into an array of action IDs.
     * @return array an array of action IDs entered by the user
     */
    public function getActionIDs()
    {
        return ['index', 'create', 'update', 'view', 'delete'];
    }

    /**
     * @return string the controller class file path
     */
    public function getControllerFile()
    {
        return Yii::getAlias('@' . str_replace('\\', '/', $this->controllerClass)) . '.php';
    }

    /**
     * @return string the controller ID
     */
    public function getControllerID()
    {
        $name = StringHelper::basename($this->controllerClass);
        return Inflector::camel2id(substr($name, 0, strlen($name) - 10));
    }

    /**
     * @return string the namespace of the controller class
     */
    public function getControllerNamespace()
    {
        $name = StringHelper::basename($this->controllerClass);
        return ltrim(substr($this->controllerClass, 0, - (strlen($name) + 1)), '\\');
    }

    public function getModuleId($controllerNs) {
        $modPath = ['app/modules', 'app/coremodules'];
        $paths = explode("\\", $controllerNs);
        $i = 0;
        $ns = [];
        foreach($paths as $path) {
            if($i == 3) { break; }
            $ns[] = $path;
            $i++;
        }

        $nsMod = implode('/', $ns);
        return basename($nsMod);
    }

    public function isApiModule($controllerClass) {
        $modPath = ['app/modules', 'app/coremodules'];
        $paths = explode("\\", $controllerClass);
        $i = 0;
        $ns = [];
        foreach($paths as $path) {
            if($i == 2) { break; }
            $ns[] = $path;
            $i++;
        }

        $nsMod = implode('/', $ns);
        foreach($modPath as $path) {
            if($path == $nsMod) { return true; }
        }
        return false;
    }
}
