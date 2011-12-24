<?php

 /**
 * smarty_function_sugar_email_btn
 * This is the constructor for the Smarty plugin.
 * This function exists so that the proper email button based on user prefs is loaded into the quotes module.
 * 
 * @param $params The runtime Smarty key/value arguments
 * @param $smarty The reference to the Smarty object used in this invocation
 */
function smarty_function_sugar_email_btn($params, &$smarty)
{
	global $app_strings, $current_user;
	$pdfButtons = '';
	$client = $current_user->getPreference('email_link_type');
	if ($client != 'sugar') {
		$pdfButtons = '<input title="'. $app_strings["LBL_EMAIL_COMPOSE"] . '" accessKey="'. $app_strings["LBL_EMAIL_PDF_BUTTON_KEY"] . '" class="button" type="submit" name="button" value="'. $app_strings["LBL_EMAIL_COMPOSE"] . '" onclick="location.href=\'mailto:\';return false;"> ';
	} else {
		$pdfButtons = '<input title="'. $app_strings["LBL_EMAIL_PDF_BUTTON_TITLE"] . '" accessKey="'. $app_strings["LBL_EMAIL_PDF_BUTTON_KEY"] . '" class="button" type="submit" name="button" value="'. $app_strings["LBL_EMAIL_PDF_BUTTON_LABEL"] . '" onclick="this.form.email_action.value=\'EmailLayout\';"> ';
	}
	return $pdfButtons;
}
?>
