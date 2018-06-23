<?php
/**
 * Implementation of Download controller
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
class mediarepo_Controller_Download extends mediarepo_Controller_Common {

	public function run() {
		$repo = $this->params['repo'];
		$type = $this->params['type'];

		switch($type) {
			case "version":
				$content = $this->params['content'];
				if(null === $this->callHook('version')) {
					if(file_exists($repo->contentDir . $content->getPath())) {
						header("Content-Transfer-Encoding: binary");
						header("Content-Length: " . filesize($repo->contentDir . $content->getPath() ));
						$efilename = rawurlencode($content->getOriginalFileName());
						header("Content-Disposition: attachment; filename=\"" . $efilename . "\"; filename*=UTF-8''".$efilename);
						header("Content-Type: " . $content->getMimeType());
						header("Cache-Control: must-revalidate");

						sendFile($repo->contentDir.$content->getPath());
					}
				}
				break;
		}
	}
}
