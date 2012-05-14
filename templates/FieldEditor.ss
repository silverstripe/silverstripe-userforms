<% require css(userforms/css/FieldEditor.css) %>
<% require javascript(userforms/javascript/UserForm.js) %>

<div class="FieldEditor <% if canEdit %><% else %>readonly<% end_if %>" id="Fields" $AttributesHTML>
	
	<div class="FieldListHold">
		<ul class="FieldList" id="Fields_fields">
			<% control Fields %>
				$EditSegment
			<% end_control %>
		</ul>
	</div>
	 
	<% if canEdit %>
	<div class="MenuHolder">
		<h2><% _t('ADD', 'Add') %></h2>

		<select name="AddUserFormField" id="AddUserFormField">
			<option value=""><% _t('SELECTAFIELD', 'Select a Field') %></option>

			<% control CreatableFields %>
				<option value="$ClassName">$Title</option>
			<% end_control %>
		</select>

		<input type="submit" class="action" value="<% _t('ADD', 'Add') %>" /> 
	</div>
	<% end_if %>

</div>