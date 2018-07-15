<?php
Yii::import('application.libraries.gii.Inflector');

class ModelCode extends CCodeModel
{
    const TYPE_TABLE = 1;
	const TYPE_VIEW  = 2;
	
	public $connectionId='db';
	public $tablePrefix;
	public $tableName;
	public $modelClass;
	public $modelPath='application.models';
	public $baseClass='OActiveRecord';
	public $buildRelations=true;
	public $commentsAsLabels=false;
	public $uploadPath=array(
		'directory' => 'public/main',
	);
	public $useEvent=false;
	public $useGetFunction=false;
	public $useModified=false;
	public $link='https://github.com/ommu';

	/**
	 * @var array list of candidate relation code. The array are indexed by AR class names and relation names.
	 * Each element represents the code of the one relation in one AR class.
	 */
	protected $relations;
	protected $tableViews;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('tablePrefix, baseClass, tableName, modelClass, modelPath, connectionId, link', 'filter', 'filter'=>'trim'),
			array('connectionId, tableName, modelClass, modelPath, baseClass, uploadPath, useEvent, useGetFunction, useModified, link', 'required'),
			array('tablePrefix, tableName, modelPath', 'match', 'pattern'=>'/^(\w+[\w\.]*|\*?|\w+\.\*)$/', 'message'=>'{attribute} should only contain word characters, dots, and an optional ending asterisk.'),
			array('connectionId', 'validateConnectionId', 'skipOnError'=>true),
			array('tableName', 'validateTableName', 'skipOnError'=>true),
			array('tablePrefix, modelClass', 'match', 'pattern'=>'/^[a-zA-Z_]\w*$/', 'message'=>'{attribute} should only contain word characters.'),
		    array('baseClass', 'match', 'pattern'=>'/^[a-zA-Z_\\\\][\w\\\\]*$/', 'message'=>'{attribute} should only contain word characters and backslashes.'),
			array('modelPath', 'validateModelPath', 'skipOnError'=>true),
			array('baseClass, modelClass', 'validateReservedWord', 'skipOnError'=>true),
			array('baseClass', 'validateBaseClass', 'skipOnError'=>true),
			array('connectionId, tablePrefix, modelPath, baseClass, buildRelations, commentsAsLabels, uploadPath, link', 'sticky'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'tablePrefix'=>'Table Prefix',
			'tableName'=>'Table Name',
			'modelPath'=>'Model Path',
			'modelClass'=>'Model Class',
			'baseClass'=>'Base Class',
			'buildRelations'=>'Build Relations',
			'commentsAsLabels'=>'Use Column Comments as Attribute Labels',
			'connectionId'=>'Database Connection',
			'uploadPath[directory]'=>'Upload Path (path location)',
			'uploadPath[subfolder]'=>'Use Subfolder with PrimaryKey',
			'useEvent'=>'Generate Events',
			'useGetFunction'=>'Generate GetFunction',
			'useModified'=>'Modified',
			'link'=>'Link Repository',
		));
	}

	public function requiredTemplates()
	{
		return array(
			'model.php',
		);
	}

	public function init()
	{
		if(Yii::app()->{$this->connectionId}===null)
			throw new CHttpException(500,'A valid database connection is required to run this generator.');
		$this->tablePrefix=Yii::app()->{$this->connectionId}->tablePrefix;
		parent::init();
	}

	public function prepare()
	{
		if(($pos=strrpos($this->tableName,'.'))!==false)
		{
			$schema=substr($this->tableName,0,$pos);
			$tableName=substr($this->tableName,$pos+1);
		}
		else
		{
			$schema='';
			$tableName=$this->tableName;
		}
		if($tableName[strlen($tableName)-1]==='*')
		{
			$tables=Yii::app()->{$this->connectionId}->schema->getTables($schema);
			if($this->tablePrefix!='')
			{
				foreach($tables as $i=>$table)
				{
					if(strpos($table->name,$this->tablePrefix)!==0)
						unset($tables[$i]);
				}
			}
		}
		else
			$tables=array($this->getTableSchema($this->tableName));

		$this->files=array();
		$templatePath=$this->templatePath;
		$this->relations=$this->generateRelations();
		$this->tableViews=$this->getAllTableViews();

		foreach($tables as $table)
		{
			$tableView = '';
			$tableView1 = $this->tableView($table->name);
			$tableView2 = $this->tableView($table->name, true);
			if(in_array($tableView1, $this->tableViews))
				$tableView = $tableView1;
			else {
				if(in_array($tableView2, $this->tableViews))
					$tableView = $tableView2;
			}

			$tableName=$this->removePrefix($table->name);
			$className=$this->generateClassName($table->name);
			$params=array(
				'tableName'=>$schema==='' ? $tableName : $schema.'.'.$tableName,
				'modelClass'=>$className,
				'columns'=>$table->columns,
				'labels'=>$this->generateLabels($table),
				'rules'=>$this->generateRules($table),
				'relations'=>isset($this->relations[$className]) ? $this->relations[$className] : array(),
				'connectionId'=>$this->connectionId,
				'table'=>$table,
				'tableViews'=>$this->tableViews,
			);
			$this->files[]=new CCodeFile(
				Yii::getPathOfAlias($this->modelPath).'/'.$className.'.php',
				$this->render($templatePath.'/model.php', $params)
			);
			if($tableView) {
				$table = $this->getTableSchema($tableView);
				$tableName=$this->removePrefix($tableView);
				$className=$this->generateClassName($tableView);
				$params=array(
					'tableName'=>$schema==='' ? $tableName : $schema.'.'.$tableName,
					'modelClass'=>$className,
					'columns'=>$table->columns,
					'labels'=>$this->generateLabels($table),
					'rules'=>$this->generateRules($table),
					'relations'=>isset($this->relations[$className]) ? $this->relations[$className] : array(),
					'connectionId'=>$this->connectionId,
					'table'=>$table,
					'tableViews'=>$this->tableViews,
				);
				$this->files[]=new CCodeFile(
					Yii::getPathOfAlias($this->modelPath).'/'.$className.'.php',
					$this->render($templatePath.'/model.php', $params)
				);
			}
		}
	}

	public function validateTableName($attribute,$params)
	{
		if($this->hasErrors())
			return;

		$invalidTables=array();
		$invalidColumns=array();

		if($this->tableName[strlen($this->tableName)-1]==='*')
		{
			if(($pos=strrpos($this->tableName,'.'))!==false)
				$schema=substr($this->tableName,0,$pos);
			else
				$schema='';

			$this->modelClass='';
			$tables=Yii::app()->{$this->connectionId}->schema->getTables($schema);
			foreach($tables as $table)
			{
				if($this->tablePrefix=='' || strpos($table->name,$this->tablePrefix)===0)
				{
					if(in_array(strtolower($table->name),self::$keywords))
						$invalidTables[]=$table->name;
					if(($invalidColumn=$this->checkColumns($table))!==null)
						$invalidColumns[]=$invalidColumn;
				}
			}
		}
		else
		{
			if(($table=$this->getTableSchema($this->tableName))===null)
				$this->addError('tableName',"Table '{$this->tableName}' does not exist.");
			if($this->modelClass==='')
				$this->addError('modelClass','Model Class cannot be blank.');

			if(!$this->hasErrors($attribute) && ($invalidColumn=$this->checkColumns($table))!==null)
					$invalidColumns[]=$invalidColumn;
		}

		if($invalidTables!=array())
			$this->addError('tableName', 'Model class cannot take a reserved PHP keyword! Table name: '.implode(', ', $invalidTables).".");
		if($invalidColumns!=array())
			$this->addError('tableName', 'Column names that does not follow PHP variable naming convention: '.implode(', ', $invalidColumns).".");
	}

	/*
	 * Check that all database field names conform to PHP variable naming rules
	 * For example mysql allows field name like "2011aa", but PHP does not allow variable like "$model->2011aa"
	 * @param CDbTableSchema $table the table schema object
	 * @return string the invalid table column name. Null if no error.
	 */
	public function checkColumns($table)
	{
		foreach($table->columns as $column)
		{
			if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/',$column->name))
				return $table->name.'.'.$column->name;
		}
	}

	public function validateModelPath($attribute,$params)
	{
		if(Yii::getPathOfAlias($this->modelPath)===false)
			$this->addError('modelPath','Model Path must be a valid path alias.');
	}

	public function validateBaseClass($attribute,$params)
	{
		$class=@Yii::import($this->baseClass,true);
		if(!is_string($class) || !$this->classExists($class))
			$this->addError('baseClass', "Class '{$this->baseClass}' does not exist or has syntax error.");
		elseif($class!=='CActiveRecord' && !is_subclass_of($class,'CActiveRecord'))
			$this->addError('baseClass', "'{$this->baseClass}' must extend from CActiveRecord.");
	}

	public function getTableSchema($tableName)
	{
		$connection=Yii::app()->{$this->connectionId};
		return $connection->getSchema()->getTable($tableName, $connection->schemaCachingDuration!==0);
	}

	public function generateLabels($table)
	{
		$labels=array();
		foreach($table->columns as $column)
		{
			//if($this->commentsAsLabels && $column->comment)
			//	$labels[$column->name]=$column->comment;
			//else
			//{
				$label=ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $column->name)))));
				$label=preg_replace('/\s+/',' ',$label);
				if(strcasecmp(substr($label,-3),' id')===0)
					$label=substr($label,0,-3);
				if($label==='Id')
					$label='ID';
				$label=str_replace("'","\\'",$label);
				$labels[$column->name]=$label;
			//}
		}
		return $labels;
	}

	public function generateRules($table)
	{
		$rules=array();
		$required=array();
		$integers=array();
		$numerical=array();
		$length=array();
		$safe=array();
		$trigger=array();
		$serialize=array();
		foreach($table->columns as $column)
		{
			$commentArray = explode(',', $column->comment);
			if($column->autoIncrement)
				continue;

			$r=!$column->allowNull && $column->defaultValue===null;
			if($r && $column->comment != 'trigger' && !in_array($column->name, array('creation_id','modified_id','slug')))
				$required[]=$column->name;
			if($column->type==='integer')
				$integers[]=$column->name;
			elseif($column->type==='double')
				$numerical[]=$column->name;
			elseif($column->type==='string') {
				if(preg_match('/(int)/', $column->dbType))
					$integers[]=$column->name;
				if($column->size>0)
					$length[$column->size][]=$column->name;
			} elseif(!$column->isPrimaryKey && !$r)
				$safe[]=$column->name;

			if(in_array('trigger[delete]', $commentArray)) {
				$columnName = $column->name.'_i';
				if(!empty($required))
					$required = array_diff($required, array($column->name));
				$required[] = $columnName;

				$lengthSize = in_array('redactor', $commentArray) ? '~' : (in_array('text', $commentArray) ? '128' : '64');
				if($lengthSize != '~')
					$length[$lengthSize][] = $columnName;
			}
			if($column->name == 'tag_id') {
				$columnName = $this->setRelation($column->name, true).'_i';
				if(!empty($required))
					$required = array_diff($required, array($column->name));
				$required[] = $columnName;
				$safe[] = $column->name;
			}
			if(!in_array($column->name, ['creation_id','modified_id','user_id','updated_id','member_id','tag_id']) && $column->dbType == 'text' && in_array('file', $commentArray)) {
				if(!empty($required))
					$required = array_diff($required, array($column->name));
				$safe[]=$column->name;
			}
			if($column->dbType == 'text' && in_array('serialize', $commentArray)) {
				if(!empty($required))
					$required = array_diff($required, array($column->name));
				$serialize[]=$column->name;
				$safe[]=$column->name;
			}
			if($column->comment == 'trigger')
				$trigger[]=$column->name;
		}
		foreach($table->columns as $column)
		{
			$commentArray = explode(',', $column->comment);
			if($column->autoIncrement)
				continue;

			if(!in_array($column->name, ['creation_id','modified_id','user_id','updated_id','member_id','tag_id']) && $column->dbType == 'text' && in_array('file', $commentArray))
				$safe[]='old_'.$column->name.'_i';
		}
		if($required!==array())
			$rules[]="array('".implode(', ',$required)."', 'required')";
		if($integers!==array())
			$rules[]="array('".implode(', ',$integers)."', 'numerical', 'integerOnly'=>true)";
		if($numerical!==array())
			$rules[]="array('".implode(', ',$numerical)."', 'numerical')";
		if($safe!==array())
			$rules[]="array('".implode(', ',$safe)."', 'safe')";
		ksort($length);
		if($length!==array())
		{
			foreach($length as $len=>$cols)
				$rules[]="array('".implode(', ',$cols)."', 'length', 'max'=>$len)";
		}
		if($serialize!==array())
			$rules[]="// array('".implode(', ',$serialize)."', 'serialize')";
		if($trigger!==array())
			$rules[]="// array('".implode(', ',$trigger)."', 'trigger')";

		return $rules;
	}

	public function getRelations($className)
	{
		return isset($this->relations[$className]) ? $this->relations[$className] : array();
	}

	protected function removePrefix($tableName,$addBrackets=true)
	{
		if($addBrackets && Yii::app()->{$this->connectionId}->tablePrefix=='')
			return $tableName;
		$prefix=$this->tablePrefix!='' ? $this->tablePrefix : Yii::app()->{$this->connectionId}->tablePrefix;
		if($prefix!='')
		{
			if($addBrackets && Yii::app()->{$this->connectionId}->tablePrefix!='')
			{
				$prefix=Yii::app()->{$this->connectionId}->tablePrefix;
				$lb='{{';
				$rb='}}';
			}
			else
				$lb=$rb='';
			if(($pos=strrpos($tableName,'.'))!==false)
			{
				$schema=substr($tableName,0,$pos);
				$name=substr($tableName,$pos+1);
				if(strpos($name,$prefix)===0)
					return $schema.'.'.$lb.substr($name,strlen($prefix)).$rb;
			}
			elseif(strpos($tableName,$prefix)===0)
				return $lb.substr($tableName,strlen($prefix)).$rb;
		}
		return $tableName;
	}

	protected function generateRelations()
	{
		$inflector = new Inflector;
		if(!$this->buildRelations)
			return array();

		$tableViews=$this->getAllTableViews();
		$relations=array();
		
		foreach($this->getAllTables() as $table)
		{
			if($this->tablePrefix!='' && strpos($table->name,$this->tablePrefix)!==0)
				continue;
			$tableName=$table->name;

			if ($this->isRelationTable($table))
			{
				$pks=$table->primaryKey;
				$fks=$table->foreignKeys;

				$table0=$fks[$pks[0]][0];
				$table1=$fks[$pks[1]][0];
				$className0=$this->generateClassName($table0);
				$className1=$this->generateClassName($table1);

				$unprefixedTableName=$this->removePrefix($tableName);

				$relationName=$this->generateRelationName($table0, $table1, true);
				$relations[$className0][$relationName]="array(self::MANY_MANY, '$className1', '$unprefixedTableName($pks[0], $pks[1])')";

				$relationName=$this->generateRelationName($table1, $table0, true);

				$i=1;
				$rawName=$relationName;
				while(isset($relations[$className1][$relationName]))
					$relationName=$rawName.$i++;

				$relations[$className1][$relationName]="array(self::MANY_MANY, '$className0', '$unprefixedTableName($pks[1], $pks[0])')";
			}
			else
			{
				$className=$this->generateClassName($tableName);
				$publishCondition = 0;
				if(array_key_exists('publish', $table->columns))
					$publishCondition = 1;

				$tableView1 = $this->tableView($tableName);
				$tableView2 = $this->tableView($tableName, true);
				if(in_array($tableView1, $tableViews) || in_array($tableView2, $tableViews))
					$relations[$className]['view']="array(self::BELONGS_TO, 'View$className', '$table->primaryKey')";
					
				foreach ($table->foreignKeys as $fkName => $fkEntry)
				{
					// Put table and key name in variables for easier reading
					$refTable=$fkEntry[0]; // Table name that current fk references to
					$refKey=$fkEntry[1];   // Key in that table being referenced
					$refClassName=$this->generateClassName($refTable);

					// Add relation for this table
					$relationName=$this->generateRelationName($tableName, $fkName, false);
					$relations[$className][$relationName]="array(self::BELONGS_TO, '$refClassName', '$fkName')";

					// Add relation for the referenced table
					$relationType=$table->primaryKey === $fkName ? 'HAS_ONE' : 'HAS_MANY';
					$relationName=$this->generateRelationName($refTable, $this->removePrefix($tableName,false), $relationType==='HAS_MANY');
					$i=1;
					$rawName=$relationName;
					while(isset($relations[$refClassName][$relationName]))
						$relationName=$rawName.($i++);
					if($relationType == 'HAS_MANY') {
						if($publishCondition) {
							$relations[$refClassName][$relationName]="array(self::$relationType, '$className', '$fkName', 'on'=>'$relationName.publish=1')";
							$relations[$refClassName][$inflector->singularize($relationName).'All']="array(self::$relationType, '$className', '$fkName')";
						} else
							$relations[$refClassName][$relationName]="array(self::$relationType, '$className', '$fkName')";
					} else
						$relations[$refClassName][$relationName]="array(self::$relationType, '$className', '$fkName')";
				}
			}
		}
		return $relations;
	}

	/**
	 * Checks if the given table is a "many to many" pivot table.
	 * Their PK has 2 fields, and both of those fields are also FK to other separate tables.
	 * @param CDbTableSchema table to inspect
	 * @return boolean true if table matches description of helper table.
	 */
	protected function isRelationTable($table)
	{
		$pk=$table->primaryKey;
		return (count($pk) === 2 // we want 2 columns
			&& isset($table->foreignKeys[$pk[0]]) // pk column 1 is also a foreign key
			&& isset($table->foreignKeys[$pk[1]]) // pk column 2 is also a foriegn key
			&& $table->foreignKeys[$pk[0]][0] !== $table->foreignKeys[$pk[1]][0]); // and the foreign keys point different tables
	}

	protected function generateClassName($tableName)
	{
		if($this->tableName===$tableName || ($pos=strrpos($this->tableName,'.'))!==false && substr($this->tableName,$pos+1)===$tableName)
			return $this->modelClass;

		$tableName=$this->removePrefix($tableName,false);
		if(($pos=strpos($tableName,'.'))!==false) // remove schema part (e.g. remove 'public2.' from 'public2.post')
			$tableName=substr($tableName,$pos+1);
		$className='';
		foreach(explode('_',$tableName) as $name)
		{
			if($name!=='')
				$className.=ucfirst($name);
		}
		if(preg_match('/Core/', $className))
			$className = preg_replace('(Core)', '', $className);
		else
			$className = preg_replace('(Ommu)', '', $className);

		return $tableName[0] == '_' ? join('', array('View', $className)) : $className;
	}

	/**
	 * Generate a name for use as a relation name (inside relations() function in a model).
	 * @param string the name of the table to hold the relation
	 * @param string the foreign key name
	 * @param boolean whether the relation would contain multiple objects
	 * @return string the relation name
	 */
	protected function generateRelationName($tableName, $fkName, $multiple)
	{
		if(strcasecmp(substr($fkName,-2),'id')===0 && strcasecmp($fkName,'id'))
			$relationName=rtrim(substr($fkName, 0, -2),'_');
		else
			$relationName=$fkName;
		$relationName[0]=strtolower($relationName);

		if($multiple)
			$relationName=$this->pluralize($relationName);

		$names=preg_split('/_+/',$relationName,-1,PREG_SPLIT_NO_EMPTY);
		if(empty($names)) return $relationName;  // unlikely
		for($name=$names[0], $i=1;$i<count($names);++$i)
			$name.=ucfirst($names[$i]);

		$rawName=$name;
		$table=Yii::app()->{$this->connectionId}->schema->getTable($tableName);
		$i=0;
		while(isset($table->columns[$name]))
			$name=$rawName.($i++);

		return $this->setRelation($name);
	}

	public function validateConnectionId($attribute, $params)
	{
		if(Yii::app()->hasComponent($this->connectionId)===false || !(Yii::app()->getComponent($this->connectionId) instanceof CDbConnection))
			$this->addError('connectionId','A valid database connection is required to run this generator.');
	}

	public function getUploadPathDirectory()
	{
		return $this->uploadPath['directory'];
	}

	public function getUploadPathSubfolder()
	{
		return $this->uploadPath['subfolder'];
	}

	public function getUseEvent()
	{
		return $this->useEvent;
	}

	public function getUseGetFunction()
	{
		return $this->useGetFunction;
	}

	public function getUseModified()
	{
		return $this->useModified;
	}

	public function getLinkSource()
	{
		return $this->link;
	}

	public function getAllTables()
	{
		$schemaName='';
		if(($pos=strpos($this->tableName,'.'))!==false)
			$schemaName=substr($this->tableName,0,$pos);

		return Yii::app()->{$this->connectionId}->schema->getTables($schemaName);
	}

	public function getAllTableViews()
	{
		$tableViews=array();
		
		foreach($this->getAllTables() as $table) {
			if($table->name[0] == '_')
				$tableViews[] = $table->name;
		}

		return $tableViews;
	}
	
	public function tableType($tableName) 
	{
		if($tableName == '')
			throw new \Exception('Parameter $tableName wajib ada!.');

		$_tblType = null;
		$allTables = $this->getAllTables();
		foreach($allTables as $item) {
			$vars = get_object_vars($item);
			foreach($vars as $key => $val) {
				if($key != 'Table_type' && $val == $tableName) {
					if($vars['Table_type'] == 'VIEW')
						$_tblType = self::TYPE_VIEW;
					break;
				}
			}

			if($_tblType != null)
				break;
		}

		if($_tblType == self::TYPE_VIEW)
			return self::TYPE_VIEW;
		else
			return self::TYPE_TABLE;
	}

	public function tableView($tableName, $type2=false)
	{
		if($tableName[0] == '_')
			return false;

		if($type2 == false) {
			$arrayTable = explode('_', $tableName);
			$arrayTable = array_diff($arrayTable, array('ommu'));
			return '_'.implode('_', $arrayTable);
		} else
			return '_'.join('', array('view', $this->tableView($tableName)));
	}
	
	public function foreignKeys($foreignKeys)
	{
		$column = [];
		if(!empty($foreignKeys)) {
			foreach($foreignKeys as $key=>$val) {
				// Only variables should be passed by reference
				$arrayValue = array_values($val);
				//$column[array_pop($arrVal)] = array_shift($arrVal);
				$column[$key] = array_shift($arrayValue);
			}
		}
	
		return $column;
	}

	public function relationIndex($key) 
	{
		$relation = explode('_', $key);
		$relation = array_diff($relation, array('ommu','core'));

		if(count($relation) != 1)
			return end($relation);
		else {
			if(is_array($relation))
				return implode('', $relation);
			else
				return $relation;
		}
	}

	public function setRelation($names, $column=false) 
	{
		$inflector = new Inflector;
		if($column == false) {
			$names = $inflector->camel2id($names, '_');
			$return = $this->relationIndex($names);

			return $return != 'cat' ? $return : 'category';
	
		} else {
			$key = $names;
			if (!empty($key) && strcasecmp($key, 'id')) {
				if (substr_compare($key, 'id', -2, 2, true) === 0)
					$key = rtrim(substr($key, 0, -2), '_');
				elseif (substr_compare($key, 'id', 0, 2, true) === 0)
					$key = ltrim(substr($key, 2, strlen($key)), '_');
			}
			$key = $this->relationIndex($key);
			if(strtolower($key) == 'cat')
				$key = 'category';
		
			$key = $inflector->singularize($inflector->id2camel($key, '_'));
	
			return lcfirst($key);
		}
	}

	public function tableAttribute($columns)
	{
		$primaryKey = array();
		foreach($columns as $name=>$column):
			if($column->isPrimaryKey || $column->autoIncrement)
				$primaryKey[] = $column->name;
			if(preg_match('/(name|title)/', $column->name))
				return $column->name;
		endforeach;
		$pk = $primaryKey;
	
		if(!empty($primaryKey))
			return $pk[0];
		else
			return 'id';
	}

	public function tableRelationAttribute($tableName, $separator='->')
	{
		$tables=array($this->getTableSchema($tableName));
		$table = $tables[0];

		$foreignKeys = $this->foreignKeys($table->foreignKeys);
		$titleCondition = 0;
		$foreignCondition = 0;

		foreach ($table->columns as $column) {
			$relationColumn = [];
			if(preg_match('/(name|title)/', $column->name)) {
				$commentArray = explode(',', $column->comment);
				if(in_array('trigger[delete]', $commentArray)) {
					$relationColumn[$column->name] = $this->i18nRelation($column->name);
					$relationColumn[] = 'message';
				} else {
					if($column->name == 'username')
						$relationColumn[$column->name] = 'displayname';
					else
						$relationColumn[$column->name] = $column->name;

				}
				$titleCondition = 1;
			}
			if(!empty($relationColumn))
				return implode($separator, $relationColumn);
		}
		if(!$titleCondition) {
			foreach ($table->columns as $column) {
				$relationColumn = [];
				if($column->name == 'tag_id') {
					$relationColumn[$column->name] = $this->setRelation($column->name, true);
					$relationColumn[] = 'body';
				}
				if(!empty($relationColumn))
					return implode($separator, $relationColumn);
			}
		}
		if(!$titleCondition) {
			foreach ($table->columns as $column) {
				$relationColumn = [];
				if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
					$relationTableName = trim($foreignKeys[$column->name]);
					if(!$foreignCondition) {
						$relationColumn[$column->name] = $this->setRelation($column->name, true);
						$relationColumn[] = $this->tableRelationAttribute($relationTableName, $separator);
						$foreignCondition = 1;
					}
				}
				if(!empty($relationColumn))
					return implode($separator, $relationColumn);
			}
		}
		$pk = $table->primaryKey;

		return $pk;
	}

	public function table2ndRelation($attr='', $separator='.')
	{
		$relations = [];
		if($attr != '') {
			$relations = explode($separator, $attr);
			array_pop($relations);
		}

		return implode($separator, $relations);
	}

	public function table2ndAttribute($attr='', $separator='.')
	{
		if($attr != '') {
			$relations = explode($separator, $attr);
			return array_pop($relations);
		}

		return $attr;
	}

	public function i18nRelation($column, $relation=true)
	{
		return preg_match('/(name|title)/', $column) ? 'title' : (preg_match('/(desc|description)/', $column) ? ($column != 'description' ? 'description' :  ($relation == true ? $column.'Rltn' : $column)) : ($relation == true ? $column.'Rltn' : $column));
	}
}
