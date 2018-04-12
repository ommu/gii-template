<?php
echo $form->field($generator, 'controllerClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('controllerClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'modelClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('modelClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'baseClass', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('baseClass'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'authType', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
    ->dropDownList([
        0 => 'CompositeAuth', 
        1 => 'HttpBasicAuth', 
        2 => 'HttpBearerAuth',
        3 => 'QueryParamAuth'])
    ->label($generator->getAttributeLabel('authType'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);
// echo $form->field($generator, 'nonCrudApi', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}{hint}</div>'])
//     ->checkbox()
//     ->label('', ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);