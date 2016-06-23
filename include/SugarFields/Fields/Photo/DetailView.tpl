{***
created by eCartx at 20160621
***}

{if !empty({{sugarvar key='value' string=true}})}
	<img src='{{sugarvar key='value'}}' height='{{$displayParams.height|default:100}}' width='{{$displayParams.width|default:100}}'>
{/if}}
