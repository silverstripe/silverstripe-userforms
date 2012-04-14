<li>
	<img class="handle" src="sapphire/images/drag.gif" alt="<% _t('DRAG', 'Drag to rearrange order of options') %>" />
	
	<label for="{$FieldName}[Title]"><% _t('EditableOption.Title', 'Title') %></label> $TitleField
	<label for="{$FieldName}[Value]"><% _t('EditableOption.Value', 'Value') %></label> $ValueField
	<input type="hidden" class="sortOptionHidden hidden" name="{$FieldName}[Sort]" value="$Sort" />
	
	<% if canEdit %>
		<a href="$ID" class="deleteOption"><img src="cms/images/delete.gif" alt="<% _t('DELETE', 'Remove this option') %>" /></a>	
	<% else %>
		<img src="cms/images/locked.gif" alt="<% _t('LOCKED', 'These fields cannot be modified') %>" />	
	<% end_if %>
</li>