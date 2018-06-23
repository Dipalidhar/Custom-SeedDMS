<?php
/**
 * Implementation of a query hit
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
 * Class for managing a query hit.
 *
 * @category   REPO
 * @package    mediarepo_SQLiteFTS
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2011, Uwe Steinmann
 * @version    Release: 1.0.9
 */
class mediarepo_SQLiteFTS_QueryHit {

	/**
	 * @var mediarepo_SQliteFTS_Indexer $index
	 * @access protected
	 */
	protected $_index;

	/**
	 * @var mediarepo_SQliteFTS_Document $document
	 * @access protected
	 */
	protected $_document;

	/**
	 * @var integer $id id of document
	 * @access public
	 */
	public $id;

	/**
	 *
	 */
	public function __construct(mediarepo_SQLiteFTS_Indexer $index) { /* {{{ */
		$this->_index = $index;
	} /* }}} */

	/**
	 * Return the document associated with this hit
	 *
	 * @return mediarepo_SQLiteFTS_Document
	 */
	public function getDocument() { /* {{{ */
		if (!$this->_document instanceof mediarepo_SQLiteFTS_Document) {
			$this->_document = $this->_index->getDocument($this->id);
		}

		return $this->_document;
	} /* }}} */
}
?>
