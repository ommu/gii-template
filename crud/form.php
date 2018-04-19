<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\crud\Generator */

echo $form->field($generator, 'modelClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
	->label($generator->getAttributeLabel('modelClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'searchModelClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
	->label($generator->getAttributeLabel('searchModelClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'controllerClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
	->label($generator->getAttributeLabel('controllerClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'viewPath', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
	->label($generator->getAttributeLabel('viewPath'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'baseControllerClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
	->label($generator->getAttributeLabel('baseControllerClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'indexWidgetType', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
	->dropDownList([
		'grid' => 'GridView',
		'list' => 'ListView',
	])->label($generator->getAttributeLabel('indexWidgetType'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'enableI18N', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
	->checkbox()
	->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'messageCategory', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
	->label($generator->getAttributeLabel('messageCategory'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'enablePjax', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
	->checkbox()
	->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'useJuiDatePicker', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
	->checkbox()
	->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'attachRBACFilter', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
	->checkbox()
	->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);
	
echo $form->field($generator, 'useModified', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->checkbox()
    ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'link', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}	'])
	->label($generator->getAttributeLabel('link'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);