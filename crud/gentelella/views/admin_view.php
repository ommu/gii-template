<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$patternClass = array();
$patternClass[0] = '(Ommu)';
$patternClass[1] = '(Swt)';

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);
$tableSchema = $generator->getTableSchema();

$urlParams = $generator->generateUrlParams();

$patternLabel = array();
$patternLabel[0] = '(Core )';
$patternLabel[1] = '(Zone )';

$uploadCondition = 0;
foreach ($tableSchema->columns as $column):
	if($column->type == 'text' && $column->comment == 'file') 
		$uploadCondition = 1;
endforeach;

$labelButton = Inflector::pluralize(preg_replace($patternLabel, '', $label));

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this yii\web\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->modelClass)."\n"; ?>
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
use yii\helpers\Url;
use yii\widgets\DetailView;
<?php 
echo $uploadCondition ? "use ".ltrim($generator->modelClass)."\n" : '';
?>

$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($labelButton) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu']['content'] = [
	['label' => <?= $generator->generateString('Back To Manage') ?>, 'url' => Url::to(['index']), 'icon' => 'table'],
	['label' => <?= $generator->generateString('Update') ?>, 'url' => Url::to(['update', <?= $urlParams ?>]), 'icon' => 'pencil'],
	['label' => <?= $generator->generateString('Delete') ?>, 'url' => Url::to(['delete', <?= $urlParams ?>]), 'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>, 'method' => 'post', 'icon' => 'trash'],
];
?>

<?= "<?php echo " ?>DetailView::widget([
	'model' => $model,
	'options' => [
		'class'=>'table table-striped detail-view',
	],
	'attributes' => [
<?php
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
if (($tableSchema = $tableSchema) === false) {
	foreach ($generator->getColumnNames() as $name) {
		echo "\t\t'" . $name . "',\n";
	}
} else {
	foreach ($tableSchema->columns as $column) {
		if($column->name[0] == '_')
			continue;

if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id'])) {
	$relationTableName = trim($foreignKeys[$column->name]);
	$relationAttributeName = $generator->getNameRelationAttribute($relationTableName);
	if(trim($foreignKeys[$column->name]) == 'ommu_users')
		$relationAttributeName = 'displayname';
	$relationName = $generator->setRelationName($column->name);
	$publicVariable = $relationName.'_search';?>
		[
			'attribute' => '<?php echo $publicVariable;?>',
			'value' => isset($model-><?php echo $relationName;?>) ? $model-><?php echo $relationName;?>-><?php echo $relationAttributeName;?> : '-',
		],
<?php } else if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','tag_id'))) {
	$relationName = $generator->setRelationName($column->name);
	$publicVariable = $relationName.'_search';
	$relationAttributeName = 'displayname';
	if($column->name == 'tag_id') {
		$publicVariable = $relationName.'_i';
		$relationAttributeName = 'body';
	}?>
		[
			'attribute' => '<?php echo $publicVariable;?>',
			'value' => isset($model-><?php echo $relationName;?>) ? $model-><?php echo $relationName;?>-><?php echo $relationAttributeName;?> : '-',
		],
<?php } else if(in_array($column->dbType, array('timestamp','datetime','date'))) {?>
		[
			'attribute' => '<?php echo $column->name;?>',
			'value' => !in_array($model-><?php echo $column->name;?>, <?php echo $column->dbType == 'date' ? "['0000-00-00','1970-01-01','0002-12-02','-0001-11-30']" : "['0000-00-00 00:00:00','1970-01-01 00:00:00','0002-12-02 07:07:12','-0001-11-30 00:00:00']";?>) ? Yii::$app->formatter->format($model-><?php echo $column->name;?>, '<?php echo $column->dbType == 'date' ? $column->dbType : 'datetime';?>') : '-',
		],
<?php } else if($column->dbType == 'tinyint(1)') {?>
		[
			'attribute' => '<?php echo $column->name;?>',
<?php if(in_array($column->name, ['publish','headline']) || $column->comment != '') {
	if($column->name == 'publish') {
		if($column->comment == '') {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>),
		<?php } else {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>, '<?php echo $column->comment;?>'),
<?php 	}
	} else if($column->name == 'headline') {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>, 'Headline,No Headline', true),
<?php } else {?>
			'value' => $this->quickAction(Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id'=>$model->primaryKey]), $model-><?php echo $column->name;?>, '<?php echo $column->comment;?>'),
<?php }?>
			'format' => 'raw',
<?php } else {?>
			'value' => $model-><?php echo $column->name;?> == 1 ? <?php echo $generator->generateString('Yes');?> : <?php echo $generator->generateString('No');?>,
<?php }?>
		],
<?php } else if(in_array($column->dbType, array('text'))) {?>
		[
			'attribute' => '<?php echo $column->name;?>',
<?php if($column->comment == 'file'):?>
			'value' => function ($model) {
				$image = join('/', [Url::Base(), <?php echo $modelClass;?>::getUploadPath(false), $model-><?php echo $column->name;?>]);
				return $model-><?php echo $column->name;?> ? Html::img($image, ['width' => '100%']).'<br/><br/>'.$image : '-';
			},
<?php elseif($column->comment == 'serialize'):?>
			'value' => serialize($model-><?php echo $column->name;?>),
<?php else:?>
			'value' => $model-><?php echo $column->name;?> ? $model-><?php echo $column->name;?> : '-',
<?php endif;
if(in_array($column->comment, array('redactor','file'))):?>
			'format' => 'raw',
<?php endif;?>
		],
<?php } else {
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$publicVariable = $column->name.'_i';
		$publicAttributeRelation = preg_match('/(name|title)/', $column->name) ? 'title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? 'description' : $column->name.'Rltn') : $column->name.'Rltn');?>
		[
			'attribute' => '<?php echo $publicVariable;?>',
			'value' => isset($model-><?php echo $publicAttributeRelation;?>) ? $model-><?php echo $publicAttributeRelation;?>->message : '-',
<?php if(in_array('redactor', $commentArray)):?>
			'format' => 'html',
<?php endif;?>
		],
<?php } else {
		$format = $generator->generateColumnFormat($column);
		echo "\t\t'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
		}
	}
	}
}
?>
	],
]) ?>