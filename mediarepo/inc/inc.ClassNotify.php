<?php
/**
 * Abstract class of notifation system
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Abstract class of notification systems
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010 Uwe Steinmann
 * @version    Release: @package_version@
 */
abstract class mediarepo_Notify {
	abstract function toIndividual($sender, $recipient, $subject, $message, $params=array());
	abstract function toGroup($sender, $groupRecipient, $subject, $message, $params=array());
	abstract function toList($sender, $recipients, $subject, $message, $params=array());
}
?>
