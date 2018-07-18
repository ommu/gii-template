<?php
$class=get_class($model);
Yii::app()->clientScript->registerScript('gii.crud',"
$('#{$class}_controller').change(function(){
	$(this).data('changed',$(this).val()!='');
});
$('#{$class}_model').bind('keyup change', function(){
	var controller=$('#{$class}_controller');
	if(!controller.data('changed')) {
		var id=new String($(this).val().match(/\\w*$/));
		if(id.length>0)
			id=id.substring(0,1).toLowerCase()+id.substring(1);
		controller.val(id);
	}
});
");
?>
<h1>Crud Generator</h1>

<p>This generator generates a controller and views that implement CRUD operations for the specified data model.</p>

<?php $form=$this->beginWidget('CCodeForm', array('model'=>$model)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'model'); ?>
		<?php echo $form->textField($model,'model',array('size'=>65)); ?>
		<div class="tooltip">
			Model class is case-sensitive. It can be either a class name (e.g. <code>Post</code>)
		    or the path alias of the class file (e.g. <code>application.models.Post</code>).
		    Note that if the former, the class must be auto-loadable.
		</div>
		<?php echo $form->error($model,'model'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'controller'); ?>
		<?php echo $form->textField($model,'controller',array('size'=>65)); ?>
		<div class="tooltip">
			Controller ID is case-sensitive. CRUD controllers are often named after
			the model class name that they are dealing with. Below are some examples:
			<ul>
				<li><code>post</code> generates <code>PostController.php</code></li>
				<li><code>postTag</code> generates <code>PostTagController.php</code></li>
				<li><code>admin/user</code> generates <code>admin/UserController.php</code>.
					If the application has an <code>admin</code> module enabled,
					it will generate <code>UserController</code> (and other CRUD code)
					within the module instead.
				</li>
			</ul>
		</div>
		<?php echo $form->error($model,'controller'); ?>
	</div>

	<div class="row sticky">
		<?php echo $form->labelEx($model,'baseControllerClass'); ?>
		<?php echo $form->textField($model,'baseControllerClass',array('size'=>65)); ?>
		<div class="tooltip">
			This is the class that the new CRUD controller class will extend from.
			Please make sure the class exists and can be autoloaded.
		</div>
		<?php echo $form->error($model,'baseControllerClass'); ?>
	</div>

	<div class="row sticky">
		<?php echo $form->labelEx($model,'controllerPath'); ?>
		<?php echo $form->textField($model,'controllerPath',array('size'=>65)); ?>
		<div class="tooltip">
			This refers to the directory that the new controller class file should be generated under.
			It should be specified in the form of a path alias, for example, <code>application.controllers</code>.
		</div>
		<?php echo $form->error($model,'controllerPath'); ?>
	</div>

	<div class="row sticky">
		<?php echo $form->labelEx($model,'viewPath'); ?>
		<?php echo $form->textField($model,'viewPath',array('size'=>65)); ?>
		<div class="tooltip">
			This refers to the directory that the new view render file should be generated under.
			It should be specified in the form of a path alias, for example, <code>application.views</code>.
		</div>
		<?php echo $form->error($model,'viewPath'); ?>
	</div>

	<div class="row sticky">
		<?php echo $form->labelEx($model,'uploadPath[directory]'); ?>
		<?php echo $form->textField($model,'uploadPath[directory]',array('size'=>64)); ?>
		<div class="tooltip">
		It can be either a upload path directory (e.g. <code>public/module-name</code>)
		</div>
		<?php echo $form->error($model,'uploadPath[directory]'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uploadPath[subfolder]'); ?>
		<?php echo $form->checkBox($model,'uploadPath[subfolder]'); ?>
		<div class="tooltip">
		It can be either a upload path directory (e.g. <code>public/module-name</code>)
		</div>
		<?php echo $form->error($model,'uploadPath[subfolder]'); ?>
	</div>

	<?php 
	$functions = $model->defaultFunction;
	if(!$model->getErrors() && $model->model) {
		$class=@Yii::import($model->model,true);
		$table=CActiveRecord::model($class)->tableSchema;
		$columns = $table->columns;
		foreach($columns as $name=>$column) {
			if($column->dbType == 'tinyint(1)' && (in_array($column->name, array('publish','headline')) || $column->comment != '')) {
				$functions[$column->name] = array(
					'redirect' => true,
					'dialog' => false,
					'file' => "admin_$column->name.php",
				);
			}
		}
	}?>
	<div style="margin: 5px 0;">
		<table class="preview">
			<tr>
				<th class="file"><?php echo $form->labelEx($model,'generateCode'); ?></th>
				<th class="file">Generate</th>
				<th class="file">Dialog</th>
				<th class="file">Redirect</th>
			</tr>
<?php if(!empty($functions)) {
	$redirect = array(
		'manage'=>'manage',
		'update'=>'update',
		'view'=>'view',
	);
	foreach($functions as $key => $val) {?>
		<tr>
			<td class="file"><?php echo ucwords($key);?> <small>"<em><?php echo $val['file'];?></em>"</small></td>
			<td class="confirm"><?php echo $form->checkBox($model,"generateCode[$key][generate]"); ?></td>
			<td class="confirm"><?php echo $val['dialog'] == true ? $form->checkBox($model,"generateCode[$key][dialog]") : '-'; ?></td>
			<td class="confirm"><?php echo $val['redirect'] == true ? $form->dropDownList($model,"generateCode[$key][redirect]", $redirect) : '-';?></td>
		</tr>
<?php }
}?>
		</table>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'useModified'); ?>
		<?php echo $form->checkBox($model,'useModified'); ?>
		<div class="tooltip">
			Default value is <code>false</code>. Digunakan untuk menampilkan tanggal perubahan generate pada source code
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
