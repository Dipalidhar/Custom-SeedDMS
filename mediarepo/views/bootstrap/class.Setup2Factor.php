<?php
/**
 * Implementation of Setup2Factor view
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2016 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Include classes for 2-factor authentication
 */
require "vendor/autoload.php";

/**
 * Class which outputs the html page for ForcePasswordChange view
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2016 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_View_Setup2Factor extends mediarepo_Bootstrap_Style {

	function js() { /* {{{ */
		header('Content-Type: application/javascript');
?>
function checkForm()
{
	msg = new Array();

	if($("#currentpwd").val() == "") msg.push("<?php printMLText("js_no_pwd");?>");
	if($("#pwd").val() == "") msg.push("<?php printMLText("js_no_pwd");?>");
	if($("#pwd").val() != $("#pwdconf").val()) msg.push("<?php printMLText("js_pwd_not_conf");?>");
	if (msg != "") {
  	noty({
  		text: msg.join('<br />'),
  		type: 'error',
      dismissQueue: true,
  		layout: 'topRight',
  		theme: 'defaultTheme',
			_timeout: 1500,
  	});
		return false;
	}
	else
		return true;
}

$(document).ready( function() {
	$('body').on('submit', '#form', function(ev){
		if(checkForm()) return;
		ev.preventDefault();
	});
});
<?php
	} /* }}} */

	function show() { /* {{{ */
		$repo = $this->params['repo'];
		$user = $this->params['user'];
		$sitename = $this->params['sitename'];

		$this->htmlStartPage(getMLText("2_factor_auth"), "forcepasswordchange");
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("my_account"), "my_account");
		$this->contentHeading(getMLText('2_factor_auth'));
		echo "<div class=\"alert\">".getMLText('2_factor_auth_info')."</div>";
		echo '<div class="row-fluid">';
		$this->contentContainerStart('span6');

		$tfa = new \RobThree\Auth\TwoFactorAuth('MediaREPO');
		$oldsecret = $user->getSecret();
		$secret = $tfa->createSecret();
?>
<form class="form-horizontal" action="../op/op.Setup2Factor.php" method="post" id="form" name="form1">
		<div class="control-group"><label class="control-label"><?php printMLText('2_fact_auth_secret'); ?></label><div class="controls">
		<input id="secret" class="secret" type="text" name="secret" size="30" value="<?php echo $secret; ?>"><br />
		</div></div>
		<div class="control-group"><label class="control-label"></label><div class="controls">
		<img src="<?php echo $tfa->getQRCodeImageAsDataUri($sitename, $secret); ?>">
		</div></div>
		<div class="control-group"><label class="control-label"></label><div class="controls">
		<input class="btn" type="submit" value="<?php printMLText("submit_2_fact_auth") ?>"><br />
		</div></div>
</form>
<?php
		if($oldsecret) {
			$this->contentContainerEnd();
			$this->contentContainerStart('span6');
			echo '<div>'.$oldsecret.'</div>';
			echo '<div><img src="'.$tfa->getQRCodeImageAsDataUri($sitename, $oldsecret).'"></div>';
?>
<?php
		}

		$this->contentContainerEnd();
		echo '</div>';
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
