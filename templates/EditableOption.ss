<li>
	<img class="handle" src="$ModulePath(framework)/images/drag.gif" alt="<% _t('EditableOption.DRAG', 'Drag to rearrange order of options') %>" />
	<input type="text" name="{$FieldName}[Title]" value="$Title" />
	<input type="hidden" class="sortOptionHidden hidden" name="{$FieldName}[Sort]" value="$Sort" />
	
	<% if canEdit %>
		<a href="$ID" class="deleteOption"><img src="$ModulePath(framework)/images/delete.gif" alt="<% _t('EditableOption.DELETE', 'Remove this option') %>" /></a>
	<% else %>
		<img src="cms/images/locked.gif" alt="<% _t('EditableOption.LOCKED', 'These fields cannot be modified') %>" />	
	<% end_if %>
</li>