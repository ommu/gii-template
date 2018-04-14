<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();
$label = Inflector::camel2words($modelClass);
$attributeName = $generator->getNameAttribute();
$tableSchemaColumns = $generator->tableSchema->columns;

$patternLabel = array();
$patternLabel[0] = '(Core )';
$patternLabel[1] = '(Zone )';

$labelButton = preg_replace($patternLabel, '', $label);


$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo $controllerClass."\n"; ?>
 * @var $this yii\web\View
 * @var $model <?php echo ltrim($generator->modelClass)."\n"; ?>
 * version: 0.0.1
 *
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 * Reference start
 * TOC :
 *  Index
 *  Create
 *  Update
 *  View
 *  Delete
<?php if(array_key_exists('publish', $tableSchemaColumns)): ?>
 *  RunAction
 *  Publish
<?php endif; ?>
<?php if(array_key_exists('headline', $tableSchemaColumns)): ?>
 *  Headline
<?php endif; ?>
 *
 *  findModel
 *
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @link <?php echo $yaml['link']."\n";?>
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @contact <?php echo $yaml['contact']."\n";?>
 *
 */
 
namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
<?php if($generator->attachRBACFilter): ?>
use mdm\admin\components\AccessControl;
<?php endif; ?>

class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
<?php if($generator->attachRBACFilter): ?>
            'access' => [
                'class' => AccessControl::className(),
            ],
<?php endif; ?>
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
<?php if(array_key_exists('publish', $tableSchemaColumns)): ?>
                    'publish' => ['POST'],
<?php endif;
if(array_key_exists('headline', $tableSchemaColumns)): ?>
                    'headline' => ['POST'],
<?php endif; ?>
                ],
            ],
        ];
    }

    /**
     * Lists all <?= $modelClass ?> models.
     * @return mixed
     */
    public function actionIndex()
    {
<?php if (!empty($generator->searchModelClass)): ?>
        $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $gridColumn = Yii::$app->request->get('GridColumn', null);
        $cols = [];
        if($gridColumn != null && count($gridColumn) > 0) {
            foreach($gridColumn as $key => $val) {
                if($gridColumn[$key] == 1)
                    $cols[] = $key;
            }
        }
        $columns = $searchModel->getGridColumn($cols);

        $this->view->title = <?php echo $generator->generateString(Inflector::pluralize($labelButton));?>;
        $this->view->description = '';
        $this->view->keywords = '';
        return $this->render('admin_index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'columns'     => $columns,
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);

        $this->view->title = <?php echo $generator->generateString(Inflector::pluralize($labelButton));?>;
        $this->view->description = '';
        $this->view->keywords = '';
        return $this->render('admin_index', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif; ?>
    }

    /**
     * Creates a new <?= $modelClass ?> model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new <?= $modelClass ?>();

        if(Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if($model->save()) {
                //return $this->redirect(['view', <?= $urlParams ?>]);
                Yii::$app->session->setFlash('success', <?php echo $generator->generateString($labelButton.' success created.');?>);
                return $this->redirect(['index']);
            } 
        }

        $this->view->title = <?php echo $generator->generateString('Create ' . $labelButton);?>;
        $this->view->description = '';
        $this->view->keywords = '';
        return $this->render('admin_create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing <?= $modelClass ?> model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionUpdate(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);
        if(Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());

            if($model->save()) {
                //return $this->redirect(['view', <?= $urlParams ?>]);
                Yii::$app->session->setFlash('success', <?php echo $generator->generateString($labelButton.' success updated.');?>);
                return $this->redirect(['index']);
            }
        }

<?php if($generator->enableI18N) {
    $pageTitleArray = ['modelClass' => $labelButton];
    $pageTitleArray[$attributeName] = "\$model->$attributeName";
?>
        $this->view->title = <?php echo $generator->generateString('Update {modelClass}: {'.$attributeName.'}', $pageTitleArray);?>;
<?php } else {?>
        $this->view->title = <?= $generator->generateString('Update {modelClass}: ', ['modelClass' => $labelButton]) ?>.$model-><?= $attributeName ?>;
<?php }?>
        $this->view->description = '';
        $this->view->keywords = '';
        return $this->render('admin_update', [
            'model' => $model,
        ]);
    }

    /**
     * Displays a single <?= $modelClass ?> model.
     * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionView(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);

<?php if($generator->enableI18N) {
    $pageTitleArray = ['modelClass' => $labelButton];
    $pageTitleArray[$attributeName] = "\$model->$attributeName";
?>
        $this->view->title = <?php echo $generator->generateString('View {modelClass}: {'.$attributeName.'}', $pageTitleArray);?>;
<?php } else {?>
        $this->view->title = <?= $generator->generateString('View {modelClass}: ', ['modelClass' => $labelButton]) ?>.$model-><?= $attributeName; ?>;
<?php }?>
        $this->view->description = '';
        $this->view->keywords = '';
        return $this->render('admin_view', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing <?= $modelClass ?> model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionDelete(<?= $actionParams ?>)
    {
<?php if(array_key_exists('publish', $tableSchemaColumns)): ?>
        $model = $this->findModel(<?= $actionParams ?>);
        $model->publish = 2;

        if($model->save(false, ['publish'])) {
            //return $this->redirect(['view', <?= $urlParams ?>]);
            Yii::$app->session->setFlash('success', <?php echo $generator->generateString($labelButton.' success deleted.');?>);
            return $this->redirect(['index']);
        }
<?php else: ?>
        $this->findModel(<?= $actionParams ?>)->delete();
        
        Yii::$app->session->setFlash('success', <?php echo $generator->generateString($labelButton.' success deleted.');?>);
        return $this->redirect(['index']);
<?php endif; ?>
    }
<?php if(array_key_exists('publish', $tableSchemaColumns)): ?>

    /**
     * Publish/Unpublish an existing <?= $modelClass ?> model.
     * If publish/unpublish is successful, the browser will be redirected to the 'index' page.
     * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionPublish(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);
        $replace = $model->publish == 1 ? 0 : 1;
        $model->publish = $replace;

        if($model->save(false, ['publish'])) {
            Yii::$app->session->setFlash('success', <?php echo $generator->generateString($labelButton.' success updated.');?>);
            return $this->redirect(['index']);
        }
    }
<?php endif;
if(array_key_exists('headline', $tableSchemaColumns)): ?>

    /**
     * Headline an existing <?= $modelClass ?> model.
     * If headline is successful, the browser will be redirected to the 'index' page.
     * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionHeadline(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);
        $model->headline = 1;
        $model->publish  = 1;

        if ($model->save(false, ['publish', 'headline'])) {
            Yii::$app->session->setFlash('success', <?php echo $generator->generateString($labelButton.' success updated.');?>);
            return $this->redirect(['index']);
        }
    }
<?php endif; ?>

    /**
     * Finds the <?= $modelClass ?> model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
     * @return <?=                 $modelClass ?> the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(<?= $actionParams ?>)
    {
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        if(($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) 
            return $model;
        else
            throw new NotFoundHttpException(<?php echo $generator->generateString('The requested page does not exist.');?>);
    }
}
