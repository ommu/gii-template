<?php
$class=get_class($model);
Yii::app()->clientScript->registerScript('gii.model',"
$('#{$class}_connectionId').change(function(){
	var tableName=$('#{$class}_tableName');
	tableName.autocomplete('option', 'source', []);
	$.ajax({
		url: '".Yii::app()->getUrlManager()->createUrl('gii/model/getTableNames')."',
		data: {db: this.value},
		dataType: 'json'
	}).done(function(data){
		tableName.autocomplete('option', 'source', data);
	});
});
$('#{$class}_modelClass').change(function(){
	$(this).data('changed',$(this).val()!='');
});
$('#{$class}_tableName').bind('keyup change', function(){
	var model=$('#{$class}_modelClass');
	var tableName=$(this).val();
	if(tableName.substring(tableName.length-1)!='*') {
		$('.form .row.model-class').show();
	}
	else {
		$('#{$class}_modelClass').val('');
		$('.form .row.model-class').hide();
	}
	if(!model.data('changed')) {
		var i=tableName.lastIndexOf('.');
		if(i>=0)
			tableName=tableName.substring(i+1);
		var tablePrefix=$('#{$class}_tablePrefix').val();
		if(tablePrefix!='' && tableName.indexOf(tablePrefix)==0)
			tableName=tableName.substring(tablePrefix.length);
		var modelClass='';
		$.each(tableName.split('_'), function() {
			if(this.length>0)
				modelClass+=this.substring(0,1).toUpperCase()+this.substring(1);
		});
		model.val(modelClass);
	}
});
$('.form .row.model-class').toggle($('#{$class}_tableName').val().substring($('#{$class}_tableName').val().length-1)!='*');
");
?>
<h1>Model Generator</h1>

<p>This generator generates a model class for the specified database table.</p>

<?php $form=$this->beginWidget('CCodeForm', array('model'=>$model)); ?>

	<div class="row sticky">
		<?php echo $form->labelEx($model, 'connectionId')?>
		<?php echo $form->textField($model, 'connectionId', array('size'=>65))?>
		<div class="tooltip">
		The database component that should be used.
		</div>
		<?php echo $form->error($model,'connectionId'); ?>
	</div>
	<div class="row sticky">
		<?php echo $form->labelEx($model,'tablePrefix'); ?>
		<?php echo $form->textField($model,'tablePrefix', array('size'=>65)); ?>
		<div class="tooltip">
		This refers to the prefix name that is shared by all database tables.
		Setting this property mainly affects how model classes are named based on
		the table names. For example, a table prefix <code>tbl_</code> with a table name <code>tbl_post</code>
		will generate a model class named <code>Post</code>.
		<br/>
		Leave this field empty if your database tables do not use common prefix.
		</div>
		<?php echo $form->error($model,'tablePrefix'); ?>
	</div>
	<div class="row sticky">
		<?php echo $form->label($model,'moduleName'); ?>
		<?php echo $form->textField($model,'moduleName', array('size'=>32)); ?>
		<div class="tooltip">
		Module Name.
		</div>
		<?php echo $form->error($model,'moduleName'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'tableName'); ?>
		<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'model'=>$model,
			'attribute'=>'tableName',
			'name'=>'tableName',
			'source'=>Yii::app()->hasComponent($model->connectionId) ? array_keys(Yii::app()->{$model->connectionId}->schema->getTables()) : array(),
			'options'=>array(
				'minLength'=>'0',
				'focus'=>new CJavaScriptExpression('function(event,ui) {
					$("#'.CHtml::activeId($model,'tableName').'").val(ui.item.label).change();
					return false;
				}')
			),
			'htmlOptions'=>array(
				'id'=>CHtml::activeId($model,'tableName'),
				'size'=>'65',
				'data-tooltip'=>'#tableName-tooltip'
			),
		)); ?>
		<div class="tooltip" id="tableName-tooltip">
		This refers to the table name that a new model class should be generated for
		(e.g. <code>tbl_user</code>). It can contain schema name, if needed (e.g. <code>public.tbl_post</code>).
		You may also enter <code>*</code> (or <code>schemaName.*</code> for a particular DB schema)
		to generate a model class for EVERY table.
		</div>
		<?php echo $form->error($model,'tableName'); ?>
	</div>
	<div class="row model-class">
		<?php echo $form->label($model,'modelClass', array('required'=>true)); ?>
		<?php echo $form->textField($model,'modelClass', array('size'=>65)); ?>
		<div class="tooltip">
		This is the name of the model class to be generated (e.g. <code>Post</code>, <code>Comment</code>).
		It is case-sensitive.
		</div>
		<?php echo $form->error($model,'modelClass'); ?>
	</div>
	<div class="row sticky">
		<?php echo $form->labelEx($model,'baseClass'); ?>
		<?php echo $form->textField($model,'baseClass', array('size'=>65)); ?>
		<div class="tooltip">
			This is the class that the new model class will extend from.
			Please make sure the class exists and can be autoloaded.
		</div>
		<?php echo $form->error($model,'baseClass'); ?>
	</div>
	<div class="row sticky">
		<?php echo $form->labelEx($model,'modelPath'); ?>
		<?php echo $form->textField($model,'modelPath', array('size'=>65)); ?>
		<div class="tooltip">
			This refers to the directory that the new model class file should be generated under.
			It should be specified in the form of a path alias, for example, <code>application.models</code>.
		</div>
		<?php echo $form->error($model,'modelPath'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'buildRelations'); ?>
		<?php echo $form->checkBox($model,'buildRelations'); ?>
		<div class="tooltip">
			Whether relations should be generated for the model class.
			In order to generate relations, full scan of the whole database is needed.
			You should disable this option if your database contains too many tables.
		</div>
		<?php echo $form->error($model,'buildRelations'); ?>
	</div>

	<div class="row sticky">
		<?php echo $form->labelEx($model,'uploadPath[directory]'); ?>
		<?php echo $form->textField($model,'uploadPath[directory]', array('size'=>64)); ?>
		<div class="tooltip">
		It can be either a upload path directory (e.g. <code>public/module-name</code>)
		</div>
		<?php echo $form->error($model,'uploadPath[directory]'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uploadPath[subfolder]'); ?>
		<?php echo $form->checkBox($model,'uploadPath[subfolder]'); ?>
		<div class="tooltip">
		Default value is <code>false</code>. Digunakan untuk menambahkan sub directory berupa id (nomor) pada upload path directory.
		</div>
		<?php echo $form->error($model,'uploadPath[subfolder]'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'useEvent'); ?>
		<?php echo $form->checkBox($model,'useEvent'); ?>
		<div class="tooltip">
			Default value is <code>false</code>. Should we generate event afterSave, before/afterDelete, afterValidate etc.
		</div>
		<?php echo $form->error($model,'useEvent'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'useGetFunction'); ?>
		<?php echo $form->checkBox($model,'useGetFunction'); ?>
		<div class="tooltip">
			Default value is <code>false</code>. Digunakan untuk menggenerate function get pada models.
		</div>
		<?php echo $form->error($model,'useGetFunction'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'useModified'); ?>
		<?php echo $form->checkBox($model,'useModified'); ?>
		<div class="tooltip">
			Default value is <code>false</code>. Used to display modification date in source code
		</div>
		<?php echo $form->error($model,'useModified'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'commentsAsLabels'); ?>
		<?php echo $form->checkBox($model,'commentsAsLabels'); ?>
		<div class="tooltip">
			Whether comments specified for the table columns should be used as the new model's attribute labels.
			In case your RDBMS doesn't support feature of commenting columns or column comment wasn't set,
			column name would be used as the attribute name base.
		</div>
		<?php echo $form->error($model,'commentsAsLabels'); ?>
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
