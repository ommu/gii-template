<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\form\Generator */

echo $form->field($generator, 'viewName', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('viewName'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'modelClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('modelClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'scenarioName', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('scenarioName'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'viewPath', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('viewPath'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'enableI18N', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'messageCategory', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('messageCategory'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);
