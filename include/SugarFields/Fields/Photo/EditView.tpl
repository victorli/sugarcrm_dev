{***
created by ecartx at 20160621
***}

<input type="hidden" id="old_{{sugarvar key='name'}}" name="old_{{sugarvar key='name'}}" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}" />
<input id="{{sugarvar key='name'}}" name="{{sugarvar key='name'}}" type="file" title="{{$vardef.help}}" size="{{$displayParams.size|default:30}}" {{if !empty($vardef.len)}} maxlength="{{$vardef.len}}"{{elseif !empty($displayParams.maxlength)}} maxlength="{{$displayParams.maxlength}}" {{else}} maxlength="255" {{/if}} value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}" {{$displayParams.field}}>
{if !empty({{sugarvar key='value' string=true}})}
  <img src="{{sugarvar key='value'}}" height='100' width='100'>
{/if}
