<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\crud\Generator */

echo $form->field($generator, 'modelClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('modelClass'));

echo $form->field($generator, 'searchModelClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('searchModelClass'));

echo $form->field($generator, 'controllerClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('controllerClass'));

echo $form->field($generator, 'viewPath', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('viewPath'));

echo $form->field($generator, 'baseControllerClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('baseControllerClass'));

echo $form->field($generator, 'indexWidgetType', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->dropDownList([
		'grid' => 'GridView',
		'list' => 'ListView',
	])
	->label($generator->getAttributeLabel('indexWidgetType'));

echo $form->field($generator, 'enableI18N')
	->checkbox()
	->label($generator->getAttributeLabel('enableI18N'));

echo $form->field($generator, 'messageCategory', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('messageCategory'));

echo $form->field($generator, 'enablePjax')
	->checkbox()
	->label($generator->getAttributeLabel('enablePjax'));

echo $form->field($generator, 'attachRBACFilter')
	->checkbox()
	->label($generator->getAttributeLabel('attachRBACFilter'));

echo $form->field($generator, 'uploadPathSubfolder')
	->checkbox()
	->label($generator->getAttributeLabel('uploadPathSubfolder'));
	
echo $form->field($generator, 'useModified')
    ->checkbox()
	->label($generator->getAttributeLabel('useModified'));

echo $form->field($generator, 'link', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('link'));