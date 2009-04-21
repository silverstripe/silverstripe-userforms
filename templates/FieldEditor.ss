<div class="FieldEditor <% if isReadonly %>readonly<% end_if %>" id="Fields" name="$Name.Attr">
	<div class="MenuHolder">
		<ul class="TopMenu Menu">
			<li class="addField"><% _t('ADD', 'Add') %>:</li>
			<% control CreatableFields %>
				<li><a href="#" title="<% _t('ADD', 'Add') %> $Title" id="$ClassName">$Title</a></li>
			<% end_control %>
		</ul>
	</div>
	<div class="FieldListHold">
		<ul class="FieldList" id="Fields_fields">
			<% control Fields %>
				<% if isReadonly %>
					$ReadonlyEditSegment	
				<% else %>
					$EditSegment
				<% end_if %>
			<% end_control %>
		</ul>
	</div>
	<div class="MenuHolder">
		<ul class="TopMenu Menu">
			<li class="addField"><% _t('ADD', 'Add') %>:</li>
			<% control CreatableFields %>
				<li><a href="#" title="<% _t('ADD', 'Add') %> $Title" id="$ClassName">$Title</a></li>
			<% end_control %>
		</ul>
	</div>

	<div class="FormOptions">
		<h3>Form Options</h3>
		<% control FormOptions %>
			$FieldHolder
		<% end_control %>
	</div>
</div>