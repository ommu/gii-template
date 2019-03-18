<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator mdm\gii\generators\migration\Generator */
/* @var $migrationName string migration name */

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

use yii\db\Schema;

class <?= $migrationName ?> extends \yii\db\Migration
{
	public function up()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}
<?php foreach ($tables as $table): ?>
		
		$this->createTable('<?= $table['name'] ?>', [
<?php foreach ($table['columns'] as $column => $definition): ?>
			<?= "'$column' => $definition"?>,
<?php endforeach;?>
<?php if(isset($table['primary'])): ?>
			<?= "'{$table['primary']}'" ?>,
<?php endif; ?>
<?php foreach ($table['relations'] as $definition): ?>
			<?= "'$definition'" ?>,
<?php endforeach;?>
		], $tableOptions);
<?php endforeach;?>
	}

	public function down()
	{
<?php foreach (array_reverse($tables) as $table): ?>
		$this->dropTable('<?= $table['name'] ?>');
<?php endforeach;?>
	}
}
