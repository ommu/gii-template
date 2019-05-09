<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\module\Generator */

echo $form->field($generator, 'moduleClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('moduleClass'));

echo $form->field($generator, 'moduleID', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('moduleID'));

echo $form->field($generator, 'description', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('description'));

echo $form->field($generator, 'keyword', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('keyword'));

echo $form->field($generator, 'moduleCore')
	->checkbox()
	->label($generator->getAttributeLabel('moduleCore'));

echo $form->field($generator, 'useModified')
	->checkbox()
	->label($generator->getAttributeLabel('useModified'));

echo $form->field($generator, 'link', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}'])
	->label($generator->getAttributeLabel('link'));