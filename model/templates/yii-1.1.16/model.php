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
 */
 
/* 
* set name relation with underscore
*/
function setRelationName($names, $column=false) {
	$patterns = array();
	$patterns[0] = '(_ommu)';
	$patterns[1] = '(_core)';
	
	if($column == false) {
		$char=range("A","Z");
		foreach($char as $val) {
			if(strpos($names, $val) !== false) {
				$names = str_replace($val, '_'.strtolower($val), $names);
			}
		}
	} else
		$names = rtrim($names, 'id');

	$return = trim(preg_replace($patterns, '', $names), '_');
	$return = array_map('strtolower', explode('_', $return));
	//print_r($return);

	if(count($return) != 1)
		return end($return);
	else {
		if(is_array($return))
			return implode('', $return);
		else
			return $return;
	}
}

function guessNameColumn($columns)
{
	//echo '<pre>';
	//print_r($columns);
	$primaryKey = array();
	foreach ($columns as $key => $column) {
		if($column->isPrimaryKey || $column->autoIncrement)
			$primaryKey[] = $key;
		if(preg_match('/(name|title)/', $key))
			return $key;
	}
	$pk = $primaryKey;

	if(!empty($primaryKey))
		return $pk[0];
	else
		return 'id';
}

$publishCondition = 0;
$slugCondition = 0;
$i18n = 0;
$primaryKeyColumn = key($columns);
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column):
	if($column->dbType == 'tinyint(1)' && in_array($column->name, array('publish','headline')))
		$publishCondition = 1;
	if($column->name == 'slug')
		$slugCondition = 1;
endforeach;

?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $modelClass."\n"; ?>
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (opensource.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($this->modifiedStatus):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php endif; ?>
 * @link <?php echo $this->linkSource."\n";?>
 *
 * This is the model class for table "<?php echo $tableName; ?>".
 *
 * The followings are the available columns in table '<?php echo $tableName; ?>':
<?php foreach($columns as $column): ?>
 * @property <?php echo $column->type.' $'.$column->name."\n"; ?>
<?php endforeach; ?>
<?php if(!empty($relations)): ?>
 *
 * The followings are the available model relations:
<?php 
//echo '<pre>';
//print_r($relations);
	
foreach($relations as $name=>$relation): ?>
 * @property <?php
	if (preg_match("~^array\(self::([^,]+), '([^']+)', '([^']+)'\)$~", $relation, $matches))
	{
		$relationType = $matches[1];
		$relationModel = preg_replace('(Ommu)', '', $matches[2]);
		$relationName = setRelationName($name);
		if($relationName == 'cat')
			$relationName = 'category';

		switch($relationType){
			case 'HAS_ONE':
				echo $relationModel.' $'.$relationName."\n";
			break;
			case 'BELONGS_TO':
				echo $relationModel.' $'.$relationName."\n";
			break;
			case 'HAS_MANY':
				echo $relationModel.'[] $'.$relationName."\n";
			break;
			case 'MANY_MANY':
				echo $relationModel.'[] $'.$relationName."\n";
			break;
			default:
				echo 'mixed $'.$name."\n";
		}
	}
endforeach;
foreach($labels as $name=>$label):
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		if($name == 'member')
			echo " * @property Members \${$relationName};\n";
		else
			echo " * @property Users \${$relationName};\n";
	} else if($name == 'tag_id') {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		echo " * @property OmmuTags \${$relationName};\n";
	}
endforeach;
endif; ?>
 */
class <?php echo $modelClass; ?> extends <?php echo $this->baseClass."\n"; ?>
{
	public $gridForbiddenColumn = array();
<?php 
$publicVariable = array();
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$publicAttribute = $name.'_i';
		echo "\tpublic \${$publicAttribute};\n";
		$publicVariable[] = $publicAttribute;
		$i18n = 1;
	}
endforeach;
//echo '<pre>';
//print_r($labels);
foreach($labels as $name=>$label):
	if(in_array($name, array('tag_id'))) {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_i';
		echo "\tpublic \${$publicAttribute};\n";
		$publicVariable[] = $publicAttribute;
	}
endforeach; ?>

	// Variable Search
<?php 
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column):
	if($column->isForeignKey == '1') {
		$relationName = setRelationName($name, true);
		if($relationName == 'cat')
			$relationName = 'category';
		$publicAttribute = $relationName.'_search';
		echo "\tpublic \${$publicAttribute};\n";
		$publicVariable[] = $publicAttribute;
	}
endforeach;
foreach($labels as $name=>$label):
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_search';
		echo "\tpublic \${$publicAttribute};\n";
		$publicVariable[] = $publicAttribute;
	}
endforeach; ?>
<?php if($slugCondition) {?>

	/**
	 * Behaviors for this model
	 */
	public function behaviors() 
	{
		return array(
			'sluggable' => array(
				'class'=>'ext.yii-behavior-sluggable.SluggableBehavior',
				'columns' => array('title.message'),
				'unique' => true,
				'update' => true,
			),
		);
	}
<?php }?>

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
<?php if($tableName[0] == '_'):?>

	/**
	 * @return string the primarykey column
	 */
	public function primaryKey()
	{
		return Yii::app()-><?php echo $primaryKeyColumn; ?>;
	}
<?php endif?>

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
<?php 
//print_r($rules);
foreach($rules as $rule): ?>
			<?php echo $rule.",\n"; ?>
<?php endforeach;
if($i18n):
	foreach($columns as $name=>$column):
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)):
			$publicAttribute = $name.'_i';
			$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : 'description';?>
			array('<?php echo $publicAttribute;?>', 'length', 'max'=><?php echo $publicAttributeRelation == 'title' ? '32' : '128';?>),
<?php endif;
	endforeach;
endif;?>
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('<?php echo implode(', ', array_merge(array_keys($columns), $publicVariable)); ?>', 'safe', 'on'=>'search'),
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
	//echo '<pre>';
	//print_r($relations);
	foreach($relations as $name=>$relation): ?>
			<?php
			$relationName = setRelationName($name);
			if($relationName == 'cat')
				$relationName = 'category';
			$relationModel = preg_replace('(Ommu)', '', $relation);
			echo "'$relationName' => $relationModel,\n"; ?>
<?php endforeach;
if($i18n):
	foreach($columns as $name=>$column):
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)):
			$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : 'description';
			echo "\t\t\t'$publicAttributeRelation' => array(self::BELONGS_TO, 'SourceMessage', '{$name}'),\n";
		endif;
	endforeach;
endif;
	foreach($columns as $name=>$column):
		if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
			$relationArray = explode('_', $name);
			$relationName = $relationArray[0];
			if($name == 'member_id')
				echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'Members', '{$name}'),\n";
			else
				echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'Users', '{$name}'),\n";
		} else if($name == 'tag_id') {
			$relationArray = explode('_', $name);
			$relationName = $relationArray[0];
			echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'OmmuTags', '{$name}'),\n";
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
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$publicAttribute = $name.'_i';
		$publicAttributeLabel = ucwords(strtolower($name));
		echo "\t\t\t'$publicAttribute' => Yii::t('attribute', '$publicAttributeLabel'),\n";
	}
endforeach;
foreach($columns as $name=>$column):
	if($column->isForeignKey == '1') {
		$relationName = setRelationName($name, true);
		if($relationName == 'cat')
			$relationName = 'category';
		$publicAttribute = $relationName.'_search';
		$publicAttributeLabel = ucwords(strtolower($relationName));
		echo "\t\t\t'$publicAttribute' => Yii::t('attribute', '$publicAttributeLabel'),\n";
	}
endforeach;
foreach($labels as $name=>$label):
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_search';
		echo "\t\t\t'$publicAttribute' => Yii::t('attribute', '$label'),\n";
	}
endforeach; ?>
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
//echo '<pre>';
//print_r($columns);
$isPrimaryKey = '';
$isVariableSearch = 0;

foreach($columns as $name=>$column) {	
	if($column->isForeignKey == '1' || (in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))))
		$isVariableSearch = 1;
}
if($isVariableSearch == 1) {?>
		// Custom Search
		$criteria->with = array(
<?php foreach($columns as $name=>$column) {	
	if($column->isForeignKey == '1') {
		$relationName = setRelationName($name, true);
		if($relationName == 'cat')
			$relationName = 'category';
		$relationAttribute = 'column_name_relation';
		echo "\t\t\t'$relationName' => array(\n";
		echo "\t\t\t\t'alias'=>'$relationName',\n";
		echo "\t\t\t\t'select'=>'$relationAttribute'\n";
		echo "\t\t\t),\n";
	}
}
if($i18n):
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)):
		$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : 'description';
		echo "\t\t\t'$publicAttributeRelation' => array(\n";
		echo "\t\t\t\t'alias'=>'$publicAttributeRelation',\n";
		echo "\t\t\t\t'select'=>'message'\n";
		echo "\t\t\t),\n";
	endif;
endforeach;
endif;
foreach($columns as $name=>$column) {
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];
		if($name == 'member_id') {
			$relationName = 'member_view';
			echo "\t\t\t'{$relationName}.view' => array(\n";
			echo "\t\t\t\t'alias'=>'{$relationName}_view',\n";
			echo "\t\t\t\t'select'=>'member_name'\n";
			echo "\t\t\t),\n";
		} else {
			echo "\t\t\t'$relationName' => array(\n";
			echo "\t\t\t\t'alias'=>'$relationName',\n";
			echo "\t\t\t\t'select'=>'displayname'\n";
			echo "\t\t\t),\n";
		}
	} else if($name == 'tag_id') {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];
		echo "\t\t\t'$relationName' => array(\n";
		echo "\t\t\t\t'alias'=>'$relationName',\n";
		echo "\t\t\t\t'select'=>'body'\n";
		echo "\t\t\t),\n";
	}
}?>
		);
		
<?php }
/*
foreach($columns as $name=>$column) {	
	if($column->isForeignKey == '1' || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id')))) {
		$arrayName = explode('_', $column->name);
		$cName = 'displayname';
		if($column->isForeignKey == '1')
			$cName = 'column_name_relation';
		$cRelation = $arrayName[0];
		if($cRelation == 'cat')
			$cRelation = 'category';
		if($column->name == 'member_id') {
			$cRelation = 'member_view';
			$cName = 'member_name';	
		}
		$name = $cRelation.'_search';
		echo "\t\t\$criteria->compare('{$cRelation}.{$cName}',strtolower(\$this->$name),true);\n";
	}
}
*/
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column) {
	if($column->name == 'publish') {
		echo "\t\tif(isset(\$_GET['type']) && \$_GET['type'] == 'publish')\n";
		echo "\t\t\t\$criteria->compare('t.$name', 1);\n";
		echo "\t\telseif(isset(\$_GET['type']) && \$_GET['type'] == 'unpublish')\n";
		echo "\t\t\t\$criteria->compare('t.$name', 0);\n";
		echo "\t\telseif(isset(\$_GET['type']) && \$_GET['type'] == 'trash')\n";
		echo "\t\t\t\$criteria->compare('t.$name', 2);\n";
		echo "\t\telse {\n";
		echo "\t\t\t\$criteria->addInCondition('t.$name', array(0,1));\n";
		echo "\t\t\t\$criteria->compare('t.$name', \$this->$name);\n";
		echo "\t\t}\n";

	} else if($column->isForeignKey == '1' || (in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];
		if($relationName == 'cat')
			$relationName = 'category';
		echo "\t\t\$criteria->compare('t.$name', isset(\$_GET['$relationName']) ? \$_GET['$relationName'] : \$this->$name);\n";

	} else if(in_array($column->dbType, array('timestamp','datetime'))) {
		echo "\t\tif(\$this->$name != null && !in_array(\$this->$name, array('0000-00-00 00:00:00', '1970-01-01 00:00:00')))\n";
		echo "\t\t\t\$criteria->compare('date(t.$name)', date('Y-m-d', strtotime(\$this->$name)));\n";

	} else if(in_array($column->dbType, array('date'))) {
		echo "\t\tif(\$this->$name != null && !in_array(\$this->$name, array('0000-00-00', '1970-01-01')))\n";
		echo "\t\t\t\$criteria->compare('date(t.$name)', date('Y-m-d', strtotime(\$this->$name)));\n";

	} else if(in_array($column->dbType, array('int','smallint')) || ($column->type==='string' && $column->isPrimaryKey == '1'))
		echo "\t\t\$criteria->compare('t.$name', \$this->$name);\n";

	else if($column->type==='string')
		echo "\t\t\$criteria->compare('t.$name', strtolower(\$this->$name), true);\n";
		
	else
		echo "\t\t\$criteria->compare('t.$name', \$this->$name);\n";

	if($column->isPrimaryKey) {
		$isPrimaryKey = $name;
	}
}
if($isVariableSearch == 1)
	echo "\n";
foreach($columns as $name=>$column) {	
	if($column->isForeignKey == '1') {
		$relationName = setRelationName($name, true);
		if($relationName == 'cat')
			$relationName = 'category';
		$relationAttribute = 'column_name_relation';
		$publicAttribute = $relationName.'_search';
		echo "\t\t\$criteria->compare('{$relationName}.{$relationAttribute}', strtolower(\$this->$publicAttribute), true);\n";
	}
}
if($i18n):
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)):
		$publicAttribute = $name.'_i';
		$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : 'description';
		echo "\t\t\$criteria->compare('{$publicAttributeRelation}.message', strtolower(\$this->$publicAttribute), true);\n";
	endif;
endforeach;
endif;
foreach($columns as $name=>$column) {
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_search';
		$relationAttribute = 'displayname';

		if($column->name == 'member_id') {
			$relationName = 'member_view';
			$relationAttribute = 'member_name';
		}
		echo "\t\t\$criteria->compare('{$relationName}.{$relationAttribute}', strtolower(\$this->$publicAttribute), true);\n";
	} else if($name == 'tag_id') {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_search';
		$relationAttribute = 'body';

		echo "\t\t\$$publicAttribute = Utility::getUrlTitle(strtolower(trim(\$this->$publicAttribute)));\n";
		echo "\t\t\$criteria->compare('$relationName.$relationAttribute', \$$publicAttribute, true);\n";
	}
}

	if($tableName[0] == '_' && !$isPrimaryKey)
		$isPrimaryKey = $primaryKeyColumn;

	echo "\n\t\tif(!isset(\$_GET['{$modelClass}_sort']))\n";
	echo "\t\t\t\$criteria->order = 't.$isPrimaryKey DESC';\n";
?>

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>Yii::app()->params['grid-view'] ? Yii::app()->params['grid-view']['pageSize'] : 20,
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
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column)
{
	if(!$column->isPrimaryKey && $column->dbType != 'tinyint(1)') {
		if($column->isForeignKey == '1' || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
			$arrayName = explode('_', $column->name);
			$columnName = 'displayname';
			if($column->isForeignKey == '1')
				$columnName = 'column_name_relation';
			if($column->name == 'tag_id')
				$columnName = 'body';
			$relationName = $arrayName[0];
			if($relationName == 'cat')
				$relationName = 'category';
			$publicAttribute = $relationName.'_search';
			if($column->name == 'member_id') {
				$relationName = 'member_view';
				$columnName = 'member_name';	
			}
			echo "\t\t\tif(!isset(\$_GET['$relationName'])) {\n";
			echo "\t\t\t\t\$this->templateColumns['$publicAttribute'] = array(\n";
			echo "\t\t\t\t\t'name' => '$publicAttribute',\n";
if($column->name == 'tag_id') {
			echo "\t\t\t\t\t'value' => 'str_replace(\'-\', \' \', \$data->{$relationName}->{$columnName})',\n";
} else {
			echo "\t\t\t\t\t'value' => '\$data->{$relationName}->{$columnName} ? \$data->{$relationName}->{$columnName} : \'-\'',\n";
}
			echo "\t\t\t\t);\n";
			echo "\t\t\t}\n";
			
		} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
			echo "\t\t\t\$this->templateColumns['$name'] = array(\n";
			echo "\t\t\t\t'name' => '$name',\n";
			if(in_array($column->dbType, array('timestamp','datetime')))
				echo "\t\t\t\t'value' => '!in_array(\$data->$name, array(\'0000-00-00 00:00:00\', \'1970-01-01 00:00:00\')) ? Utility::dateFormat(\$data->$name) : \'-\'',\n";
			else
				echo "\t\t\t\t'value' => '!in_array(\$data->$name, array(\'0000-00-00\', \'1970-01-01\')) ? Utility::dateFormat(\$data->$name) : \'-\'',\n";
			echo "\t\t\t\t'htmlOptions' => array(\n";
			echo "\t\t\t\t\t'class' => 'center',\n";
			echo "\t\t\t\t),\n";
			echo "\t\t\t\t'filter' => Yii::app()->controller->widget('application.libraries.core.components.system.CJuiDatePicker', array(\n";
			echo "\t\t\t\t\t'model'=>\$this,\n";
			echo "\t\t\t\t\t'attribute'=>'$name',\n";
			echo "\t\t\t\t\t'language' => 'en',\n";
			echo "\t\t\t\t\t'i18nScriptFile' => 'jquery-ui-i18n.min.js',\n";
			echo "\t\t\t\t\t//'mode'=>'datetime',\n";
			echo "\t\t\t\t\t'htmlOptions' => array(\n";
			echo "\t\t\t\t\t\t'id' => '$name";echo "_filter',\n";
			echo "\t\t\t\t\t),\n";
			echo "\t\t\t\t\t'options'=>array(\n";
			echo "\t\t\t\t\t\t'showOn' => 'focus',\n";
			echo "\t\t\t\t\t\t'dateFormat' => 'dd-mm-yy',\n";
			echo "\t\t\t\t\t\t'showOtherMonths' => true,\n";
			echo "\t\t\t\t\t\t'selectOtherMonths' => true,\n";
			echo "\t\t\t\t\t\t'changeMonth' => true,\n";
			echo "\t\t\t\t\t\t'changeYear' => true,\n";
			echo "\t\t\t\t\t\t'showButtonPanel' => true,\n";
			echo "\t\t\t\t\t),\n";
			echo "\t\t\t\t), true),\n";
			echo "\t\t\t);\n";
			
		} else {
			$translateCondition = 0;
			$commentArray = explode(',', $column->comment);
			$publicAttribute = $name;
			if(in_array('trigger[delete]', $commentArray)) {
				$publicAttribute = $name.'_i';
				$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : 'description';
				$translateCondition = 1;
			}
			echo "\t\t\t\$this->templateColumns['$publicAttribute'] = array(\n";
			echo "\t\t\t\t'name' => '$publicAttribute',\n";
if($translateCondition) {
			echo "\t\t\t\t'value' => '\$data->{$publicAttributeRelation}->message',\n";
} else {
			echo "\t\t\t\t'value' => '\$data->$name',\n";
}
//if(in_array($column->dbType, array('text'))) {
//			echo "\t\t\t\t'type' => 'raw',\n";
//}
			echo "\t\t\t);\n";
		}
	}
}
foreach($columns as $name=>$column)
{
	if(!$column->isPrimaryKey && $column->dbType == 'tinyint(1)') {
		if(in_array($name, array('publish')))
			echo "\t\t\tif(!isset(\$_GET['type'])) {\n";
		echo "\t\t\t\$this->templateColumns['$name'] = array(\n";
		echo "\t\t\t\t'name' => '$name',\n";
		echo "\t\t\t\t'value' => 'Utility::getPublish(Yii::app()->controller->createUrl(\'$name\',array(\'id\'=>\$data->$isPrimaryKey)), \$data->$name)',\n";
		echo "\t\t\t\t'htmlOptions' => array(\n";
		echo "\t\t\t\t\t'class' => 'center',\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'filter'=>array(\n";
		echo "\t\t\t\t\t1=>Yii::t('phrase', 'Yes'),\n";
		echo "\t\t\t\t\t0=>Yii::t('phrase', 'No'),\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'type' => 'raw',\n";
		echo "\t\t\t);\n";
		if(in_array($name, array('publish')))
			echo "\t\t\t}\n";
	}
}
?>
		}
		parent::afterConstruct();
	}

	/**
	 * User get information
	 */
	public static function getInfo($id, $column=null)
	{
		if($column != null) {
			$model = self::model()->findByPk($id,array(
				'select' => $column
			));
			return $model->$column;
			
		} else {
			$model = self::model()->findByPk($id);
			return $model;
		}
	}
<?php 
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column):
	if($column->isPrimaryKey && ((preg_match('/tinyint/', $column->dbType) && $column->size == '3') || (preg_match('/smallint/', $column->dbType) && in_array($column->size, array('3','5'))))):
?>

	/**
	 * get<?php echo ucfirst(setRelationName($modelClass))."\n";?>
	 * 0 = unpublish
	 * 1 = publish
	 */
	public static function get<?php echo ucfirst(setRelationName($modelClass));?>(<?php echo $publishCondition ? '$publish=null, $type=null' : '';?>) 
	{
		$criteria=new CDbCriteria;
<?php if($publishCondition):?>
		if($publish != null)
			$criteria->compare('t.publish', $publish);

<?php endif;?>
		$model = self::model()->findAll($criteria);

		if($type == null) {
			$items = array();
			if($model != null) {
				foreach($model as $key => $val) {
					$items[$val-><?php echo $column->name;?>] = $val-><?php echo guessNameColumn($columns);?>;
				}
				return $items;
			} else
				return false;
		} else
			return $model;
	}
<?php endif;
endforeach;?>
<?php if($i18n) {?>

	/**
	 * This is invoked when a record is populated with data from a find() call.
	 */
	protected function afterFind()
	{
<?php
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)):
		$publicAttribute = $name.'_i';
		$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : 'description';?>
		$this-><?php echo $publicAttribute;?> = $this-><?php echo $publicAttributeRelation;?>->message;
<?php endif;
endforeach; ?>
		
		parent::afterFind();
	}
<?php }?>

	/**
	 * before validate attributes
	 */
	protected function beforeValidate() 
	{
		if(parent::beforeValidate()) {
<?php
$creationCondition = 0;
foreach($columns as $name=>$column)
{
	if(in_array($name, array('creation_id','modified_id','updated_id')) && $column->comment != 'trigger') {
		if($name == 'creation_id') {
			$creationCondition = 1;
			echo "\t\t\tif(\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->{$name} = !Yii::app()->user->isGuest ? Yii::app()->user->id : 0;\n";
		} else {
			if($creationCondition)
				echo "\t\t\telse\n";
			else
				echo "\t\t\tif(!\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->{$name} = !Yii::app()->user->isGuest ? Yii::app()->user->id : 0;\n";
		}
	}
}
?>
			// Create action
		}
		return true;
	}

	/**
	 * after validate attributes
	 */
	protected function afterValidate()
	{
		parent::afterValidate();
		// Create action
		
		return true;
	}
	
	/**
	 * before save attributes
	 */
	protected function beforeSave() 
	{
<?php if($i18n) {?>
		$module = strtolower(Yii::app()->controller->module->id);
		$controller = strtolower(Yii::app()->controller->id);
		$action = strtolower(Yii::app()->controller->action->id);

		$location = $module.' '.$controller;
		
<?php }?>
		if(parent::beforeSave()) {
<?php
foreach($columns as $name=>$column)
{
	if(in_array($column->dbType, array('date')) && $column->comment != 'trigger') {
		echo "\t\t\t//\$this->$name = date('Y-m-d', strtotime(\$this->$name));\n";
	} else if($column->name == 'tag_id') {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_i';?>
			if($this->isNewRecord) {
				$<?php echo $publicAttribute;?> = Utility::getUrlTitle(strtolower(trim($this-><?php echo $publicAttribute;?>)));
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
<?php } else {
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $name.'_i';
			$publicAttributeLocation = preg_match('/(name|title)/', $name) ? '_title' : '_desc';?>
			if($this->isNewRecord || (!$this->isNewRecord && !$this-><?php echo $name;?>)) {
				$<?php echo $name;?>=new SourceMessage;
				$<?php echo $name;?>->message = $this-><?php echo $publicAttribute;?>;
				$<?php echo $name;?>->location = $location.'<?php echo $publicAttributeLocation;?>';
				if($<?php echo $name;?>->save())
					$this-><?php echo $name;?> = $name->id;
<?php if($i18n && preg_match('/(name|title)/', $name)) {?>

				$this->slug = Utility::getUrlTitle($this-><?php echo $publicAttribute;?>);
<?php }?>
				
			} else {
				$<?php echo $name;?> = SourceMessage::model()->findByPk($this-><?php echo $name;?>);
				$<?php echo $name;?>->message = $this-><?php echo $publicAttribute;?>;
				$<?php echo $name;?>->save();
			}

<?php	}
	}
} ?>
			// Create action
		}
		return true;
	}
	
	/**
	 * After save attributes
	 */
	protected function afterSave() 
	{
		parent::afterSave();
		// Create action
	}

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

	/**
	 * After delete attributes
	 */
	protected function afterDelete() 
	{
		parent::afterDelete();
		// Create action
	}

}