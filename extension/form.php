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
echo $form->field($generator, 'vendorName', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('vendorName'));

echo $form->field($generator, 'packageName', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('packageName'));

echo $form->field($generator, 'namespace', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('namespace'));

echo $form->field($generator, 'type', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->dropDownList($generator->optsType())
	->label($generator->getAttributeLabel('type'));

echo $form->field($generator, 'keywords', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('keywords'));

echo $form->field($generator, 'license', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->dropDownList($generator->optsLicense(), ['prompt'=>'Choose...'])
	->label($generator->getAttributeLabel('license'));

echo $form->field($generator, 'title', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('title'));

echo $form->field($generator, 'description', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('description'));

echo $form->field($generator, 'authorName', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('authorName'));

echo $form->field($generator, 'authorEmail', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('authorEmail'));

echo $form->field($generator, 'outputPath', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-md-9 col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('outputPath'));
?>
