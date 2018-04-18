<?php
/**
 * SearchRules class
 *
 * berguna untuk kolom rules dan tipe rule pada generator crud
 */
namespace app\libraries\gii\crud;

class SearchRules
{
	public $columns = [];
	public $ruleType = null;

	public function __construct($columns, $ruleType) {
		$this->columns = $columns;
		$this->ruleType = $ruleType;
	}
}