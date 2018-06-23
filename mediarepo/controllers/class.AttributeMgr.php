<?php
/**
 * Implementation of Attribute Definition manager controller
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2013 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Class which does the busines logic for attribute definition manager
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2013 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_Controller_AttributeMgr extends mediarepo_Controller_Common {

	public function run() { /* {{{ */
	} /* }}} */

	public function addattrdef() { /* {{{ */
		$repo = $this->params['repo'];
		$name = $this->params['name'];
		$type = $this->params['type'];
		$objtype = $this->params['objtype'];
		$multiple = $this->params['multiple'];
		$minvalues = $this->params['minvalues'];
		$maxvalues = $this->params['maxvalues'];
		$valueset = $this->params['valueset'];
		$regex = $this->params['regex'];

		return($repo->addAttributeDefinition($name, $objtype, $type, $multiple, $minvalues, $maxvalues, $valueset, $regex));
	} /* }}} */

	public function removeattrdef() { /* {{{ */
		$attrdef = $this->params['attrdef'];
		return $attrdef->remove();
	} /* }}} */

	public function editattrdef() { /* {{{ */
		$repo = $this->params['repo'];
		$name = $this->params['name'];
		$attrdef = $this->params['attrdef'];
		$type = $this->params['type'];
		$objtype = $this->params['objtype'];
		$multiple = $this->params['multiple'];
		$minvalues = $this->params['minvalues'];
		$maxvalues = $this->params['maxvalues'];
		$valueset = $this->params['valueset'];
		$regex = $this->params['regex'];

		if (!$attrdef->setName($name)) {
			return false;
		}
		if (!$attrdef->setType($type)) {
			return false;
		}
		if (!$attrdef->setObjType($objtype)) {
			return false;
		}
		if (!$attrdef->setMultipleValues($multiple)) {
			return false;
		}
		if (!$attrdef->setMinValues($minvalues)) {
			return false;
		}
		if (!$attrdef->setMaxValues($maxvalues)) {
			return false;
		}
		if (!$attrdef->setValueSet($valueset)) {
			return false;
		}
		if (!$attrdef->setRegex($regex)) {
			return false;
		}

		return true;
	} /* }}} */

	public function removeattrvalue() { /* {{{ */
		$attrdef = $this->params['attrdef'];
		$attrval = $this->params['attrval'];
		//$attrdef->getObjects($attrval);
		return $attrdef->removeValue($attrval);
	} /* }}} */
}

