<?php
/**
 * Implementation of RemoveFolder controller
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
 * Class which does the busines logic for downloading a document
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2013 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_Controller_RemoveFolder extends mediarepo_Controller_Common {

	public function run() {
		$repo = $this->params['repo'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$folder = $this->params['folder'];
		$index = $this->params['index'];
		$indexconf = $this->params['indexconf'];

		/* Get the document id and name before removing the document */
		$foldername = $folder->getName();
		$folderid = $folder->getID();

		if(false === $this->callHook('preRemoveFolder')) {
			if(empty($this->errormsg))
				$this->errormsg = 'hook_preRemoveFolder_failed';
			return null;
		}

		$result = $this->callHook('removeFolder', $folder);
		if($result === null) {
			/* Register a callback which removes each document from the fulltext index
			 * The callback must return true other the removal will be canceled.
			 */
			function removeFromIndex($arr, $document) {
				$index = $arr[0];
				$indexconf = $arr[1];
				$lucenesearch = new $indexconf['Search']($index);
				if($hit = $lucenesearch->getDocument($document->getID())) {
					$index->delete($hit->id);
					$index->commit();
				}
				return true;
			}
			if($index)
				$repo->setCallback('onPreRemoveDocument', 'removeFromIndex', array($index, $indexconf));

			if (!$folder->remove()) {
				$this->errormsg = 'error_occured';
				return false;
			} else {

				if(!$this->callHook('postRemoveFolder')) {
				}

			}
		} else
			return $result;

		return true;
	}
}
