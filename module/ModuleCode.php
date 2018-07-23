<?php

class ModuleCode extends CCodeModel
{
	public $moduleID;

	public $moduleName;
	public $moduleDesc;
	public $useModified=false;
	public $link='https://github.com/ommu';

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('moduleID, moduleName, moduleDesc, link', 'filter', 'filter'=>'trim'),
			array('moduleID, moduleName, useModified, link', 'required'),
			array('moduleID', 'match', 'pattern'=>'/^\w+$/', 'message'=>'{attribute} should only contain word characters.'),
			array('moduleName, moduleDesc, link', 'sticky'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'moduleID'=>'Module ID',
			'useModified'=>'Modified',
			'link'=>'Link Repository',
		));
	}

	public function successMessage()
	{
		if(Yii::app()->hasModule($this->moduleID))
			return 'The module has been generated successfully. You may '.CHtml::link('try it now', Yii::app()->createUrl($this->moduleID), array('target'=>'_blank')).'.';

		$output=<<<EOD
<p>The module has been generated successfully.</p>
<p>To access the module, you need to modify the application configuration as follows:</p>
EOD;
		$code=<<<EOD
<?php
return array(
    'modules'=>array(
        '{$this->moduleID}',
    ),
    ......
);
EOD;

		return $output.highlight_string($code,true);
	}

	public function prepare()
	{
		$this->files=array();
		$templatePath=$this->templatePath;
		$modulePath=$this->modulePath;
		$moduleTemplateFile=$templatePath.DIRECTORY_SEPARATOR.'module.php';
		$moduleYAMLTemplateFile=$templatePath.DIRECTORY_SEPARATOR.'module.yaml.php';

		$this->files[]=new CCodeFile(
			$modulePath.'/'.$this->moduleClass.'.php',
			$this->render($moduleTemplateFile)
		);
		$this->files[]=new CCodeFile(
			$modulePath.'/'.$this->moduleID.'.yaml',
			$this->render($moduleYAMLTemplateFile)
		);

		$files=CFileHelper::findFiles($templatePath,array(
			'exclude'=>array(
				'.svn',
				'.gitignore',
				'.yaml',
			),
		));

		foreach($files as $file)
		{
			if($file!==$moduleTemplateFile)
			{
				if(CFileHelper::getExtension($file)==='php')
					$content=$this->render($file);
				elseif(basename($file)==='.gitkeep')  // an empty directory
				{
					$file=dirname($file);
					$content=null;
				}
				else
					$content=file_get_contents($file);
				if(!preg_match('/(yaml)/', $file)) {
					$this->files[]=new CCodeFile(
						$modulePath.substr($file,strlen($templatePath)),
						$content
					);
				}
			}
		}
	}

	public function getModuleClass()
	{
		return ucfirst($this->moduleID).'Module';
	}

	public function getModulePath()
	{
		return Yii::app()->modulePath.DIRECTORY_SEPARATOR.$this->moduleID;
	}

	public function getModuleName()
	{
		return $this->moduleName;
	}

	public function getModuleDescription()
	{
		return $this->moduleDesc;
	}

	public function getUseModified()
	{
		return $this->useModified;
	}

	public function getLinkSource()
	{
		return $this->link;
	}
}