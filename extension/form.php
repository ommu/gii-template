<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\extension\Generator */

?>
<div class="alert alert-info">
    Please read the
    <?= \yii\helpers\Html::a('Extension Guidelines', 'http://www.yiiframework.com/doc-2.0/guide-structure-extensions.html', ['target'=>'new']) ?>
    before creating an extension.
</div>

<?php
echo $form->field($generator, 'vendorName', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('vendorName'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'packageName', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('packageName'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'namespace', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('namespace'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'type', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->dropDownList($generator->optsType())
    ->label($generator->getAttributeLabel('type'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'keywords', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('keywords'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'license', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->dropDownList($generator->optsLicense(), ['prompt'=>'Choose...'])
    ->label($generator->getAttributeLabel('license'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'title', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('title'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'description', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('description'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'authorName', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('authorName'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'authorEmail', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('authorEmail'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);

echo $form->field($generator, 'outputPath', ['template' => '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}{error}</div>{hint}'])
    ->label($generator->getAttributeLabel('outputPath'), ['class'=>'control-label col-md-3 col-sm-3 col-xs-12']);
?>
