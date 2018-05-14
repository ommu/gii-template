<?php
$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?= $generator->moduleID ?> module config
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

return [
	'id' => '<?=$generator->moduleID?>',
	'class' => <?=$generator->moduleClass?>::className(),
<?=($generator->moduleCore? "\t'isCoreModule' => true,\n":'')?>
];