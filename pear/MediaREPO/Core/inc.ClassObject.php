<?php
/**
 * Implementation of an generic object in the document management system
 *
 * @category   REPO
 * @package    mediarepo_Core
 * @license    GPL2
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2012 Uwe Steinmann
 * @version    Release: 5.1.7
 */


/**
 * Class to represent a generic object in the document management system
 *
 * This is the base class for generic objects in MediaREPO.
 *
 * @category   REPO
 * @package    mediarepo_Core
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2012 Uwe Steinmann
 * @version    Release: 5.1.7
 */
class mediarepo_Core_Object { /* {{{ */
	/**
	 * @var integer unique id of object
	 */
	protected $_id;

	/**
	 * @var array list of attributes
	 */
	protected $_attributes;

	/**
	 * @var mediarepo_Core_REPO back reference to document management system
	 */
	public $_repo;

    /**
     * mediarepo_Core_Object constructor.
     * @param $id
     */
	function __construct($id) { /* {{{ */
		$this->_id = $id;
		$this->_repo = null;
	} /* }}} */

	/**
	 * Set repo this object belongs to.
	 *
	 * Each object needs a reference to the repo it belongs to. It will be
	 * set when the object is created.
	 * The repo has a references to the currently logged in user
	 * and the database connection.
	 *
	 * @param mediarepo_Core_REPO $repo reference to repo
	 */
	function setREPO($repo) { /* {{{ */
		$this->_repo = $repo;
	} /* }}} */

	/**
	 * Return the internal id of the document
	 *
	 * @return integer id of document
	 */
	function getID() { return $this->_id; }

	/**
	 * Returns all attributes set for the object
	 *
	 * @return array|bool
     */
	function getAttributes() { /* {{{ */
		if (!$this->_attributes) {
			$db = $this->_repo->getDB();

			switch(get_class($this)) {
				case $this->_repo->getClassname('document'):
					$queryStr = "SELECT a.* FROM `tblDocumentAttributes` a LEFT JOIN `tblAttributeDefinitions` b ON a.`attrdef`=b.`id` WHERE a.`document` = " . $this->_id." ORDER BY b.`name`";
					break;
				case $this->_repo->getClassname('documentcontent'):
					$queryStr = "SELECT a.* FROM `tblDocumentContentAttributes` a LEFT JOIN `tblAttributeDefinitions` b ON a.`attrdef`=b.`id` WHERE a.`content` = " . $this->_id." ORDER BY b.`name`";
					break;
				case $this->_repo->getClassname('folder'):
					$queryStr = "SELECT a.* FROM `tblFolderAttributes` a LEFT JOIN `tblAttributeDefinitions` b ON a.`attrdef`=b.`id` WHERE a.`folder` = " . $this->_id." ORDER BY b.`name`";
					break;
				default:
					return false;
			}
			$resArr = $db->getResultArray($queryStr);
			if (is_bool($resArr) && !$resArr) return false;

			$this->_attributes = array();

			foreach ($resArr as $row) {
				$attrdef = $this->_repo->getAttributeDefinition($row['attrdef']);
				$attr = new mediarepo_Core_Attribute($row["id"], $this, $attrdef, $row["value"]);
				$attr->setREPO($this->_repo);
				$this->_attributes[$attrdef->getId()] = $attr;
			}
		}
		return $this->_attributes;

	} /* }}} */

    /**
     * Returns an attribute of the object for the given attribute definition
     *
     * @param mediarepo_Core_AttributeDefinition $attrdef
     * @return array|string value of attritbute or false. The value is an array
     * if the attribute is defined as multi value
     */
	function getAttribute($attrdef) { /* {{{ */
		if (!$this->_attributes) {
			$this->getAttributes();
		}

		if (isset($this->_attributes[$attrdef->getId()])) {
			return $this->_attributes[$attrdef->getId()];
		} else {
			return false;
		}

	} /* }}} */

	/**
	 * Returns an attribute value of the object for the given attribute definition
	 *
	 * @param mediarepo_Core_AttributeDefinition $attrdef
	 * @return array|string value of attritbute or false. The value is an array
	 * if the attribute is defined as multi value
	 */
	function getAttributeValue($attrdef) { /* {{{ */
		if (!$this->_attributes) {
			$this->getAttributes();
		}

		if (isset($this->_attributes[$attrdef->getId()])) {
			$value =  $this->_attributes[$attrdef->getId()]->getValue();
			if($attrdef->getMultipleValues()) {
				$sep = substr($value, 0, 1);
				$vsep = $attrdef->getValueSetSeparator();
				/* If the value doesn't start with the separator used in the value set,
				 * then assume that the value was not saved with a leading separator.
				 * This can happen, if the value was previously a single value from
				 * the value set and later turned into a multi value attribute.
				 */
				if($sep == $vsep)
					return(explode($sep, substr($value, 1)));
				else
					return(array($value));
			} else {
				return $value;
			}
		} else
			return false;

	} /* }}} */

    /**
     * Returns an attribute value of the object for the given attribute definition
     *
     * This is a short cut for getAttribute($attrdef)->getValueAsArray() but
     * first checks if the object has an attribute for the given attribute
     * definition.
     *
     * @param mediarepo_Core_AttributeDefinition $attrdef
     * @return array|bool
     * even if the attribute is not defined as multi value
     */
	function getAttributeValueAsArray($attrdef) { /* {{{ */
		if (!$this->_attributes) {
			$this->getAttributes();
		}

		if (isset($this->_attributes[$attrdef->getId()])) {
			return $this->_attributes[$attrdef->getId()]->getValueAsArray();
		} else
			return false;

	} /* }}} */

	/**
	 * Returns an attribute value of the object for the given attribute definition
	 *
	 * This is a short cut for getAttribute($attrdef)->getValueAsString() but
	 * first checks if the object has an attribute for the given attribute
	 * definition.
	 *
     * @param mediarepo_Core_AttributeDefinition $attrdef
	 * @return string value of attritbute or false. The value is always a string
	 * even if the attribute is defined as multi value
	 */
	function getAttributeValueAsString($attrdef) { /* {{{ */
		if (!$this->_attributes) {
			$this->getAttributes();
		}

		if (isset($this->_attributes[$attrdef->getId()])) {
			return $this->_attributes[$attrdef->getId()]->getValue();
		} else
			return false;

	} /* }}} */

	/**
	 * Set an attribute of the object for the given attribute definition
	 *
	 * @param mediarepo_Core_AttributeDefinition $attrdef definition of attribute
	 * @param array|string $value value of attribute, for multiple values this
	 * must be an array
	 * @return boolean true if operation was successful, otherwise false
	 */
	function setAttributeValue($attrdef, $value) { /* {{{ */
		$db = $this->_repo->getDB();
		if (!$this->_attributes) {
			$this->getAttributes();
		}
		switch($attrdef->getType()) {
		case mediarepo_Core_AttributeDefinition::type_boolean:
			$value = ($value === true || $value != '' || $value == 1) ? 1 : 0;
			break;
		}
		if($attrdef->getMultipleValues() && is_array($value)) {
			$sep = substr($attrdef->getValueSet(), 0, 1);
			$value = $sep.implode($sep, $value);
		}
		if(!isset($this->_attributes[$attrdef->getId()])) {
			switch(get_class($this)) {
				case $this->_repo->getClassname('document'):
					$tablename = 'tblDocumentAttributes';
					$queryStr = "INSERT INTO `tblDocumentAttributes` (`document`, `attrdef`, `value`) VALUES (".$this->_id.", ".$attrdef->getId().", ".$db->qstr($value).")";
					break;
				case $this->_repo->getClassname('documentcontent'):
					$tablename = 'tblDocumentContentAttributes';
					$queryStr = "INSERT INTO `tblDocumentContentAttributes` (`content`, `attrdef`, `value`) VALUES (".$this->_id.", ".$attrdef->getId().", ".$db->qstr($value).")";
					break;
				case $this->_repo->getClassname('folder'):
					$tablename = 'tblFolderAttributes';
					$queryStr = "INSERT INTO `tblFolderAttributes` (`folder`, `attrdef`, `value`) VALUES (".$this->_id.", ".$attrdef->getId().", ".$db->qstr($value).")";
					break;
				default:
					return false;
			}
			$res = $db->getResult($queryStr);
			if (!$res)
				return false;

			$attr = new mediarepo_Core_Attribute($db->getInsertID($tablename), $this, $attrdef, $value);
			$attr->setREPO($this->_repo);
			$this->_attributes[$attrdef->getId()] = $attr;
			return true;
		}

		$this->_attributes[$attrdef->getId()]->setValue($value);

		return true;
	} /* }}} */

	/**
	 * Remove an attribute of the object for the given attribute definition
	 * @param mediarepo_Core_AttributeDefinition $attrdef
	 * @return boolean true if operation was successful, otherwise false
	 */
	function removeAttribute($attrdef) { /* {{{ */
		$db = $this->_repo->getDB();
		if (!$this->_attributes) {
			$this->getAttributes();
		}
		if(isset($this->_attributes[$attrdef->getId()])) {
			switch(get_class($this)) {
				case $this->_repo->getClassname('document'):
					$queryStr = "DELETE FROM `tblDocumentAttributes` WHERE `document`=".$this->_id." AND `attrdef`=".$attrdef->getId();
					break;
				case $this->_repo->getClassname('documentcontent'):
					$queryStr = "DELETE FROM `tblDocumentContentAttributes` WHERE `content`=".$this->_id." AND `attrdef`=".$attrdef->getId();
					break;
				case $this->_repo->getClassname('folder'):
					$queryStr = "DELETE FROM `tblFolderAttributes` WHERE `folder`=".$this->_id." AND `attrdef`=".$attrdef->getId();
					break;
				default:
					return false;
			}
			$res = $db->getResult($queryStr);
			if (!$res)
				return false;

			unset($this->_attributes[$attrdef->getId()]);
		}
		return true;
	} /* }}} */
} /* }}} */
