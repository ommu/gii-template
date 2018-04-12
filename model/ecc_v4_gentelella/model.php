<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

/**
 * Variable
 */
use app\libraries\gii\model\Generator;
use yii\helpers\Inflector;

$patternClass = $patternLabel = array();
$patternClass[0] = '(Ommu)';
$patternClass[1] = '(Swt)';

$patternLabel[0] = '(ID)';
$patternLabel[1] = '(Search)';

/**
 * Condition
 */
$publishCondition = 0;
$slugCondition = 0;
$userCondition = 0;
foreach ($tableSchema->columns as $column):
    if($column->dbType == 'tinyint(1)' && in_array($column->name, ['publish','headline']))
        $publishCondition = 1;
    if($column->name == 'slug')
        $slugCondition = 1;
    if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id')))
        $userCondition = 1;
endforeach;

/**
 * foreignKeys Column
 */
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?= $className."\n" ?>
 * version: 0.0.1
 *
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
 * The followings are the available columns in table "<?= $generator->generateTableName($tableName) ?>":
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
 * The followings are the available model relations:
<?php 
//echo '<pre>';
//print_r($relations);
foreach ($relations as $name => $relation):
$relationModel = preg_replace($patternClass, '', $relation[1]);
$relationName = $relation[2] ? $name : $relation[1];?>
 * @property <?= $relationModel . ($relation[2] ? '[]' : '') . ' $' . ($relation[2] ? lcfirst($generator->setRelationName($relationName)) : lcfirst(Inflector::singularize($generator->setRelationName($relationName)))) ."\n" ?>
<?php endforeach; ?>
<?php endif; ?>

 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @link <?php echo $yaml['link']."\n";?>
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @contact <?php echo $yaml['contact']."\n";?>
 *
 */

namespace <?= $generator->ns ?>;

use Yii;
use yii\helpers\Url;
<?php echo $slugCondition ? "use ".ltrim('yii\behaviors\SluggableBehavior', '\\').";\n" : '';
echo $userCondition ? "use ".ltrim('app\coremodules\user\models\Users', '\\').";\n" : '';
echo $publishCondition ? "use ".ltrim('app\libraries\grid\GridView', '\\').";\n" : '';?>

class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
    public $gridForbiddenColumn = [];

<?php 
$publicVariable = array();
foreach ($tableSchema->columns as $column): 
if(!empty($foreignKeys) && in_array($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
    $relationTableName = array_search($column->name, $foreignKeys);
    $relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
    $relationName = lcfirst(Inflector::singularize($generator->setRelationName($relationModelName)));
    $publicVariable[] = $relationName.'_search';
endif;
endforeach;
foreach ($tableSchema->columns as $column): 
if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
    $relationNameArray = explode('_', $column->name);
    $relationName = lcfirst($relationNameArray[0]);
    $publicVariable[] = $relationName.'_search';
endif;
endforeach;
if(!empty($publicVariable)) {
    echo "\t// Variable Search\n"; 
foreach ($publicVariable as $val):
    echo "\tpublic $$val;\n";
endforeach;
    echo "\n"; 
}?>
    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php
if($tableType == Generator::TYPE_VIEW):
?>

    /**
     * @return string the primarykey column
     */
    public static function primaryKey() {
        return ['<?=$viewPrimaryKey?>'];
    }
<?php
endif;
?>
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>
<?php if ($slugCondition): ?>

    /**
     * behaviors model class.
     */
    public function behaviors() {
        return [
            [
                'class'     => SluggableBehavior::className(),
                'attribute' => '<?php echo $generator->getNameAttribute();?>',
                'immutable' => true,
                'ensureUnique' => true,
            ],
        ];
    }
<?php endif; ?>

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [<?= "\n         " . implode(",\n            ", preg_replace($patternClass, '', $rules)) . ",\n      " ?>];
    }
<?php 
//echo '<pre>';
//print_r($relations);
foreach ($relations as $name => $relation):
$relationModel = preg_replace($patternClass, '', $relation[1]);
$relationName = $relation[2] ? $name : Inflector::singularize($relation[1]);
//echo $relationName; ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= $generator->setRelationName($relationName) ?>()
    {
        <?= preg_replace($patternClass, '', $relation[0]) . "\n" ?>
    }
<?php endforeach; ?>
<?php 
//echo '<pre>';
//print_r($tableSchema->columns);
foreach ($tableSchema->columns as $column):
if(!$column->isPrimaryKey && in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
$relationNameArray = explode('_', $column->name);
$relationName = lcfirst($relationNameArray[0]); ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= ucfirst($relationName) ?>()
    {
        return $this->hasOne(Users::className(), ['user_id' => '<?php echo $column->name;?>']);
    }
<?php endif;
endforeach; ?>

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
<?php 
//echo '<pre>';
// print_r($labels);
foreach ($labels as $name => $label):
if(count(explode(' ', $label)) > 1)
    $label = trim(preg_replace($patternLabel, '', $label));
    echo "\t\t\t'$name' => " . $generator->generateString($label) . ",\n";
endforeach;
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
foreach ($tableSchema->columns as $column):
    if(!empty($foreignKeys) && in_array($column->name, $foreignKeys) && !in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
        $relationTableName = array_search($column->name, $foreignKeys);
        $relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
        $relationName = lcfirst(Inflector::singularize($generator->setRelationName($relationModelName)));
        $relationSearchName = $relationName.'_search';
        $attributeLabels = implode(' ', array_map('ucfirst', explode('_', $relationSearchName)));
        if(count(explode(' ', $attributeLabels)) > 1)
            $attributeLabels = trim(preg_replace($patternLabel, '', $attributeLabels));
        echo "\t\t\t'$relationSearchName' => " . $generator->generateString($attributeLabels) . ",\n";
    endif;
endforeach;
foreach ($tableSchema->columns as $column):
    if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
        $relationNameArray = explode('_', $column->name);
        $relationName = lcfirst($relationNameArray[0]);
        $relationSearchName = $relationName.'_search';
        $attributeLabels = implode(' ', array_map('ucfirst', explode('_', $relationSearchName)));
        if(count(explode(' ', $attributeLabels)) > 1)
            $attributeLabels = trim(preg_replace($patternLabel, '', $attributeLabels));
        echo "\t\t\t'$relationSearchName' => " . $generator->generateString($attributeLabels) . ",\n";
    endif;
endforeach; ?>
        ];
    }
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * @inheritdoc
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
    
    /**
     * Set default columns to display
     */
    public function init() 
    {
        parent::init();

        $this->templateColumns['_no'] = [
            'header' => <?php echo $generator->generateString('No');?>,
            'class'  => 'yii\grid\SerialColumn',
            'contentOptions' => ['class'=>'center'],
        ];
<?php 
//echo '<pre>';
//print_r($tableSchema);
foreach ($tableSchema->columns as $column):
if(!$column->isPrimaryKey || !$column->autoIncrement):
//if($column->dbType != 'tinyint(1)' && !in_array($column->name, ['publish','headline'])):
if($column->dbType != 'tinyint(1)'):
if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id'))):
    $relationNameArray = explode('_', $column->name);
    $relationName = lcfirst($relationNameArray[0]);
    $relationSearchName = $relationName.'_search'; ?>
        if(!isset($_GET['<?php echo $relationName;?>'])) {
            $this->templateColumns['<?php echo $relationSearchName;?>'] = [
                'attribute' => '<?php echo $relationSearchName;?>',
                'value' => function($model, $key, $index, $column) {
                    return isset($model-><?php echo $relationName;?>->displayname) ? $model-><?php echo $relationName;?>->displayname : '-';
                },
            ];
        }
<?php elseif(in_array($column->dbType, array('timestamp','datetime','date'))):?>
        $this->templateColumns['<?php echo $column->name;?>'] = [
            'attribute' => '<?php echo $column->name;?>',
            'filter'    => \yii\jui\DatePicker::widget([
                'dateFormat' => 'yyyy-MM-dd',
                'attribute' => '<?php echo $column->name;?>',
                'model'  => $this,
            ]),
            'value' => function($model, $key, $index, $column) {
                if(!in_array($model-><?php echo $column->name;?>, <?php echo $column->dbType == 'date' ? "\n\t\t\t\t\t['0000-00-00','1970-01-01','-0001-11-30']" : "\n\t\t\t\t\t['0000-00-00 00:00:00','1970-01-01 00:00:00','-0001-11-30 00:00:00']";?>)) {
                    return Yii::$app->formatter->format($model-><?php echo $column->name;?>, '<?php echo $column->dbType == 'date' ? $column->dbType : 'date';?>'/*datetime*/);
                }else {
                    return '-';
                }
            },
            'format'    => 'html',
        ];
<?php else:
if(!empty($foreignKeys) && in_array($column->name, $foreignKeys)):
    $relationTableName = array_search($column->name, $foreignKeys);
    $relationModelName = preg_replace($patternClass, '', $generator->generateClassName($relationTableName));
    $relationAttributeName = $generator->getNameAttribute($relationTableName);
    $relationName = lcfirst(Inflector::singularize($generator->setRelationName($relationModelName)));
    $relationSearchName = $relationName.'_search';?>
        if(!isset($_GET['<?php echo $relationName;?>'])) {
            $this->templateColumns['<?php echo $relationSearchName;?>'] = [
                'attribute' => '<?php echo $relationSearchName;?>',
                'value' => function($model, $key, $index, $column) {
                    return $model-><?php echo $relationName;?>-><?php echo $relationAttributeName;?>;
                },
            ];
        }
<?php else:?>
        $this->templateColumns['<?php echo $column->name;?>'] = '<?php echo $column->name;?>';
<?php endif;
endif;
endif;
endif;
endforeach;
foreach ($tableSchema->columns as $column):
if(!$column->isPrimaryKey || !$column->autoIncrement):
if($column->dbType == 'tinyint(1)' && !in_array($column->name, ['publish','headline'])):?>
        $this->templateColumns['<?php echo $column->name;?>'] = [
            'attribute' => '<?php echo $column->name;?>',
            'value' => function($model, $key, $index, $column) {
                return $model-><?php echo $column->name;?>;
            },
            'contentOptions' => ['class'=>'center'],
        ];
<?php endif;
endif;
endforeach;
foreach ($tableSchema->columns as $column):
if(!$column->isPrimaryKey || !$column->autoIncrement):
if($column->dbType == 'tinyint(1)' && $column->name == 'headline'):?>
        $this->templateColumns['<?php echo $column->name;?>'] = [
            'attribute' => '<?php echo $column->name;?>',
            'filter' => GridView::getFilterYesNo(),
            'value' => function($model, $key, $index, $column) {
                $url = Url::to(['headline', 'id' => $model->primaryKey]);
                return GridView::getHeadline($url, $model-><?php echo $column->name;?>);
            },
            'contentOptions' => ['class'=>'center'],
            'format'    => 'raw',
        ];
<?php endif;
endif;
endforeach;
foreach ($tableSchema->columns as $column):
if(!$column->isPrimaryKey || !$column->autoIncrement):
if($column->dbType == 'tinyint(1)' && $column->name == 'publish'):?>
        if(!isset($_GET['trash'])) {
            $this->templateColumns['<?php echo $column->name;?>'] = [
                'attribute' => '<?php echo $column->name;?>',
                'filter' => GridView::getFilterYesNo(),
                'value' => function($model, $key, $index, $column) {
                    $url = Url::to(['publish', 'id' => $model->primaryKey]);
                    return GridView::getPublish($url, $model-><?php echo $column->name;?>);
                },
                'contentOptions' => ['class'=>'center'],
                'format'    => 'raw',
            ];
        }
<?php endif;
endif;
endforeach; ?>
<?php /*
        if(count($this->defaultColumns) == 0) {
foreach ($tableSchema->columns as $column):
    if(!$column->isPrimaryKey) {
        if(in_array($column->dbType, array('timestamp','datetime','date'))) {?>
            $this->defaultColumns[] = [
                'attribute' => '<?php echo $column->name;?>',
                'filter'    => \yii\jui\DatePicker::widget(['dateFormat' => Yii::$app->formatter->dateFormat,
                    'attribute' => '<?php echo $column->name;?>',
                    'model'  => $this,
                ]),
                'format'    => 'html',
            ];
<?php } else { ?>
            $this->defaultColumns[] = [
                'attribute' => '<?php echo $column->name;?>',
                'class'  => 'yii\grid\DataColumn',
            ];
<?php   }
    }
endforeach; 
        }
*/ ?>
    }

<?php
//echo '<pre>';
//print_r($tableSchema->columns);
foreach($tableSchema->columns as $name=>$column):
    if($column->isPrimaryKey && (($column->type == 'tinyint' && $column->size == '3') || ($column->type == 'smallint' && in_array($column->size, array('3','5'))))):
        $functionName = $generator->setRelationName($className);
        $attributeName = $generator->getNameAttribute($generator->generateTableName($tableName));?>
    /**
     * function get<?= $functionName."\n"; ?>
     */
    public static function get<?= $functionName ?>(<?php echo $publishCondition ? '$publish = null' : '';?>) 
    {
        $items = [];
        $model = self::find();
<?php if($publishCondition) {?>
        if($publish != null)
            $model = $model->andWhere(['publish' => $publish]);
<?php }?>
        $model = $model->orderBy('<?php echo $attributeName;?> ASC')->all();

        if($model !== null) {
            foreach($model as $val) {
                $items[$val-><?php echo $column->name;?>] = $val-><?php echo $attributeName;?>;
            }
        }
        
        return $items;
    }

<?php endif;
endforeach;?>
    /**
     * before validate attributes
     */
    public function beforeValidate() 
    {
        if(parent::beforeValidate()) {
<?php
$creationCondition = 0;
foreach($tableSchema->columns as $name=>$column):
    if(in_array($column->name, array('creation_id','modified_id','updated_id')) && $column->comment != 'trigger'):
        if($column->name == 'creation_id') {
            $creationCondition = 1;
            echo "\t\t\tif(\$this->isNewRecord) {\n";
            echo "\t\t\t\t\$this->{$column->name} = !Yii::\$app->user->isGuest ? Yii::\$app->user->id : '0';\n";

        } else {
            if($creationCondition) {
                echo "\t\t\t\t\$this->{$column->name} = 0;\n";
                echo "\t\t\t}else\n";
            }else
                echo "\t\t\tif(!\$this->isNewRecord)\n";
            echo "\t\t\t\t\$this->{$column->name} = !Yii::\$app->user->isGuest ? Yii::\$app->user->id : '0';\n";
        }
    endif;
endforeach;
?>
        }
        return true;
    }

<?php 
$bsEvents = [];
foreach($tableSchema->columns as $name=>$column)
{
    if(in_array($column->dbType, array('date')) && $column->comment != 'trigger'):
        $bsEvents[] = $name;
    endif;
}
if($generator->generateEvents || !empty($bsEvents)): ?>
    /**
     * before save attributes
     */
    public function beforeSave($insert) 
    {
        if(parent::beforeSave($insert)) {
<?php if(!empty($bsEvents)):
foreach($bsEvents as $name):
        echo "\t\t\t\$this->$name = date('Y-m-d', strtotime(\$this->$name));\n";
endforeach;
else:?>
            // Create action
<?php endif; ?>
        }
        return true;    
    }

<?php if($generator->generateEvents): ?>
    /**
     * after validate attributes
     */
    public function afterValidate()
    {
        parent::afterValidate();
        // Create action
        
        return true;
    }
    
    /**
     * After save attributes
     */
    public function afterSave($insert, $changedAttributes) 
    {
        parent::afterSave($insert, $changedAttributes);
        // Create action
    }

    /**
     * Before delete attributes
     */
    public function beforeDelete() 
    {
        if(parent::beforeDelete()) {
            // Create action
        }
        return true;
    }

    /**
     * After delete attributes
     */
    public function afterDelete() 
    {
        parent::afterDelete();
        // Create action
    }
<?php endif;
endif; ?>
}
