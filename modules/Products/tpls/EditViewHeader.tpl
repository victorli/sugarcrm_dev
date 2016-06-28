<!-- Begin Tabs -->
{literal}
<script type="text/javascript">
$(function(){
	$('#tabs').tabs();
});
function submitForm(){
	$('form#EditView').submit();
}

function cancelForm(){
	$('input[name=action]').value = 'DetailView';
	$('form#EditView').submit();
}
{/literal}
</script>
<form method="post" name="EditView" id="EditView" action="index.php">
	<input type="hidden" name="module" value="Products">
	<input type="hidden" name="record" id="record" value="{$ID}">
	<input type="hidden" name="action">
	<input type="hidden" name="return_module" value="{$RETURN_MODULE}">
	<input type="hidden" name="return_id" value="{$RETURN_ID}">
	<input type="hidden" name="return_action" value="{$RETURN_ACTION}">
		
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
    <tr>
        <td>
            <input type="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="submitForm()">
            <input type="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" onclick="cancelForm()">
        </td>
        <td align="right" nowrap>
            <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span> {$APP.NTC_REQUIRED}
        </td>
    </tr>
</table>
<div id="tabs">
	<ul>
		<li><a href="#info">{$MOD.LBL_TAB_INFORMATION}</a></li>
		<li><a href="#catalog">{$MOD.LBL_TAB_CATALOG}</a></li>
		<li><a href="#feature">{$MOD.LBL_TAB_FEATURE}</a></li>
	</ul>
	<div id="#info" style="overflow:hidden;">