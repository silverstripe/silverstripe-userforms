<div class="FieldEditor <% if isReadonly %>readonly<% end_if %>" id="Fields" name="$Name.Attr">
	<ul class="TopMenu Menu">
		<li><% _t('ADD', 'Add') %>:</li>
		
		<% control CreatableFields %>
			<li><a href="#" title="<% _t('ADD', 'Add') %> $Title" id="$ClassName">$Title</a></li>
		<% end_control %>
	</ul>
	<div class="FieldList" id="Fields_fields">
	<% control Fields %>
		<% if isReadonly %>
			$ReadonlyEditSegment	
		<% else %>
			$EditSegment
		<% end_if %>
	<% end_control %>
	</div>
	<ul class="BottomMenu Menu">
		<li><% _t('ADD', 'Add') %>:</li>
		<% control CreatableFields %>
			<li><a href="#" title="<% _t('ADD', 'Add') %> $Title" id="$ClassName">$Title</a></li>
		<% end_control %>
	</ul>
	<div class="FormOptions">
		<% control FormOptions %>
			$FieldHolder
		<% end_control %>
	</div>
</div>