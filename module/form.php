<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\module\Generator */

echo $form->field($generator, 'moduleClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('moduleClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'moduleID', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('moduleID'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'description', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('description'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'keyword', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('keyword'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'moduleCore', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);