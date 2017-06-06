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
function setRelationName($names) {
	$char=range("A","Z");
	foreach($char as $val) {
		if(strpos($names, $val) !== false) {
			$names = str_replace($val, '_'.strtolower($val), $names);	
		}
	}
	return $names;
}

?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $modelClass."\n"; ?>
 * version: 0.0.1
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @copyright Copyright (c) <?php echo date('Y'); ?> CarCentOS (Career Center OS)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @link http://carcentos.careercenter.id
 * @contact (+62)856-299-4114
 *
 * This is the template for generating the model class of a specified table.
 * - $this: the ModelCode object
 * - $tableName: the table name for this class (prefix is already removed if necessary)
 * - $modelClass: the model class name
 * - $columns: list of table columns (name=>CDbColumnSchema)
 * - $labels: list of attribute labels (name=>label)
 * - $rules: list of validation rules
 * - $relations: list of relations (name=>relation declaration)
 *
 * --------------------------------------------------------------------------------------
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
<?php foreach($relations as $name=>$relation): ?>
 * @property <?php
	if (preg_match("~^array\(self::([^,]+), '([^']+)', '([^']+)'\)$~", $relation, $matches))
    {
        $relationType = $matches[1];
        $relationModel = $matches[2];

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
    ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?php echo $modelClass; ?> extends <?php echo $this->baseClass."\n"; ?> {
	public $defaultColumns = array();

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return <?php echo $modelClass; ?> the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
<?php if($connectionId!='db'):?>

	/**
	 * @return CDbConnection database connection
	 */
	public function getDbConnection() {
		return Yii::app()-><?php echo $connectionId ?>;
	}
<?php endif?>

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return '<?php echo $tableName; ?>';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
<?php foreach($rules as $rule): ?>
			<?php echo $rule.",\n"; ?>
<?php endforeach; ?>
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('<?php echo implode(', ', array_keys($columns)); ?>', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
<?php foreach($relations as $name=>$relation): 
			$name = setRelationName($name);
?>
			<?php echo "'$name' => $relation,\n"; ?>
<?php endforeach; ?>
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
<?php foreach($labels as $name=>$label): ?>
			<?php echo "'$name' => Yii::t('label', '$label'),\n"; ?>
<?php endforeach; ?>
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

<?php
foreach($columns as $name=>$column)
{
	if(in_array($column->name, array('publish','published'))) {
		echo "\t\tif(isset(\$_GET['type']) && \$_GET['type'] == 'publish')\n";
		echo "\t\t\t\$criteria->compare('t.$name',1);\n";
		echo "\t\telseif(isset(\$_GET['type']) && \$_GET['type'] == 'unpublish')\n";
		echo "\t\t\t\$criteria->compare('t.$name',0);\n";
		echo "\t\telseif(isset(\$_GET['type']) && \$_GET['type'] == 'trash')\n";
		echo "\t\t\t\$criteria->compare('t.$name',2);\n";
		echo "\t\telse {\n";
		echo "\t\t\t\$criteria->addInCondition('t.$name',array(0,1));\n";
		echo "\t\t\t\$criteria->compare('t.$name',\$this->$name);\n";
		echo "\t\t}\n";

	} else if($column->isForeignKey == '1' || (in_array($column->name, array('swt_users_id','creation_id','modified_id')))) {
		$arrayName = explode('_', $column->name);
		if(in_array($column->name, array('swt_users_id'))) {
			echo "\t\tif(isset(\$_GET['$arrayName[1]']))\n";
			echo "\t\t\t\$criteria->compare('t.$name',\$_GET['$arrayName[1]']);\n";	
		} else {
			echo "\t\tif(isset(\$_GET['$arrayName[0]']))\n";
			echo "\t\t\t\$criteria->compare('t.$name',\$_GET['$arrayName[0]']);\n";		
		}
		echo "\t\telse\n";
		echo "\t\t\t\$criteria->compare('t.$name',\$this->$name);\n";

	} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
		echo "\t\tif(\$this->$name != null && !in_array(\$this->$name, array('0000-00-00 00:00:00', '0000-00-00')))\n";
		echo "\t\t\t\$criteria->compare('date(t.$name)',date('Y-m-d', strtotime(\$this->$name)));\n";

	} else if(in_array($column->dbType, array('int','smallint'))) {
		echo "\t\t\$criteria->compare('t.$name',\$this->$name);\n";
		
	} else if($column->type==='string') {
		echo "\t\t\$criteria->compare('$name',strtolower(\$this->$name),true);\n";
		
	} else {
		echo "\t\t\$criteria->compare('$name',\$this->$name);\n";
	}
	if($column->isPrimaryKey) {
		$isPrimaryKey = $name;
	}
}
	echo "\n\t\tif(!isset(\$_GET['{$modelClass}_sort']))\n";
	echo "\t\t\t\$criteria->order = 't.$isPrimaryKey DESC';\n";
?>

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Get column for CGrid View
	 */
	public function getGridColumn($columns=null) {
		if($columns !== null) {
			foreach($columns as $val) {
				/*
				if(trim($val) == 'enabled') {
					$this->defaultColumns[] = array(
						'name'  => 'enabled',
						'value' => '$data->enabled == 1? "Ya": "Tidak"',
					);
				}
				*/
				$this->defaultColumns[] = $val;
			}
		}else {
<?php
foreach($columns as $name=>$column)
{
	if($column->isPrimaryKey)
		echo "\t\t\t"."//"."\$this->defaultColumns[] = '$name';\n";
	else
		echo "\t\t\t\$this->defaultColumns[] = '$name';\n";
}
?>
		}

		return $this->defaultColumns;
	}

	/**
	 * Set default columns to display
	 */
	protected function afterConstruct() {
		if(count($this->defaultColumns) == 0) {
			$this->defaultColumns[] = array(
				'header' => 'No',
				'value' => '$this->grid->dataProvider->pagination->currentPage*$this->grid->dataProvider->pagination->pageSize + $row+1'
			);
<?php
foreach($columns as $name=>$column)
{
	if(!$column->isPrimaryKey) {
		if($column->dbType == 'tinyint(1)') {
			echo "\t\t\tif(!isset(\$_GET['type'])) {\n";
			echo "\t\t\t\t\$this->defaultColumns[] = array(\n";
			echo "\t\t\t\t\t'name' => '$name',\n";
			if(in_array($column->name, array('publish','published')))
				echo "\t\t\t\t\t'value' => 'Utility::getPublish(Yii::app()->controller->createUrl(\"publish\",array(\"id\"=>\$data->$isPrimaryKey)), \$data->$name, 1)',\n";
			else
				echo "\t\t\t\t\t'value' => '\$data->$name == 1 ? Chtml::image(Yii::app()->theme->baseUrl.\'/images/icons/publish.png\') : Chtml::image(Yii::app()->theme->baseUrl.\'/images/icons/unpublish.png\')',\n";
			echo "\t\t\t\t\t'htmlOptions' => array(\n";
			echo "\t\t\t\t\t\t'class' => 'center',\n";
			echo "\t\t\t\t\t),\n";
			echo "\t\t\t\t\t'filter'=>array(\n";
			echo "\t\t\t\t\t\t1=>Yii::t('phrase', 'Yes'),\n";
			echo "\t\t\t\t\t\t0=>Yii::t('phrase', 'No'),\n";
			echo "\t\t\t\t\t),\n";
			echo "\t\t\t\t\t'type' => 'raw',\n";
			echo "\t\t\t\t);\n";
			echo "\t\t\t}\n";
			
		} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
			echo "\t\t\t\$this->defaultColumns[] = array(\n";
			echo "\t\t\t\t'name' => '$name',\n";
			echo "\t\t\t\t'value' => 'Utility::dateFormat(\$data->$name)',\n";
			echo "\t\t\t\t'htmlOptions' => array(\n";
			echo "\t\t\t\t\t'class' => 'center',\n";
			echo "\t\t\t\t),\n";
			echo "\t\t\t\t'filter' => Yii::app()->controller->widget('zii.widgets.jui.CJuiDatePicker', array(\n";
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
			
		} else
			echo "\t\t\t\$this->defaultColumns[] = '$name';\n";		
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

	/**
	 * before validate attributes
	 */
	/*
	protected function beforeValidate() {
		if(parent::beforeValidate()) {
			// Create action
		}
		return true;
	}
	*/

	/**
	 * after validate attributes
	 */
	/*
	protected function afterValidate()
	{
		parent::afterValidate();
			// Create action
		return true;
	}
	*/
	
	/**
	 * before save attributes
	 */
	/*
	protected function beforeSave() {
		if(parent::beforeSave()) {
<?php
$isDateTrigger = 0;
foreach($columns as $name=>$column)
{
	if(in_array($column->dbType, array('datetime','date'))) {
		echo "\t\t\t//\$this->$name = date('Y-m-d', strtotime(\$this->$name));\n";
	}
}
?>
		}
		return true;	
	}
	*/
	
	/**
	 * After save attributes
	 */
	/*
	protected function afterSave() {
		parent::afterSave();
		// Create action
	}
	*/

	/**
	 * Before delete attributes
	 */
	/*
	protected function beforeDelete() {
		if(parent::beforeDelete()) {
			// Create action
		}
		return true;
	}
	*/

	/**
	 * After delete attributes
	 */
	/*
	protected function afterDelete() {
		parent::afterDelete();
		// Create action
	}
	*/
	


}