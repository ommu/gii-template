<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator mdm\gii\generators\migration\Generator */
/* @var $migrationName string migration name */

/**
 * Variable
 */
use ommu\gii\model\Generator;
use yii\helpers\Inflector;

$gridPublicVariables = [];

if($tableType == Generator::TYPE_VIEW)
	$primaryKey = $viewPrimaryKey;
else
	$primaryKey = $generator->getPrimaryKey($tableSchema);

/**
 * foreignKeys Column
 */
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
$otherModels = [];
foreach ($foreignKeys as $key => $val) {
	$module = $tableSchema->columns[$key]->comment;
	if($module)
		$otherModels[] = $generator->getUseModel($module, $generator->generateClassName($val));
}

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?= $migrationName."\n" ?>
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

use Yii;
use yii\db\Schema;

class <?= $migrationName ?> extends \yii\db\Migration
{
	public function up()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}
		$tableName = Yii::$app->db->tablePrefix . '<?= $gridTableName ?>';
		if (!Yii::$app->db->getTableSchema($tableName, true)) {
			$this->createTable($tableName, [
<?php 
foreach ($relations as $name => $relation) {
	if(!$relation[2])
		continue;
	$relationName = ($relation[2] ? lcfirst(Inflector::singularize($generator->setRelation($name, true))) : $generator->setRelation($name));
    if(!in_array($relationName, $gridPublicVariables))
        $gridPublicVariables[$relationName] = ucwords(strtolower($relationName));
}

echo "\t\t\t\t'id' => Schema::TYPE_INTEGER . '(11) UNSIGNED NOT NULL',\n";
if(!empty($gridPublicVariables)) {
	foreach ($gridPublicVariables as $key=>$val) {
		echo "\t\t\t\t'$key' => Schema::TYPE_INTEGER . '(11) NOT NULL',\n";
	}
}

$tableName = $generator->generateTableName($tableName);

echo "\t\t\t\t'modified_date' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT \'trigger,on_update\'',\n";
echo "\t\t\t\t'PRIMARY KEY ([[id]])',\n";
echo "\t\t\t\t'CONSTRAINT {$gridTableName}_ibfk_1 FOREIGN KEY ([[id]]) REFERENCES $tableName ([[$primaryKey]]) ON DELETE CASCADE ON UPDATE CASCADE',\n";
?>
			], $tableOptions);
		}
	}

	public function down()
	{
		$tableName = Yii::$app->db->tablePrefix . '<?= $gridTableName ?>';
		$this->dropTable($tableName);
	}
}


