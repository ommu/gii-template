<?php
/**
 * Gii Generator for Backoffice Themes
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) 2017 ECC UGM (ecc.ft.ugm.ac.id)
 * @created date 7 September 2017, 08:23 WIB
 * @link http://ecc.ft.ugm.ac.id
 */

namespace ommu\gii;

use Yii;
use yii\helpers\VarDumper;
use yii\helpers\Inflector;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class Generator extends \yii\gii\Generator
{
	/**
	 * @return string name of the code generator
	 */
	public function getName() 
	{
		//do something
	}
	
	/**
	 * Generates the code based on the current user input and the specified code template files.
	 * This is the main method that child classes should implement.
	 * Please refer to [[\yii\gii\generators\controller\Generator::generate()]] as an example
	 * on how to implement this method.
	 * @return CodeFile[] a list of code files to be created.
	 */
	public function generate() 
	{
		//do something
	}
	
	/**
	 * Generates a string depending on enableI18N property
	 *
	 * @param string $string the text be generated
	 * @param array $placeholders the placeholders to use by `Yii::t()`
	 * @return string
	 */
	public function generateString($string = '', $placeholders = [])
	{
		$string = addslashes($string);
		if ($this->enableI18N) {
			$ph = self::VarDumper($placeholders);
			$str = "Yii::t('" . $this->messageCategory . "', '" . $string . "'" . $ph . ")";
		} else {
			// No I18N, replace placeholders by real words, if any
			if (!empty($placeholders)) {
				$phKeys = array_map(function($word) {
					return '{' . $word . '}';
				}, array_keys($placeholders));
				$phValues = array_values($placeholders);
				$str = "'" . str_replace($phKeys, $phValues, $string) . "'";
			} else {
				// No placeholders, just the given string
				$str = "'" . $string . "'";
			}
		}
		return $str;
	}
	
	/**
	 * Generates a string depending on enableI18N property
	 *
	 * @param string $string the text be generated
	 * @param array $placeholders the placeholders to use by `Yii::t()`
	 * @return string
	 */
	public static function VarDumper($placeholders)
	{
		// If there are placeholders, use them
		if (!empty($placeholders)) {
			$count = count($placeholders);
			$i=0;
			$ph = ', [';
			foreach($placeholders as $key => $val) {
				$i++;
				if($i == $count)
					$ph .= preg_match('/^[$]/', $val) ? "'$key' => $val" : "'$key' => '$val'";
				else
					$ph .= preg_match('/^[$]/', $val) ? "'$key' => $val, " : "'$key' => '$val', ";
			}
			$ph .= ']';
		} else {
			$ph = '';
		}

		return $ph;
	}
	
	/**
	 * set name relation with underscore
	 */
	public function setRelationName($names, $model=false) 
	{
		if($model == true) {
			$patterns = array();
			$patterns[0] = '(_ommu)';
			$patterns[1] = '(_core)';
			$patterns[2] = '(_swt)';

			$char=range("A","Z");
			foreach($char as $val) {
				if(strpos($names, $val) !== false) {
					$names = str_replace($val, '_'.strtolower($val), $names);
				}
			}
			$return = trim(preg_replace($patterns, '', $names), '_');
			$return = array_map('ucfirst', explode('_', $return));
			//print_r($return);
			if(count($return) != 1)
				return end($return);
			else {
				if(is_array($return))
					return implode('', $return);
				else
					return $return;
			}

		} else {
			$key = $names;
			if (!empty($key) && strcasecmp($key, 'id')) {
				if (substr_compare($key, 'id', -2, 2, true) === 0) {
					$key = rtrim(substr($key, 0, -2), '_');
				} elseif (substr_compare($key, 'id', 0, 2, true) === 0) {
					$key = ltrim(substr($key, 2, strlen($key)), '_');
				}
			}
			if(strtolower($key) == 'cat')
				$key = 'category';

			$key = Inflector::singularize(Inflector::id2camel($key, '_'));

			return lcfirst($key);
		}
	}
	
	public function getForeignKeys($tableForeignKeys) 
	{
		$column = [];
		if(!empty($tableForeignKeys)) {
			foreach($tableForeignKeys as $val) {
				// Only variables should be passed by reference
				$arrKey = array_keys($val);
				$arrVal = array_values($val);
				$column[array_pop($arrKey)] = array_shift($arrVal);
			}
		}
	
		return $column;
	}

	// Parse yaml dari file
	// contoh: `
	// name: susilo
	// `
	// $fileName -> demo.yaml
	// @return php array/object
	public function loadYaml($fileName) 
	{
		$fname = join('/', [Yii::getAlias('@webroot'), $fileName]);
		if(!file_exists($fname)) {
			$errMsg  = 'File "author.yaml" tidak dapat ditemukan, dimohon untuk membuat file ';
			$errMsg .= '"author.yaml" pada folder ' . Yii::getAlias('@webroot');
			throw new \Exception($errMsg);
		}

		try {
			$data = Yaml::parse(file_get_contents($fname));
		} catch(ParseException $e) {
			echo $e->getMessage();
		}
		
		return $data;
	}

	/**
	 * @inheritdoc
	 */
	public function validateNewClass($attribute, $params) {
		$class = ltrim($this->$attribute, '\\');
		if(($pos = strrpos($class, '\\')) === false) {
			$this->addError($attribute, "The class name must contain fully qualified namespace name.");
		}else {
			$ns   = substr($class, 0, $pos);
			$path = Yii::getAlias('@' . str_replace('\\', '/', $ns), false);

			if ($path === false) {
				$this->addError($attribute, "The class namespace is invalid: $ns");
			}elseif (!is_dir($path)) {
				@mkdir($path);
				// $this->addError($attribute, "Please make sure the directory containing this class exists: $path");
			}
		}
	}
}
