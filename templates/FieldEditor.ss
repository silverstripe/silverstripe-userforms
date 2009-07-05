<div class="FieldEditor <% if isReadonly %>readonly<% end_if %>" id="Fields">
	
	<div class="FieldListHold">
		<ul class="FieldList" id="Fields_fields">
			<% control Fields %>
				$EditSegment
			<% end_control %>
		</ul>
	</div>

	<% include AddField %>
	
	<div class="FormOptions">
		<h3>Form Options</h3>
		<% control FormOptions %>
			$FieldHolder
		<% end_control %>
	</div>
</div>