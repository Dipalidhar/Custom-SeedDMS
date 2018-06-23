<?php
/**
 * Implementation of TransferDocument controller
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2017 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Class which does the busines logic for downloading a document
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2017 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_Controller_TransferDocument extends mediarepo_Controller_Common {

	public function run() {
		$repo = $this->params['repo'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$document = $this->params['document'];
		$newuser = $this->params['newuser'];

		$folder = $document->getFolder();

		if(false === $this->callHook('preTransferDocument')) {
			if(empty($this->errormsg))
				$this->errormsg = 'hook_preTransferDocument_failed';
			return null;
		}

		$result = $this->callHook('transferDocument', $document);
		if($result === null) {
			if (!$document->transferToUser($newuser)) {
				return false;
			} else {
				if(!$this->callHook('postTransferDocument')) {
				}
			}
		}

		return true;
	}
}

