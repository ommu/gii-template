<?php
echo "<?php\n";
?>
return [
    'id' => '<?=$generator->moduleID?>',
    'class' => <?=$generator->moduleClass?>::className(),
<?=($generator->moduleCore? "   'isCoreModule' => true,\n":'')?>
];