<?php

use yii\gii\generators\model\Generator;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\model\Generator */

echo $form->field($generator, 'tableName', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->textInput(['table_prefix' => $generator->getTablePrefix()])
    ->label($generator->getAttributeLabel('tableName'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'modelClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('modelClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'ns', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('ns'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'baseClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('baseClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'db', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('db'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'useTablePrefix', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'generateRelations', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->dropDownList([
        Generator::RELATIONS_NONE => 'No relations',
        Generator::RELATIONS_ALL => 'All relations',
        Generator::RELATIONS_ALL_INVERSE => 'All relations with inverse',
    ])
    ->label($generator->getAttributeLabel('generateRelations'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'generateLabelsFromComments', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'generateQuery', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'queryNs', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('queryNs'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'queryClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('queryClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'queryBaseClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('queryBaseClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'enableI18N', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'messageCategory', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('messageCategory'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'useSchemaName', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'generateEvents', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'generateMessage', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);