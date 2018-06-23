<?php
/**
 * Implementation of ExtensionMgr controller
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2018 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Class which does the busines logic for managing extensions
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2018 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_Controller_ExtensionMgr extends mediarepo_Controller_Common {

	public function refresh() { /* {{{ */
		$repo = $this->params['repo'];
		$extmgr = $this->params['extmgr'];

		$extmgr->createExtensionConf();
		return true;
	} /* }}} */

	public function download() { /* {{{ */
		$repo = $this->params['repo'];
		$settings = $this->params['settings'];
		$extmgr = $this->params['extmgr'];
		$extname = $this->params['extname'];

		$filename = $extmgr->createArchive($extname, $GLOBALS['EXT_CONF'][$extname]['version']);

		if(null === $this->callHook('download')) {
			if(file_exists($filename)) {
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: " . filesize($filename));
				header("Content-Disposition: attachment; filename=\"" . utf8_basename($filename) . "\"; filename*=UTF-8''".utf8_basename($filename));
				header("Content-Type: application/zip");
				header("Cache-Control: must-revalidate");

				sendFile($filename);
			}
		}
		return true;
	} /* }}} */

	public function upload() { /* {{{ */
		$repo = $this->params['repo'];
		$extmgr = $this->params['extmgr'];
		$file = $this->params['file'];

		if($extmgr->updateExtension($file))
			$extmgr->createExtensionConf();
		else
			return false;
		return true;
	} /* }}} */

	public function getlist() { /* {{{ */
		$repo = $this->params['repo'];
		$extmgr = $this->params['extmgr'];
		$forceupdate = $this->params['forceupdate'];
		$version = $this->params['version'];

		if(!$extmgr->updateExtensionList($version, $forceupdate)) {
			$this->errormsg = $extmgr->getErrorMsg();
			return false;
		}

		return true;
	} /* }}} */

}
