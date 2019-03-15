<?php

use yii\gii\generators\model\Generator;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\model\Generator */

echo $form->field($generator, 'tableName', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->textInput(['table_prefix' => $generator->getTablePrefix()])
	->label($generator->getAttributeLabel('tableName'));

echo $form->field($generator, 'modelClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('modelClass'));

echo $form->field($generator, 'ns', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('ns'));

echo $form->field($generator, 'baseClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('baseClass'));

echo $form->field($generator, 'db', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('db'));

echo $form->field($generator, 'useTablePrefix', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('useTablePrefix'));

echo $form->field($generator, 'generateRelations', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->dropDownList([
		Generator::RELATIONS_NONE => 'No relations',
		Generator::RELATIONS_ALL => 'All relations',
		Generator::RELATIONS_ALL_INVERSE => 'All relations with inverse',
	])
	->label($generator->getAttributeLabel('generateRelations'));

echo $form->field($generator, 'generateRelationsFromCurrentSchema', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('generateRelationsFromCurrentSchema'));

echo $form->field($generator, 'generateLabelsFromComments', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('generateLabelsFromComments'));

echo $form->field($generator, 'generateQuery', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('generateQuery'));

echo $form->field($generator, 'queryNs', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('queryNs'));

echo $form->field($generator, 'queryClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('queryClass'));

echo $form->field($generator, 'queryBaseClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('queryBaseClass'));

echo $form->field($generator, 'enableI18N', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('enableI18N'));

echo $form->field($generator, 'messageCategory', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('messageCategory'));

echo $form->field($generator, 'useSchemaName', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('useSchemaName'));

echo $form->field($generator, 'generateMessage', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('generateMessage'));

echo $form->field($generator, 'generateEvents', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('generateEvents'));

echo $form->field($generator, 'uploadPath[directory]', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('uploadPath[directory]'));

echo $form->field($generator, 'uploadPath[subfolder]')
	->checkbox()
	->label($generator->getAttributeLabel('uploadPath[subfolder]'));

echo $form->field($generator, 'useGetFunction', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('useGetFunction'));
	
echo $form->field($generator, 'useModified', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('useModified'));

echo $form->field($generator, 'link', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('link'));