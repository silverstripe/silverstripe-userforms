<% include UserFormProgress %>

<form class="userform" $AttributesHTML>

<% if $Message %>
<p id="{$FormName}_error" class="message $MessageType">$Message</p>
<% else %>
<p id="{$FormName}_error" class="message $MessageType" aria-hidden="true" style="display: none;"></p>
<% end_if %>

<fieldset>
	<% if $Legend %><legend>$Legend</legend><% end_if %>

	<% if $FormFields%><% loop $FormFields %>
		<fieldset class="form-step" data-title="$Title">
			<% if $Top.DisplayErrorMessagesAtTop %>
				<fieldset class="error-container" aria-hidden="true" style="display: none;">
					<div>
						<h4></h4>
						<ul class="error-list"></ul>
					</div>
				</fieldset>
			<% end_if %>

		<% loop $Children %>
		$FieldHolder
		<% end_loop %>

			<% include UserFormStepNav ContainingPage=$Top %>
		</fieldset>
	<% end_loop %><% end_if %>

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
