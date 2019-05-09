<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\form\Generator */

echo $form->field($generator, 'tableName', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('tableName'));

echo $form->field($generator, 'migrationPath', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('migrationPath'));

echo $form->field($generator, 'migrationTime', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('migrationTime'))
	->widget('yii\widgets\MaskedInput', [
		'mask' => '999999_999999'
	]);

echo $form->field($generator, 'migrationName', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('migrationName'));

echo $form->field($generator, 'db', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('db'));

echo $form->field($generator, 'useTablePrefix')
	->checkbox()
	->label($generator->getAttributeLabel('useTablePrefix'));

echo $form->field($generator, 'generateRelations')
	->checkbox()
	->label($generator->getAttributeLabel('generateRelations'));
	
echo $form->field($generator, 'useModified')
	->checkbox()
	->label($generator->getAttributeLabel('useModified'));

echo $form->field($generator, 'link', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('link'));
