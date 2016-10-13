<fieldset id="$Name" class="form-step $extraClass" data-title="$Title">
	<% if $Form.DisplayErrorMessagesAtTop %>
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

</fieldset>
