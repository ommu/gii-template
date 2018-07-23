<h1>Module Generator</h1>

<p>This generator helps you to generate the skeleton code needed by a Yii module.</p>

<?php $form=$this->beginWidget('CCodeForm', array('model'=>$model)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'moduleID'); ?>
		<?php echo $form->textField($model,'moduleID',array('size'=>65)); ?>
		<div class="tooltip">
			Module ID is case-sensitive. It should only contain word characters.
			The generated module class will be named after the module ID.
			For example, a module ID <code>forum</code> will generate the module class
			<code>ForumModule</code>.
		</div>
		<?php echo $form->error($model,'moduleID'); ?>
	</div>

	<div class="row sticky">
		<?php echo $form->labelEx($model,'moduleName'); ?>
		<?php echo $form->textField($model,'moduleName', array('size'=>64)); ?>
		<div class="tooltip">
		It can be either a hyperlink (e.g. <code>https://github.com/ommu</code>)
		</div>
		<?php echo $form->error($model,'moduleName'); ?>
	</div>

	<div class="row sticky">
		<?php echo $form->labelEx($model,'moduleDesc'); ?>
		<?php echo $form->textField($model,'moduleDesc', array('size'=>64)); ?>
		<div class="tooltip">
		It can be either a hyperlink (e.g. <code>https://github.com/ommu</code>)
		</div>
		<?php echo $form->error($model,'moduleDesc'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'useModified'); ?>
		<?php echo $form->checkBox($model,'useModified'); ?>
		<div class="tooltip">
			Default value is <code>false</code>. Used to display modification date in source code
		</div>
		<?php echo $form->error($model,'useModified'); ?>
	</div>

	<div class="row sticky">
		<?php echo $form->labelEx($model,'link'); ?>
		<?php echo $form->textField($model,'link', array('size'=>64)); ?>
		<div class="tooltip">
		It can be either a hyperlink (e.g. <code>https://github.com/ommu</code>)
		</div>
		<?php echo $form->error($model,'link'); ?>
	</div>

<?php $this->endWidget(); ?>
