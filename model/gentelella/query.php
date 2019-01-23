<?php
/**
 * This is the template for generating the ActiveQuery class.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */
/* @var $className string class name */
/* @var $modelClassName string related model class name */

$modelFullClassName = $modelClassName;
if ($generator->ns !== $generator->queryNs) {
	$modelFullClassName = '\\' . $generator->ns . '\\' . $modelFullClassName;
}

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?= $className."\n" ?>
 *
 * This is the ActiveQuery class for [[<?= $modelFullClassName ?>]].
 * @see <?= $modelFullClassName . "\n" ?>
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

namespace <?= $generator->queryNs ?>;

class <?= $className ?> extends <?= '\\' . ltrim($generator->queryBaseClass, '\\') . "\n" ?>
{
	/*
	public function active()
	{
		return $this->andWhere('[[status]]=1');
	}
	*/
<?php foreach ($tableSchema->columns as $column):
	if(!($column->dbType == 'tinyint(1)' && in_array($column->name, ['publish','headline'])))
		continue;
	
	if($column->name == 'publish'):?>

	/**
	 * {@inheritdoc}
	 */
	public function published() 
	{
		return $this->andWhere(['<?php echo $column->name;?>' => 1]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function unpublish() 
	{
		return $this->andWhere(['<?php echo $column->name;?>' => 0]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function deleted() 
	{
		return $this->andWhere(['<?php echo $column->name;?>' => 2]);
	}
<?php elseif($column->name == 'headline'):?>

	/**
	 * {@inheritdoc}
	 */
	public function published() 
	{
		return $this->andWhere(['publish' => 1])->andWhere(['<?php echo $column->name;?>' => 1]);
	}
<?php endif;
endforeach;?>

	/**
	 * {@inheritdoc}
	 * @return <?= $modelFullClassName ?>[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return <?= $modelFullClassName ?>|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
