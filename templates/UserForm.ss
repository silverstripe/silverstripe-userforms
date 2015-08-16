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

<%--
	Include step navigation if it's a multi-page form.
	The markup inside this include is hidden by default and displayed if JavaScript is enabled.
--%>

<% if $Steps.Count > 1 %>
<% include UserFormStepNav %>
<% end_if %>

<%--
	When JavaScript is disabled, multi-page forms are diaplayed as a single page form,
	and these actions are used instead of the step navigation include.

	These actions are hidden by JavaScript on multi-page forms.
--%>

<% if $Actions %>
<div class="Actions">
	<% loop $Actions %>
	$Field
	<% end_loop %>
</div>
<% end_if %>

</form>
