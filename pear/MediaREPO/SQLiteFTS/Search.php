<?php
/**
 * Implementation of search in SQlite FTS index
 *
 * @category   REPO
 * @package    mediarepo_Lucene
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010, Uwe Steinmann
 * @version    Release: 1.0.9
 */


/**
 * Class for searching in a SQlite FTS index.
 *
 * @category   REPO
 * @package    mediarepo_Lucene
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2011, Uwe Steinmann
 * @version    Release: 1.0.9
 */
class mediarepo_SQliteFTS_Search {
	/**
	 * @var object $index SQlite FTS index
	 * @access protected
	 */
	protected $index;

	/**
	 * Create a new instance of the search
	 *
	 * @param object $index SQlite FTS index
	 * @return object instance of mediarepo_SQliteFTS_Search
	 */
	function __construct($index) { /* {{{ */
		$this->index = $index;
		$this->version = '1.0.9';
		if($this->version[0] == '@')
			$this->version = '3.0.0';
	} /* }}} */

	/**
	 * Get hit from index
	 *
	 * @param object $index lucene index
	 * @return object instance of mediarepo_Lucene_Document of false
	 */
	function getDocument($id) { /* {{{ */
		$hits = $this->index->findById((int) $id);
		return $hits ? $hits[0] : false;
	} /* }}} */

	/**
	 * Search in index
	 *
	 * @param object $index SQlite FTS index
	 * @return object instance of mediarepo_Lucene_Search
	 */
	function search($term, $owner, $status='', $categories=array(), $fields=array()) { /* {{{ */
		$querystr = '';
		if($fields) {
		} else {
			if($term)
				$querystr .= trim($term);
		}
		if($owner) {
			if($querystr)
				$querystr .= ' ';
				//$querystr .= ' AND ';
			$querystr .= 'owner:'.$owner;
			//$querystr .= $owner;
		}
		if($categories) {
			if($querystr)
				$querystr .= ' ';
				//$querystr .= ' AND ';
			$querystr .= 'category:';
			$querystr .= implode(' OR category:', $categories);
			$querystr .= '';
		}
		try {
			$hits = $this->index->find($querystr);
			$recs = array();
			foreach($hits as $hit) {
				$recs[] = array('id'=>$hit->id, 'document_id'=>$hit->id);
			}
			return $recs;
		} catch (Exception $e) {
			return false;
		}
	} /* }}} */
}
?>
