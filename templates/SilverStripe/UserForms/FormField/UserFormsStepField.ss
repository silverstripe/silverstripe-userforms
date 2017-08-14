<div id="$Name" class="form-step $extraClass" data-title="$Title">
	<% if $Form.DisplayErrorMessagesAtTop %>
		<div class="error-container" aria-hidden="true" style="display: none;">
			<div>
				<h4></h4>
				<ul class="error-list"></ul>
			</div>
		</div>
	<% end_if %>

	<% loop $Children %>
		$FieldHolder
	<% end_loop %>

</div>
