<% require css(userforms/css/FieldEditor.css) %>
<% require javascript(userforms/javascript/UserForm.js) %>

<div class="FieldEditor <% if canEdit %><% else %>readonly<% end_if %>" id="Fields" $AttributesHTML>
	
	<div class="FieldListHold">
		<ul class="FieldList" id="Fields_fields">
			<% loop Fields %>
				$EditSegment
			<% end_loop %>
		</ul>
	</div>
	 
	<% if canEdit %>
	<div class="MenuHolder no-change-track">
		<h2><% _t('FieldEditor.ADD', 'Add') %></h2>

		<select name="AddUserFormField" id="AddUserFormField">
			<option value=""><% _t('FieldEditor.SELECTAFIELD', 'Select a Field') %></option>

			<% loop CreatableFields %>
				<option value="$ClassName">$Title</option>
			<% end_loop %>
		</select>

		<input type="hidden" name="SecurityID" value="$SecurityID" />
		<input type="submit" class="action" value="<% _t('FieldEditor.ADD', 'Add') %>" /> 
	</div>
	<% end_if %>

</div>
