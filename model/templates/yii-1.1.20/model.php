<?php
/**
 * This is the template for generating the model class of a specified table.
 * - $this: the ModelCode object
 * - $tableName: the table name for this class (prefix is already removed if necessary)
 * - $modelClass: the model class name
 * - $columns: list of table columns (name=>CDbColumnSchema)
 * - $labels: list of attribute labels (name=>label)
 * - $rules: list of validation rules
 * - $relations: list of relations (name=>relation declaration)
 * - $table: schema of table
 */

Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

$primaryKey = $table->primaryKey;
if(!$primaryKey)
	$primaryKey = key($columns);

$tableViewCondition = 0;
$isStatisticTable = 0;
$generateFunctionCondition = 0;
$otherRelationCondition = 0;
$viewRelationCondition = 0;
$tinyCondition = 0;
$dateCondition = 0;
$publishCondition = 0;
$slugCondition = 0;
$tagCondition = 0;
$uploadCondition = 0;
$serializeCondition = 0;
$permissionCondition = 0;
$searchVariableCondition = 0;
$manyRelationCondition = 0;
$i18n = 0;

$foreignKeys = $this->foreignKeys($table->foreignKeys);

if($tableName[0] == '_')
	$tableViewCondition = 1;
	
$tableView1 = $this->tableView($tableName);
$tableView2 = $this->tableView($tableName, true);
if(in_array($tableView1, $tableViews) || in_array($tableView2, $tableViews)) {
	$otherRelationCondition = 1;
	$viewRelationCondition = 1;
}

foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if($column->name == $primaryKey && !$tableViewCondition) {
		if(preg_match('/(smallint)/', $column->dbType))
			$generateFunctionCondition = 1;
		if($column->comment == 'trigger')
			$isStatisticTable = 1;
	}
	if($column->dbType == 'tinyint(1)') {
		$tinyCondition = 1;
		if($column->name == 'publish')
			$publishCondition = 1;
		if($column->name == 'permission')
			$permissionCondition = 1;
	}
	if(in_array($column->dbType, array('timestamp','datetime','date')))
		$dateCondition = 1;
	if(!$tableViewCondition && $column->name == 'slug')
		$slugCondition = 1;
	if(!$tableViewCondition && $column->dbType == 'text' && in_array('file', $commentArray))
		$uploadCondition = 1;
	if(!$tableViewCondition && $column->dbType == 'text' && in_array('serialize', $commentArray))
		$serializeCondition = 1;
	if($column->isForeignKey || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
		if(!$tableViewCondition && $column->name == 'tag_id')
			$tagCondition = 1;
		$searchVariableCondition = 1;
		$otherRelationCondition = 1;
	}
	if(in_array('trigger[delete]', $commentArray)) {
		$i18n = 1;
		$otherRelationCondition = 1;
	}
endforeach;

?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $modelClass."\n"; ?>
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (www.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($this->useModified):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php endif; ?>
 * @link <?php echo $this->linkSource."\n";?>
 *
 * This is the model class for table "<?php echo $tableName; ?>".
 *
 * The followings are the available columns in table '<?php echo $tableName; ?>':
<?php foreach($columns as $column):
	if($column->name[0] == '_')
		continue;

	$type = $column->type;
	if($type == 'string' && preg_match('/(int)/', $column->dbType))
		$type = 'integer';?>
 * @property <?php echo $type.' $'.$column->name."\n"; ?>
<?php endforeach; ?>
<?php if(!empty($relations) || $otherRelationCondition): ?>
 *
 * The followings are the available model relations:
<?php 
$availableRelations = array();
$manyRelationPublicVariables = array();
foreach($relations as $name=>$relation): ?>
 * @property <?php
	if (preg_match("~^array\(self::([^,]+), '([^']+)', '([^']+)'\)$~", $relation, $matches))
	{
        $relationType = $matches[1];
        $relationModel = $matches[2];

		$availableRelations[] = $name;
		if(!preg_match('/(All)/', $name) && in_array($relationType, array('HAS_MANY','MANY_MANY')))
			$manyRelationPublicVariables[$inflector->singularize($name).'_i'] = $name;

		switch($relationType){
			case 'HAS_ONE':
				echo $relationModel.' $'.$name."\n";
			break;
			case 'BELONGS_TO':
				echo $relationModel.' $'.$name."\n";
			break;
			case 'HAS_MANY':
				echo $relationModel.'[] $'.$name."\n";
			break;
			case 'MANY_MANY':
				echo $relationModel.'[] $'.$name."\n";
			break;
			default:
				echo 'mixed $'.$name."\n";
		}
	} else if (preg_match("~^array\(self::([^,]+), '([^']+)', '([^']+)', '([^']+)'=>'([^']+)'\)$~", $relation, $matches))
	{
        $relationType = $matches[1];
        $relationModel = $matches[2];
		$availableRelations[] = $manyRelationPublicVariables[$inflector->singularize($name).'_i'] = $name;

		switch($relationType){
			case 'HAS_ONE':
				echo $relationModel.' $'.$name."\n";
			break;
			case 'BELONGS_TO':
				echo $relationModel.' $'.$name."\n";
			break;
			case 'HAS_MANY':
				echo $relationModel.'[] $'.$name."\n";
			break;
			case 'MANY_MANY':
				echo $relationModel.'[] $'.$name."\n";
			break;
			default:
				echo 'mixed $'.$name."\n";
		}
	}
endforeach;
foreach($columns as $name=>$column):
	if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
		$relationName = $this->setRelation($column->name, true);
		if(!in_array($relationName, $availableRelations)) {
			$availableRelations[] = $relationName;
			if($column->name == 'member_id')
				echo " * @property Members \${$relationName}\n";
			else if($column->name == 'tag_id')
				echo " * @property OmmuTags \${$relationName}\n";
			else
				echo " * @property Users \${$relationName}\n";
		}
	} else {
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)) {
			$relationName = $this->i18nRelation($column->name);
			echo " * @property SourceMessage \${$relationName}\n";
		}
	}
endforeach;
endif; 
if(!empty($manyRelationPublicVariables))
	$manyRelationCondition = 1;?>
 */

class <?php echo $modelClass; ?> extends <?php echo $this->baseClass."\n"; ?>
{
<?php 
$traitCondition = 0;
if($i18n || $tagCondition) {
	echo "\tuse UtilityTrait;\n";
	$traitCondition = 1;
}
if($tinyCondition || $dateCondition) {
	echo "\tuse GridViewTrait;\n";
	$traitCondition = 1;
}
if($uploadCondition) {
	echo "\tuse FileTrait;\n";
	$traitCondition = 1;
}
if($traitCondition)
	echo "\n";?>
	public $gridForbiddenColumn = array();
<?php 
$inputPublicVariables = array();
$searchPublicVariables = array();
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(!$tableViewCondition && in_array('trigger[delete]', $commentArray)) {
		$inputPublicVariable = $column->name.'_i';
		if(!in_array($inputPublicVariable, $inputPublicVariables))
			$inputPublicVariables[$inputPublicVariable] = ucwords(strtolower($this->i18nRelation($column->name, false)));
	}
endforeach;
foreach($columns as $name=>$column):
	if(!$tableViewCondition && in_array($column->name, ['tag_id'])) {
		$relationName = $this->setRelation($column->name, true);
		$inputPublicVariable = $relationName.'_i';
		if(!in_array($inputPublicVariable, $inputPublicVariables))
			$inputPublicVariables[$inputPublicVariable] = ucwords(strtolower($relationName));
	}
endforeach;
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if($column->dbType == 'text' && in_array('file', $commentArray)) {
		$inputPublicVariable = 'old_'.$column->name.'_i';
		if(!in_array($inputPublicVariable, $inputPublicVariables))
			$inputPublicVariables[$inputPublicVariable] = ucwords(strtolower('old '.$column->name));
	}
endforeach;

foreach($columns as $name=>$column):
	$smallintCondition = 0;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['creation_id','modified_id','user_id','updated_id','member_id','tag_id'])) {
		if(preg_match('/(smallint)/', $column->dbType))
			$smallintCondition = 1;
		$relationName = $this->setRelation($column->name, true);
		$searchPublicVariable = $relationName.'_search';
		if(!$smallintCondition && !in_array($searchPublicVariable, $searchPublicVariables))
			$searchPublicVariables[$searchPublicVariable] = ucwords(strtolower($relationName));
	}
endforeach;
foreach($columns as $name=>$column):
	if(in_array($column->name, ['creation_id','modified_id','user_id','updated_id','member_id','tag_id'])) {
		$relationName = $this->setRelation($column->name, true);
		$searchPublicVariable = $relationName.'_search';
		if(!in_array($searchPublicVariable, $searchPublicVariables))
			$searchPublicVariables[$searchPublicVariable] = ucwords(strtolower($relationName));
	}
endforeach;

if(!empty($inputPublicVariables)) {
	foreach ($inputPublicVariables as $key=>$val):
		echo "\tpublic $$key;\n";
	endforeach;
}
if(!empty($manyRelationPublicVariables)) {
foreach ($manyRelationPublicVariables as $key=>$val):
	echo "\tpublic $$key;\n";
endforeach;
}
if(!empty($searchPublicVariables)) {
	echo "\n\t// Variable Search\n"; 
foreach ($searchPublicVariables as $key=>$val):
	echo "\tpublic $$key;\n";
endforeach;
}
if($slugCondition):
	$tableAttribute = $this->tableAttribute($columns);?>

	/**
	 * Behaviors for this model
	 */
	public function behaviors() 
	{
		return array(
			'sluggable' => array(
				'class'=>'ext.yii-sluggable.SluggableBehavior',
				'columns' => array('<?php echo $i18n && preg_match('/(name|title)/', $tableAttribute) ? 'title.message' : $tableAttribute;?>'),
				'unique' => true,
				'update' => true,
			),
		);
	}
<?php endif; ?>

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return <?php echo $modelClass; ?> the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
<?php if($connectionId!='db'):?>

	/**
	 * @return CDbConnection the database connection used for this class
	 */
	public function getDbConnection()
	{
		return Yii::app()-><?php echo $connectionId ?>;
	}
<?php endif?>

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		preg_match("/dbname=([^;]+)/i", $this->dbConnection->connectionString, $matches);
		return $matches[1].'.<?php echo $tableName; ?>';
	}
<?php if($tableViewCondition) {?>

	/**
	 * @return string the primarykey column
	 */
	public function primaryKey()
	{
		return '<?php echo $primaryKey; ?>';
	}
<?php }?>

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
<?php
foreach($rules as $rule):
	echo "\t\t\t".$rule.",\n";
endforeach;

if(!empty($manyRelationPublicVariables))
	$searchVariables = array_merge(array_keys($inputPublicVariables), array_keys($manyRelationPublicVariables));
else
	$searchVariables = array_keys($inputPublicVariables);
$searchVariables = array_merge($searchVariables, array_keys($searchPublicVariables));

$searchVariable = implode(', ', $searchVariables);
$columnSearch = array_keys($columns);
$columnSearch1 = array();
foreach($columnSearch as $val) {
	if($val[0] == '_')
		continue;
	$columnSearch1[] = $val;
}?>
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('<?php echo implode(', ', $columnSearch1); echo !empty($searchVariables) ? ",\n\t\t\t\t{$searchVariable}" : '' ?>', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
<?php 
$availableRelations = array();
foreach($relations as $name=>$relation):
	if(!in_array($name, $availableRelations)) {
		echo "\t\t\t'$name' => $relation,\n";
		$availableRelations[] = $name;
	}
endforeach;
if($i18n):
	foreach($columns as $name=>$column):
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)):
			$relationName = $this->i18nRelation($column->name);
			if(!in_array($relationName, $availableRelations)) {
				echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'SourceMessage', '$name'),\n";
				$availableRelations[] = $relationName;
			}
		endif;
	endforeach;
endif;
foreach($columns as $name=>$column):
	if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
		$relationName = $this->setRelation($column->name, true);
		if(!in_array($relationName, $availableRelations)) {
			if($column->name == 'member_id')
				echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'Members', '$column->name'),\n";
			else if($column->name == 'tag_id')
				echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'OmmuTags', '$column->name'),\n";
			else
				echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'Users', '$column->name'),\n";
			$availableRelations[] = $relationName;
		}
	}
endforeach;?>
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
<?php
foreach($labels as $name=>$label):
	if(strtolower($label) == 'cat')
		$label = 'Category';
	echo "\t\t\t'$name' => Yii::t('attribute', '$label'),\n";
endforeach;

if(!empty($inputPublicVariables)) {
	foreach ($inputPublicVariables as $key=>$val):
		echo "\t\t\t'$key' => Yii::t('attribute', '$val'),\n";
	endforeach;
}
if(!empty($manyRelationPublicVariables)) {
	foreach ($manyRelationPublicVariables as $key=>$val):
		$attribute = ucwords($val);
		echo "\t\t\t'$key' => Yii::t('attribute', '$attribute'),\n";
	endforeach;
}
if(!empty($searchPublicVariables)) {
	foreach ($searchPublicVariables as $key=>$val):
		echo "\t\t\t'$key' => Yii::t('attribute', '$val'),\n";
	endforeach;
} ?>
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;
<?php
$isPrimaryKey = '';
$availableRelations = array();

if($searchVariableCondition || $viewRelationCondition) {?>
		$criteria->with = array(
<?php 
if($viewRelationCondition) {
	echo "\t\t\t'view' => array(\n";
	echo "\t\t\t\t'alias' => 'view',\n";
	echo "\t\t\t),\n";
}
foreach($columns as $name=>$column) {
	if($column->isForeignKey) {
		$smallintCondition = 0;
		$relationName = $this->setRelation($column->name, true);
		$relationTableName = trim($foreignKeys[$column->name]);
		$relationAttribute = $this->tableRelationAttribute($relationTableName, '.');
		if(preg_match('/(smallint)/', $column->dbType))
			$smallintCondition = 1;
		
		if(!$smallintCondition && !in_array($relationName, $availableRelations)) {
			$table2ndRelation = count(explode('.', $relationAttribute)) == 1 ? $relationName : join('.', array($relationName, $this->table2ndRelation($relationAttribute)));
			$table2ndAttribute = $this->table2ndAttribute($relationAttribute);
			echo "\t\t\t'$table2ndRelation' => array(\n";
			echo "\t\t\t\t'alias' => '$relationName',\n";
			echo "\t\t\t\t'select' => '$table2ndAttribute',\n";
			echo "\t\t\t),\n";
			$availableRelations[] = $relationName;
		}
	}
}
if($i18n):
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)):
		$relationName = $this->i18nRelation($column->name);
		if(!in_array($relationName, $availableRelations)) {
			echo "\t\t\t'$relationName' => array(\n";
			echo "\t\t\t\t'alias' => '$relationName',\n";
			echo "\t\t\t\t'select' => 'message',\n";
			echo "\t\t\t),\n";
			$availableRelations[] = $relationName;
		}
	endif;
endforeach;
endif;
foreach($columns as $name=>$column) {
	if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
		$relationName = $this->setRelation($column->name, true);
		if(!in_array($relationName, $availableRelations)) {
			$relationAttribute = 'displayname';
			if($column->name == 'member_id')
				$relationAttribute = 'member_name';
			elseif($column->name == 'tag_id')
				$relationAttribute = 'body';
	
			echo "\t\t\t'$relationName' => array(\n";
			echo "\t\t\t\t'alias' => '$relationName',\n";
			echo "\t\t\t\t'select' => '$relationAttribute',\n";
			echo "\t\t\t),\n";
			$availableRelations[] = $relationName;
		}
	}
}?>
		);

<?php }
foreach($columns as $name=>$column) {
	if($column->name[0] == '_')
		continue;

	if($column->name == 'publish') {
		if($tableViewCondition)
			echo "\t\t\$criteria->compare('t.$column->name', \$this->$column->name);\n";
		else {
			echo "\t\tif(Yii::app()->getRequest()->getParam('type') == 'publish')\n";
			echo "\t\t\t\$criteria->compare('t.$column->name', 1);\n";
			echo "\t\telseif(Yii::app()->getRequest()->getParam('type') == 'unpublish')\n";
			echo "\t\t\t\$criteria->compare('t.$column->name', 0);\n";
			echo "\t\telseif(Yii::app()->getRequest()->getParam('type') == 'trash')\n";
			echo "\t\t\t\$criteria->compare('t.$column->name', 2);\n";
			echo "\t\telse {\n";
			echo "\t\t\t\$criteria->addInCondition('t.$column->name', array(0,1));\n";
			echo "\t\t\t\$criteria->compare('t.$column->name', \$this->$column->name);\n";
			echo "\t\t}\n";
		}

	} else if($column->isForeignKey || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
		$relationName = $this->setRelation($column->name, true);
		echo "\t\t\$criteria->compare('t.$column->name', Yii::app()->getRequest()->getParam('$relationName') ? Yii::app()->getRequest()->getParam('$relationName') : \$this->$column->name);\n";

	} else if(in_array($column->dbType, array('timestamp','datetime'))) {
		echo "\t\tif(\$this->$column->name != null && !in_array(\$this->$column->name, array('0000-00-00 00:00:00','1970-01-01 00:00:00','0002-12-02 07:07:12','-0001-11-30 00:00:00')))\n";
		echo "\t\t\t\$criteria->compare('date(t.$column->name)', date('Y-m-d', strtotime(\$this->$column->name)));\n";

	} else if(in_array($column->dbType, array('date'))) {
		echo "\t\tif(\$this->$column->name != null && !in_array(\$this->$column->name, array('0000-00-00','1970-01-01','0002-12-02','-0001-11-30')))\n";
		echo "\t\t\t\$criteria->compare('date(t.$column->name)', date('Y-m-d', strtotime(\$this->$column->name)));\n";

	} else if(preg_match('/(int)/', $column->dbType) || ($column->type==='string' && $column->isPrimaryKey == '1'))
		if($column->dbType == 'tinyint(1)' && $column->name != 'permission')
			echo "\t\t\$criteria->compare('t.$column->name', Yii::app()->getRequest()->getParam('$column->name') ? Yii::app()->getRequest()->getParam('$column->name') : \$this->$column->name);\n";
		else
			echo "\t\t\$criteria->compare('t.$column->name', \$this->$column->name);\n";

	else if($column->type==='string') {
		if($tableViewCondition && preg_match('/(decimal)/', $column->dbType))
			echo "\t\t\$criteria->compare('t.$column->name', \$this->$column->name);\n";
		else
			echo "\t\t\$criteria->compare('t.$column->name', strtolower(\$this->$column->name), true);\n";

	} else
		echo "\t\t\$criteria->compare('t.$column->name', \$this->$column->name);\n";

	if($column->isPrimaryKey)
		$isPrimaryKey = $column->name;
}
if($searchVariableCondition)
	echo "\n";
$publicAttributes = array();
foreach($columns as $name=>$column) {	
	if($column->isForeignKey) {
		$smallintCondition = 0;
		$relationName = $this->setRelation($column->name, true);
		$relationTableName = trim($foreignKeys[$column->name]);
		$relationAttribute = $this->tableRelationAttribute($relationTableName, '.');
		$publicAttribute = $relationName.'_search';
		if(preg_match('/(smallint)/', $column->dbType))
			$smallintCondition = 1;

		if(!$smallintCondition && !in_array($publicAttribute, $publicAttributes)) {
			$relationPlusAttribute = join('.', array($relationName, $relationAttribute));
			$table2ndAttribute = join('.', array($relationName, $this->table2ndAttribute($relationAttribute)));
			echo "\t\t\$criteria->compare('$table2ndAttribute', strtolower(\$this->$publicAttribute), true);\t\t\t//$relationPlusAttribute\n";
			$publicAttributes[] = $publicAttribute;
		}
	}
}
if($i18n):
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)):
		$publicAttribute = $column->name.'_i';
		$publicAttributeRelation = $this->i18nRelation($column->name);

		if(!in_array($publicAttribute, $publicAttributes)) {
			echo "\t\t\$criteria->compare('$publicAttributeRelation.message', strtolower(\$this->$publicAttribute), true);\n";
			$publicAttributes[] = $publicAttribute;
		}
	endif;
endforeach;
endif;
foreach($columns as $name=>$column) {
	if(in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
		$relationName = $this->setRelation($column->name, true);
		$publicAttribute = $relationName.'_search';
		$relationAttribute = 'displayname';
		if($column->name == 'member_id')
			$relationAttribute = 'member_name';
		else if($column->name == 'tag_id') {
			$publicAttribute = $relationName.'_i';
			$relationAttribute = 'body';
		}

		if(!in_array($publicAttribute, $publicAttributes)) {
			$relationAttribute = join('.', array($relationName, $relationAttribute));
			echo "\t\t\$criteria->compare('$relationAttribute', strtolower(\$this->$publicAttribute), true);\n";
			$publicAttributes[] = $publicAttribute;
		}
	}
}
if($viewRelationCondition && !empty($manyRelationPublicVariables)) {
	foreach ($manyRelationPublicVariables as $key=>$val) {
		echo "\t\t\$criteria->compare('view.{$val}', \$this->{$key});\n";
	}
}

	if($tableViewCondition && !$isPrimaryKey)
		$isPrimaryKey = $primaryKey;

	echo "\n\t\tif(!Yii::app()->getRequest()->getParam('{$modelClass}_sort'))\n";
	echo "\t\t\t\$criteria->order = 't.$isPrimaryKey DESC';\n";
?>

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>Yii::app()->params['grid-view'] ? Yii::app()->params['grid-view']['pageSize'] : 50,
			),
		));
	}

	/**
	 * Set default columns to display
	 */
	protected function afterConstruct() {
		if(count($this->templateColumns) == 0) {
			$this->templateColumns['_option'] = array(
				'class' => 'CCheckBoxColumn',
				'name' => 'id',
				'selectableRows' => 2,
				'checkBoxHtmlOptions' => array('name' => 'trash_id[]')
			);
			$this->templateColumns['_no'] = array(
				'header' => Yii::t('app', 'No'),
				'value' => '$this->grid->dataProvider->pagination->currentPage*$this->grid->dataProvider->pagination->pageSize + $row+1',
				'htmlOptions' => array(
					'class' => 'center',
				),
			);
<?php
foreach($columns as $name=>$column)
{
	if($column->name[0] == '_')
		continue;
	if($column->isPrimaryKey || ($column->dbType == 'tinyint(1)' && $column->name != 'permission'))
		continue;

	$commentArray = explode(',', $column->comment);
		
	if($column->isForeignKey || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
		$smallintCondition = 0;
		if(preg_match('/(smallint)/', $column->dbType))
			$smallintCondition = 1;
		$relationName = $this->setRelation($column->name, true);
		$publicAttribute = $relationName.'_search';
		$relationAttribute = 'displayname';
		if($column->name == 'member_id')
			$relationAttribute = 'member_name';
		else if($column->name == 'tag_id') {
			$publicAttribute = $relationName.'_i';
			$relationAttribute = 'body';
		}
		if($column->isForeignKey) {
			$relationTableName = trim($foreignKeys[$column->name]);
			$relationAttribute = $this->tableRelationAttribute($relationTableName, '->');
		}
		if($smallintCondition)
			$publicAttribute = $column->name;

		$relationAttribute = join('->', array($relationName, $relationAttribute));
			
		echo "\t\t\tif(!Yii::app()->getRequest()->getParam('$relationName')) {\n";
		echo "\t\t\t\t\$this->templateColumns['$publicAttribute'] = array(\n";
		echo "\t\t\t\t\t'name' => '$publicAttribute',\n";
		echo "\t\t\t\t\t'value' => '\$data->$relationAttribute ? \$data->$relationAttribute : \'-\'',\n";
		if($column->isForeignKey && $smallintCondition) {
			$relationClassName = $this->generateClassName($relationTableName);
			$functionName = $inflector->singularize($this->setRelation($relationClassName));
			$relationFunction = ucfirst($functionName);
			echo "\t\t\t\t\t'filter' => $relationClassName::get{$relationFunction}(),\n";
		}
		echo "\t\t\t\t);\n";
		echo "\t\t\t}\n";
		
	} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
		echo "\t\t\t\$this->templateColumns['$column->name'] = array(\n";
		echo "\t\t\t\t'name' => '$column->name',\n";
		if(in_array($column->dbType, array('timestamp','datetime')))
			echo "\t\t\t\t'value' => '!in_array(\$data->$column->name, array(\'0000-00-00 00:00:00\', \'1970-01-01 00:00:00\', \'0002-12-02 07:07:12\', \'-0001-11-30 00:00:00\')) ? Yii::app()->dateFormatter->formatDateTime(\$data->$column->name, \'medium\', false) : \'-\'',\n";
		else
			echo "\t\t\t\t'value' => '!in_array(\$data->$column->name, array(\'0000-00-00\', \'1970-01-01\', \'0002-12-02\', \'-0001-11-30\')) ? Yii::app()->dateFormatter->formatDateTime(\$data->$column->name, \'medium\', false) : \'-\'',\n";
		echo "\t\t\t\t'htmlOptions' => array(\n";
		echo "\t\t\t\t\t'class' => 'center',\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'filter' => \$this->filterDatepicker(\$this, '$column->name'),\n";
		echo "\t\t\t);\n";
		
	} else {
		$translateCondition = 0;
		$publicAttribute = $column->name;
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $column->name.'_i';
			$relationName = $this->i18nRelation($column->name);
			$translateCondition = 1;
		}
		echo "\t\t\t\$this->templateColumns['$publicAttribute'] = array(\n";
		echo "\t\t\t\t'name' => '$publicAttribute',\n";
if($translateCondition) {
	echo "\t\t\t\t'value' => '\$data->{$relationName}->message',\n";
} else {
	if($column->dbType == 'text' && in_array('file', $commentArray)) {
		if($this->uploadPathSubfolder)
			echo "\t\t\t\t'value' => '\$data->$column->name ? CHtml::link(\$data->$column->name, join(\'/\', array(Yii::app()->request->baseUrl, self::getUploadPath(false), \$data->$primaryKey, \$data->$column->name), array(\'target\' => \'_blank\')) : \'-\'',\n";
		else
			echo "\t\t\t\t'value' => '\$data->$column->name ? CHtml::link(\$data->$column->name, join(\'/\', array(Yii::app()->request->baseUrl, self::getUploadPath(false), \$data->$column->name)), array(\'target\' => \'_blank\')) : \'-\'',\n";
	} else if($column->dbType == 'text' && in_array('serialize', $commentArray)) {
		echo "\t\t\t\t'value' => 'serialize(\$data->$column->name)',\n";
	} else {
		if($column->name === 'permission')
			echo "\t\t\t\t'value' => '\$data->$column->name ? Yii::t(\'phrase\', \'Yes\') : Yii::t(\'phrase\, \'No\')',\n";
		else
			echo "\t\t\t\t'value' => '\$data->$column->name',\n";
	}
}
if((in_array($column->dbType, array('text')) && (in_array('file', $commentArray) || in_array('redactor', $commentArray))) && $column->name != 'slug')
	echo "\t\t\t\t'type' => 'raw',\n";
		echo "\t\t\t);\n";
	}
}
if(!empty($manyRelationPublicVariables)) {
	foreach ($manyRelationPublicVariables as $key=>$val) {
		$controller = $inflector->singularize($val);
		if($controller == $this->moduleName)
			$controller = 'admin';
		$attribute = $inflector->singularize($this->setRelation($modelClass));
		echo "\t\t\t\$this->templateColumns['$key'] = array(\n";
		echo "\t\t\t\t'name' => '$key',\n";
		echo "\t\t\t\t'value' => 'CHtml::link(\$data->$key ? \$data->$key : 0, Yii::app()->controller->createUrl(\'o/$controller/manage\', array(\'$attribute\'=>\$data->$isPrimaryKey)))',\n";
		echo "\t\t\t\t'htmlOptions' => array(\n";
		echo "\t\t\t\t\t'class' => 'center',\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'filter' => false,\n";
		echo "\t\t\t\t'type' => 'raw',\n";
		echo "\t\t\t);\n";
	}
}
foreach($columns as $name=>$column)
{
	$comment = $column->comment;
	if($column->dbType == 'tinyint(1)' && in_array($column->name, array('publish','headline','permission')))
		continue;

	if($column->dbType == 'tinyint(1)' && $comment != '' && $comment[0] == '"') {
		$functionName = ucfirst($inflector->singularize($inflector->id2camel($column->name, '_')));

		echo "\t\t\t\$this->templateColumns['$column->name'] = array(\n";
		echo "\t\t\t\t'name' => '$column->name',\n";
		echo "\t\t\t\t'value' => 'Utility::getPublish(Yii::app()->controller->createUrl(\'$column->name\', array(\'id\'=>\$data->$isPrimaryKey)), \$data->$column->name, $modelClass::get$functionName())',\n";
		echo "\t\t\t\t'htmlOptions' => array(\n";
		echo "\t\t\t\t\t'class' => 'center',\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'filter' => self::get$functionName(),\n";
		echo "\t\t\t\t'type' => 'raw',\n";
		echo "\t\t\t);\n";
	}
}
foreach($columns as $name=>$column)
{
	$comment = $column->comment;
	if($column->dbType == 'tinyint(1)' && (in_array($column->name, array('publish','headline','permission')) || $comment != ''))
		continue;

	if($column->dbType == 'tinyint(1)') {
		echo "\t\t\t\$this->templateColumns['$column->name'] = array(\n";
		echo "\t\t\t\t'name' => '$column->name',\n";
		echo "\t\t\t\t'value' => '\$data->$column->name ? CHtml::image(Yii::app()->theme->baseUrl.\'/images/icons/publish.png\') : CHtml::image(Yii::app()->theme->baseUrl.\'/images/icons/unpublish.png\')',\n";
		echo "\t\t\t\t'htmlOptions' => array(\n";
		echo "\t\t\t\t\t'class' => 'center',\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'filter' => \$this->filterYesNo(),\n";
		echo "\t\t\t\t'type' => 'raw',\n";
		echo "\t\t\t);\n";
	}
}
foreach($columns as $name=>$column)
{
	$comment = $column->comment;
	if($column->dbType == 'tinyint(1)' && in_array($column->name, array('publish','headline','permission')))
		continue;

	if($column->dbType == 'tinyint(1)' && $comment != '' && $comment[0] != '"') {
		echo "\t\t\t\$this->templateColumns['$column->name'] = array(\n";
		echo "\t\t\t\t'name' => '$column->name',\n";
		echo "\t\t\t\t'value' => 'Utility::getPublish(Yii::app()->controller->createUrl(\'$column->name\', array(\'id\'=>\$data->$isPrimaryKey)), \$data->$column->name, \'$comment\')',\n";
		//echo "\t\t\t\t//'value' => '\$data->$column->name == 1 ? CHtml::image(Yii::app()->theme->baseUrl.\'/images/icons/publish.png\') : CHtml::image(Yii::app()->theme->baseUrl.\'/images/icons/unpublish.png\')',\n";
		echo "\t\t\t\t'htmlOptions' => array(\n";
		echo "\t\t\t\t\t'class' => 'center',\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'filter' => \$this->filterYesNo(),\n";
		echo "\t\t\t\t'type' => 'raw',\n";
		echo "\t\t\t);\n";
	}
}
foreach($columns as $name=>$column)
{
	$comment = $column->comment;
	if($column->name == 'headline' && $comment == '')
		$comment = 'Headline,Unheadline';

	if($column->dbType == 'tinyint(1)' && $column->name == 'headline') {
		echo "\t\t\t\$this->templateColumns['$column->name'] = array(\n";
		echo "\t\t\t\t'name' => '$column->name',\n";
		echo "\t\t\t\t'value' => 'Utility::getPublish(Yii::app()->controller->createUrl(\'$column->name\', array(\'id\'=>\$data->$isPrimaryKey)), \$data->$column->name, \'$comment\')',\n";
		//echo "\t\t\t\t//'value' => '\$data->$column->name == 1 ? CHtml::image(Yii::app()->theme->baseUrl.\'/images/icons/publish.png\') : CHtml::image(Yii::app()->theme->baseUrl.\'/images/icons/unpublish.png\')',\n";
		echo "\t\t\t\t'htmlOptions' => array(\n";
		echo "\t\t\t\t\t'class' => 'center',\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'filter' => \$this->filterYesNo(),\n";
		echo "\t\t\t\t'type' => 'raw',\n";
		echo "\t\t\t);\n";
	}
}
foreach($columns as $name=>$column)
{
	$comment = $column->comment;
	if($column->dbType == 'tinyint(1)' && $column->name == 'publish') {
		echo "\t\t\tif(!Yii::app()->getRequest()->getParam('type')) {\n";
		echo "\t\t\t\t\$this->templateColumns['$column->name'] = array(\n";
		echo "\t\t\t\t\t'name' => '$column->name',\n";
		if($comment == '')
			echo "\t\t\t\t\t'value' => 'Utility::getPublish(Yii::app()->controller->createUrl(\'$column->name\', array(\'id\'=>\$data->$isPrimaryKey)), \$data->$column->name)',\n";
		else
			echo "\t\t\t\t\t'value' => 'Utility::getPublish(Yii::app()->controller->createUrl(\'$column->name\', array(\'id\'=>\$data->$isPrimaryKey)), \$data->$column->name, \'$comment\')',\n";
		//echo "\t\t\t\t\t//'value' => '\$data->$column->name == 1 ? CHtml::image(Yii::app()->theme->baseUrl.\'/images/icons/publish.png\') : CHtml::image(Yii::app()->theme->baseUrl.\'/images/icons/unpublish.png\')',\n";
		echo "\t\t\t\t\t'htmlOptions' => array(\n";
		echo "\t\t\t\t\t\t'class' => 'center',\n";
		echo "\t\t\t\t\t),\n";
		echo "\t\t\t\t\t'filter' => \$this->filterYesNo(),\n";
		echo "\t\t\t\t\t'type' => 'raw',\n";
		echo "\t\t\t\t);\n";
		echo "\t\t\t}\n";
	}
}
?>
		}
		parent::afterConstruct();
	}

	/**
	 * Model get information
	 */
	public static function getInfo(<?php echo $permissionCondition ? '$column=null' : '$id, $column=null';?>)
	{
		if($column != null) {
			$model = self::model()->findByPk(<?php echo  $permissionCondition ? '1' : '$id';?>, array(
				'select' => $column,
			));
			if(count(explode(',', $column)) == 1)
				return $model->$column;
			else
				return $model;
			
		} else {
			$model = self::model()->findByPk(<?php echo  $permissionCondition ? '1' : '$id';?>);
			return $model;
		}
	}
<?php 
if(!$tableViewCondition && ($this->useGetFunction || $generateFunctionCondition)) {
	$functionName = $inflector->singularize($this->setRelation($modelClass));?>

	/**
	 * function get<?php echo ucfirst($functionName)."\n";?>
	 */
	public static function get<?php echo ucfirst($functionName);?>(<?php echo $publishCondition ? '$publish=null, $array=true' : '$array=true';?>) 
	{
		$criteria=new CDbCriteria;
<?php if($publishCondition):?>
		if($publish != null)
			$criteria->compare('t.publish', $publish);

<?php endif;?>
		$model = self::model()->findAll($criteria);

		if($array == true) {
			$items = array();
			if($model != null) {
				foreach($model as $key => $val) {
<?php 
$tableAttribute = $this->tableAttribute($columns);
if($i18n):
	$i18nRelation = preg_match('/(name|title)/', $tableAttribute) ? 'title' : '';?>
					$items[$val-><?php echo $isPrimaryKey;?>] = $val-><?php echo $i18nRelation ? $i18nRelation.'->message' : $tableAttribute;?>;
<?php else:?>
					$items[$val-><?php echo $isPrimaryKey;?>] = $val-><?php echo $tableAttribute;?>;
<?php endif;?>
				}
				return $items;
			} else
				return false;
		} else
			return $model;
	}
<?php }

$columnFunctionArray = array();
foreach($columns as $name=>$column) {
	if($column->comment[0] == '"')
		$columnFunctionArray[$column->name] = $column->comment;
}
if(!$tableViewCondition || !empty($columnFunctionArray)) {
	foreach($columnFunctionArray as $key=>$val) {
		$functionName = $inflector->singularize($inflector->id2camel($key, '_'));
		$itemArray = $this->commentToArray($val);?>

	/**
	 * function get<?php echo ucfirst($functionName)."\n";?>
	 */
	public static function get<?php echo ucfirst($functionName);?>($value=null)
	{
		$items = array(
<?php foreach($itemArray as $key=>$val) {?>
			'<?php echo $key;?>'=>Yii::t('phrase', '<?php echo ucfirst($val);?>'),
<?php }?>
		);

		if($value != null)
			return $items[$value];
		else
			return $items;
	}
<?php }
}
if($uploadCondition) {?>

	/**
	 * @param returnAlias set true jika ingin kembaliannya path alias atau false jika ingin string
	 * relative path. default true.
	 */
	public static function getUploadPath($returnAlias=true) 
	{
		return ($returnAlias ? join('/', array(Yii::getPathOfAlias('webroot'), '<?php echo $this->uploadPathDirectory;?>')) : '<?php echo $this->uploadPathDirectory;?>');
	}
<?php }

$afEvents = 0;
$afterFind = 0;
if($tagCondition || $uploadCondition || $serializeCondition || $i18n || ($viewRelationCondition && !empty($manyRelationPublicVariables)))
	$afEvents = 1;
if(!$tableViewCondition && ($this->useEvent || $afEvents)) {?>

	/**
	 * This is invoked when a record is populated with data from a find() call.
	 */
	protected function afterFind()
	{
		parent::afterFind();
<?php
foreach($columns as $name=>$column) {
	$commentArray = explode(',', $column->comment);
	if(in_array($column->name, ['tag_id'])) {
		$relationName = $this->setRelation($column->name, true);
		$publicAttribute = $relationName.'_i';
		echo "\t\t\$this->$publicAttribute = \$this->{$relationName}->body;\n";
		$afterFind = 1;
	} else if($column->dbType == 'text' && in_array('file', $commentArray)) {
		$publicAttribute = 'old_'.$column->name.'_i';
		echo "\t\t\$this->$publicAttribute = \$this->$column->name;\n";
		$afterFind = 1;
	} else if($column->dbType == 'text' && in_array('serialize', $commentArray)) {
		echo "\t\t\$this->$column->name = unserialize(\$this->$column->name);\n";
		$afterFind = 1;
	} else {
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $column->name.'_i';
			$relationName = $this->i18nRelation($column->name);
			echo "\t\t\$this->$publicAttribute = \$this->{$relationName}->message;\n";
			$afterFind = 1;
		}
	}
}
if($viewRelationCondition && !empty($manyRelationPublicVariables)) {
	foreach ($manyRelationPublicVariables as $key=>$val) {
		echo "\t\t\$this->$key = \$this->view->{$val};\n";
		$afterFind = 1;
	}
}
echo !$afterFind ? "\t\t// Create action\n\n" : '';?>

		return true;
	}
<?php }

$bvEvents = 0;
$beforeValidate = 0;
$creationCondition = 0;
if($uploadCondition)
	$bvEvents = 1;
foreach($columns as $name=>$column) {
	$columnArray = explode('_', $column->name);
	if(in_array($column->name, array('creation_id','modified_id','updated_id','user_id')) && $column->comment != 'trigger')
		$bvEvents = 1;
	if(in_array('ip', $columnArray))
		$bvEvents = 1;
}
if(!$tableViewCondition && ($this->useEvent || $bvEvents)) {?>

	/**
	 * before validate attributes
	 */
	protected function beforeValidate() 
	{
		if(parent::beforeValidate()) {
<?php
if($uploadCondition) {
	foreach($columns as $name=>$column) {
		$commentArray = explode(',', $column->comment);
		if($column->dbType == 'text' && in_array('file', $commentArray)) {
			$fileType = $inflector->singularize($inflector->id2camel($column->name, '_')).'FileType';?>
			$<?php echo $fileType;?> = array('bmp','gif','jpg','png');
			$<?php echo $column->name;?> = CUploadedFile::getInstance($this, '<?php echo $column->name;?>');
			if($<?php echo $column->name;?> != null) {
				$extension = pathinfo($<?php echo $column->name;?>->name, PATHINFO_EXTENSION);
				if(!in_array(strtolower($extension), $<?php echo $fileType;?>))
					$this->addError('<?php echo $column->name;?>', Yii::t('phrase', 'The file {name} cannot be uploaded. Only files with these extensions are allowed: {extensions}.', array(
						'{name}'=>$<?php echo $column->name;?>->name,
						'{extensions}'=>Utility::formatFileType($<?php echo $fileType;?>, false),
					)));
			} /* else {
				//if($this->isNewRecord)
					$this->addError('<?php echo $column->name;?>', Yii::t('phrase', '{attribute} cannot be blank.', array('{attribute}'=>$this->getAttributeLabel('<?php echo $column->name;?>'))));
			} */

<?php		$beforeValidate = 1;
		}
	}
}
foreach($columns as $name=>$column) {
	if(in_array($column->name, array('creation_id','modified_id','updated_id','user_id')) && $column->comment != 'trigger') {
		if(in_array($column->name, array('creation_id','user_id'))) {
			$creationCondition = 1;
			echo "\t\t\tif(\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->$column->name = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;\n";
		} else {
			if($creationCondition)
				echo "\t\t\telse\n";
			else
				echo "\t\t\tif(!\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->$column->name = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;\n";
		}
		$beforeValidate = 1;
	}
}
foreach($columns as $name=>$column) {
	$nameArray = explode('_', $column->name);
	if(in_array('ip', $nameArray)) {?>
			$this-><?php echo $column->name;?> = $_SERVER['REMOTE_ADDR'];
<?php $beforeValidate = 1;
	}
}
echo !$beforeValidate ? "\t\t\t// Create action\n" : '';?>
		}
		return true;
	}
<?php }

$avEvents = 0;
$afterValidate = 0;
if(!$tableViewCondition && ($this->useEvent || $avEvents)) {?>

	/**
	 * after validate attributes
	 */
	protected function afterValidate()
	{
		parent::afterValidate();
		// Create action
		
		return true;
	}
<?php }

$bsEvents = 0;
$beforeSave = 0;
if($i18n || $uploadCondition || $serializeCondition || $tagCondition)
	$bsEvents = 1;
foreach($columns as $name=>$column) {
	if(in_array($column->type, ['date','datetime']) && $column->comment != 'trigger')
		$bsEvents = 1;
}
if(!$tableViewCondition && ($this->useEvent || $bsEvents)) {?>

	/**
	 * before save attributes
	 */
	protected function beforeSave()
	{
<?php if($i18n) {?>
		$module = strtolower(Yii::app()->controller->module->id);
		$controller = strtolower(Yii::app()->controller->id);
		$action = strtolower(Yii::app()->controller->action->id);

		$location = $this->urlTitle($module.' '.$controller);
		
<?php }?>
		if(parent::beforeSave()) {
<?php 
if($uploadCondition) {
	if($uploadCondition && !$this->uploadPathSubfolder) {?>
			// create directory
			$this->createUploadDirectory(self::getUploadPath());

			$uploadPath = self::getUploadPath();
			$verwijderenPath = join('/', array(self::getUploadPath(), 'verwijderen'));

<?php }?>
			if(!$this->isNewRecord) {
<?php if($this->uploadPathSubfolder) {?>
				// create directory
				$this->createUploadDirectory(self::getUploadPath(), $this-><?php echo $primaryKey;?>);
				
				$uploadPath = join('/', array(self::getUploadPath(), $this-><?php echo $primaryKey;?>));
				$verwijderenPath = join('/', array(self::getUploadPath(), 'verwijderen'));

<?php }
foreach($columns as $name=>$column) {
	$commentArray = explode(',', $column->comment);
	if($column->dbType == 'text' && in_array('file', $commentArray)) {?>
				$this-><?php echo $column->name;?> = CUploadedFile::getInstance($this, '<?php echo $column->name;?>');
				if($this-><?php echo $column->name;?> != null) {
					if($this-><?php echo $column->name;?> instanceOf CUploadedFile) {
						$fileName = time().'_'.$this-><?php echo $primaryKey;?>.'.'.strtolower($this-><?php echo $column->name;?>->extensionName);
						if($this-><?php echo $column->name;?>->saveAs(join('/', array($uploadPath, $fileName)))) {
							if($this->old_<?php echo $column->name;?>_i != '' && file_exists(join('/', array($uploadPath, $this->old_<?php echo $column->name;?>_i))))
								rename(join('/', array($uploadPath, $this->old_<?php echo $column->name;?>_i)), join('/', array($verwijderenPath, time().'_change_'.$this->old_<?php echo $column->name;?>_i)));
							$this-><?php echo $column->name;?> = $fileName;
						}
					}
				} else {
					if($this-><?php echo $column->name;?> == '')
						$this-><?php echo $column->name;?> = $this->old_<?php echo $column->name;?>_i;
				}
<?php }
}
$beforeSave = 1;?>
			}

<?php }
foreach($columns as $name=>$column) {
	$commentArray = explode(',', $column->comment);
	if(in_array($column->dbType, array('date','datetime')) && $column->comment != 'trigger') {
		$datetimeType = $column->dbType == 'date' ? 'Y-m-d' : 'Y-m-d';	//Y-m-d H:i:s ?>
			$this-><?php echo $column->name;?> = date('Y-m-d', strtotime($this-><?php echo $column->name;?>));
<?php 	$beforeSave = 1;
	} else if($column->dbType == 'text' && in_array('serialize', $commentArray)) {?>
			$this-><?php echo $column->name;?> = serialize($this-><?php echo $column->name;?>);
<?php 	$beforeSave = 1;
	} else if($column->name == 'tag_id') {
		$relationName = $this->setRelation($column->name, true);
		$publicAttribute = $relationName.'_i';?>
			if($this->isNewRecord) {
				$<?php echo $publicAttribute;?> = $this->urlTitle($this-><?php echo $publicAttribute;?>);
				if($this-><?php echo $column->name;?> == 0) {
					$<?php echo $relationName;?> = OmmuTags::model()->find(array(
						'select' => '<?php echo $column->name;?>, body',
						'condition' => 'body = :body',
						'params' => array(
							':body' => $<?php echo $publicAttribute;?>,
						),
					));
					if($<?php echo $relationName;?> != null)
						$this-><?php echo $column->name;?> = $<?php echo $relationName;?>-><?php echo $column->name;?>;
					else {
						$data = new OmmuTags;
						$data->body = $this-><?php echo $publicAttribute;?>;
						if($data->save())
							$this-><?php echo $column->name;?> = $data-><?php echo $column->name;?>;
					}
				}
			}
<?php $beforeSave = 1;
	} else {
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $column->name.'_i';
			$publicAttributeLocation = preg_match('/(name|title)/', $column->name) ? '_title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? '_description' : '_'.$column->name) : '_'.$column->name);?>
			if($this->isNewRecord || (!$this->isNewRecord && !$this-><?php echo $column->name;?>)) {
				$<?php echo $column->name;?>=new SourceMessage;
				$<?php echo $column->name;?>->message = $this-><?php echo $publicAttribute;?>;
				$<?php echo $column->name;?>->location = $location.'<?php echo $publicAttributeLocation;?>';
				if($<?php echo $column->name;?>->save())
					$this-><?php echo $column->name;?> = $<?php echo $column->name;?>->id;
<?php if($slugCondition && $i18n && preg_match('/(name|title)/', $column->name)) {?>

				$this->slug = $this->urlTitle($this-><?php echo $publicAttribute;?>);
<?php }?>

			} else {
				$<?php echo $column->name;?> = SourceMessage::model()->findByPk($this-><?php echo $column->name;?>);
				$<?php echo $column->name;?>->message = $this-><?php echo $publicAttribute;?>;
				$<?php echo $column->name;?>->save();
			}

<?php		$beforeSave = 1;
		}
	}
}
echo !$beforeSave ? "\t\t\t// Create action\n" : '';?>
		}
		return true;
	}
<?php }

$asEvents = 0;
$afterSave = 0;
if($uploadCondition)
	$asEvents = 1;
if(!$tableViewCondition && ($this->useEvent || $asEvents)) {?>

	/**
	 * After save attributes
	 */
	protected function afterSave() 
	{
		parent::afterSave();
<?php if($uploadCondition) {
	echo "\n";
	if($this->uploadPathSubfolder) {?>
		// create directory
		$this->createUploadDirectory(self::getUploadPath(), $this-><?php echo $primaryKey;?>);

		$uploadPath = join('/', array(self::getUploadPath(), $this-><?php echo $primaryKey;?>));
<?php } else {?>
		$uploadPath = self::getUploadPath();
<?php }?>
		$verwijderenPath = join('/', array(self::getUploadPath(), 'verwijderen'));

		if($this->isNewRecord) {
<?php foreach($columns as $name=>$column) {
	$commentArray = explode(',', $column->comment);
	if($column->dbType == 'text' && in_array('file', $commentArray)) {?>
			$this-><?php echo $name;?> = CUploadedFile::getInstance($this, '<?php echo $name;?>');
			if($this-><?php echo $name;?> != null) {
				if($this-><?php echo $name;?> instanceOf CUploadedFile) {
					$fileName = time().'_'.$this-><?php echo $primaryKey;?>.'.'.strtolower($this-><?php echo $name;?>->extensionName);
					if($this-><?php echo $name;?>->saveAs(join('/', array($uploadPath, $fileName))))
						self::model()->updateByPk($this-><?php echo $primaryKey;?>, array('<?php echo $name;?>'=>$fileName));
				}
			}

<?php }
}
$afterSave = 1;?>
		}
<?php }
echo !$afterSave ? "\t\t// Create action\n" : '';?>
	}
<?php }

$bdEvents = 0;
$beforeDelete = 0;
if(!$tableViewCondition && ($this->useEvent || $bdEvents)) {?>

	/**
	 * Before delete attributes
	 */
	protected function beforeDelete() 
	{
		if(parent::beforeDelete()) {
			// Create action
		}
		return true;
	}
<?php }

$adEvents = 0;
$afterDelete = 0;
if($uploadCondition)
	$adEvents = 1;
if(!$tableViewCondition && ($this->useEvent || $adEvents)) {?>

	/**
	 * After delete attributes
	 */
	protected function afterDelete() 
	{
		parent::afterDelete();
<?php if($uploadCondition) {
	echo "\n\t\t//delete image\n";
	if($this->uploadPathSubfolder) {?>
		$uploadPath = join('/', array(self::getUploadPath(), $this-><?php echo $primaryKey;?>));
<?php } else {?>
		$uploadPath = self::getUploadPath();
<?php }?>
		$verwijderenPath = join('/', array(self::getUploadPath(), 'verwijderen'));

<?php foreach($columns as $name=>$column) {
	$commentArray = explode(',', $column->comment);
	if($column->dbType == 'text' && in_array('file', $commentArray)) {?>
		if($this-><?php echo $name;?> != '' && file_exists(join('/', array($uploadPath, $this-><?php echo $name;?>))))
			rename(join('/', array($uploadPath, $this-><?php echo $name;?>)), join('/', array($verwijderenPath, time().'_deleted_'.$this-><?php echo $name;?>)));
<?php 	}
	}
	$afterDelete = 1;

	if($this->uploadPathSubfolder)
		echo "\n\t\t\$this->deleteFolder(\$uploadPath);\n";
}
echo !$afterDelete ? "\t\t// Create action\n" : '';?>
	}
<?php }?>
}