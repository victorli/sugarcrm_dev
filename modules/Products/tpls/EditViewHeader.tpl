<!-- Begin Tabs -->
{literal}
<script type="text/javascript">
$(function(){
	$('#tabs').tabs();
});
{/literal}
</script>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
    <tr>
        <td>
            <input type="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
            <input type="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
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
	<div id="#info">