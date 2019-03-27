<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\controller\Generator */

echo $form->field($generator, 'controllerClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('controllerClass'));

echo $form->field($generator, 'actions', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('actions'));

echo $form->field($generator, 'viewPath', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('viewPath'));

echo $form->field($generator, 'baseClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('baseClass'));

echo $form->field($generator, 'enableI18N', ['horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('enableI18N'));

echo $form->field($generator, 'attachRBACFilter', ['horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('attachRBACFilter'));
	
echo $form->field($generator, 'useModified', ['horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('useModified'));

echo $form->field($generator, 'link', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('link'));
