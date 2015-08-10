<% include UserFormProgress %>

<form class="userform" $AttributesHTML>

<% if $Message %>
<p id="{$FormName}_error" class="message $MessageType">$Message</p>
<% else %>
<p id="{$FormName}_error" class="message $MessageType" aria-hidden="true" style="display: none;"></p>
<% end_if %>

<fieldset>
	<% if $Legend %><legend>$Legend</legend><% end_if %>

	<% loop $FormSteps %>
	<fieldset class="form-step">
		<% if $DisplayErrorMessagesAtTop %>
			<fieldset class="error-container" aria-hidden="true" style="display: none;">
				<div>
					<h4></h4>
					<ul></ul>
				</div>
			</fieldset>
		<% end_if %>

		<h2>$Title</h2>

		<% loop $Fields %>
		$FieldHolder
		<% end_loop %>

		<% include UserFormStepNav ContainingPage=$Top %>
	</fieldset>
	<% end_loop %>

	<div class="clear"><!-- --></div>
</fieldset>

<% if $Actions %>
<div class="Actions">
	<% loop $Actions %>
	$Field
	<% end_loop %>
</div>
<% end_if %>

</form>
