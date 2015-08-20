<form $AttributesHTML>
	
<% include UserFormProgress %>
<% include UserFormStepErrors %>

<% if $Message %>
	<p id="{$FormName}_error" class="message $MessageType">$Message</p>
<% else %>
	<p id="{$FormName}_error" class="message $MessageType" aria-hidden="true" style="display: none;"></p>
<% end_if %>

<fieldset>
	<% if $Legend %><legend>$Legend</legend><% end_if %>
	<% loop $Fields %>
		$FieldHolder
	<% end_loop %>
	<div class="clear"><!-- --></div>
</fieldset>

<% include UserFormStepNav %>

</form>
