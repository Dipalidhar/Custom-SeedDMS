<?php
/**
 * Implementation of a document
 *
 * @category   REPO
 * @package    mediarepo_SQLiteFTS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010, Uwe Steinmann
 * @version    Release: 1.0.9
 */


/**
 * Class for managing a document.
 *
 * @category   REPO
 * @package    mediarepo_SQLiteFTS
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2011, Uwe Steinmann
 * @version    Release: 1.0.9
 */
class mediarepo_SQLiteFTS_Document {

	/**
	 * @var integer $id id of document
	 * @access protected
	 */
	public $id;

	/**
	 * @var array $fields fields
	 * @access protected
	 */
	protected $fields;

	public function __get($key) { /* {{{ */
		if(isset($this->fields[$key]))
			return $this->fields[$key];
		else
			return false;
	} /* }}} */

	public function addField($key, $value) { /* {{{ */
		if($key == 'document_id') {
			$this->id = $this->fields[$key] = (int) $value;
		} else {
			if(isset($this->fields[$key]))
				$this->fields[$key] .= ' '.$value;
			else
				$this->fields[$key] = $value;
		}
	} /* }}} */

	public function getFieldValue($key) { /* {{{ */
		if(isset($this->fields[$key]))
			return $this->fields[$key];
		else
			return false;
	} /* }}} */

}
?>
