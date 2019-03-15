<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\crud\Generator */

echo $form->field($generator, 'modelClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('modelClass'));

echo $form->field($generator, 'searchModelClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('searchModelClass'));

echo $form->field($generator, 'controllerClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('controllerClass'));

echo $form->field($generator, 'viewPath', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('viewPath'));

echo $form->field($generator, 'baseControllerClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('baseControllerClass'));

echo $form->field($generator, 'indexWidgetType', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->dropDownList([
		'grid' => 'GridView',
		'list' => 'ListView',
	])
	->label($generator->getAttributeLabel('indexWidgetType'));

echo $form->field($generator, 'enableI18N', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('enableI18N'));

echo $form->field($generator, 'messageCategory', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('messageCategory'));

echo $form->field($generator, 'enablePjax', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('enablePjax'));

echo $form->field($generator, 'attachRBACFilter', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('attachRBACFilter'));

echo $form->field($generator, 'uploadPathSubfolder', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('uploadPathSubfolder'));
	
echo $form->field($generator, 'useModified', ['horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
    ->checkbox()
	->label($generator->getAttributeLabel('useModified'));

echo $form->field($generator, 'link', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('link'));