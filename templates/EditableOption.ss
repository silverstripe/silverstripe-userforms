<li>
	<img class="handle" src="$ModulePath(framework)/images/drag.gif" alt="<% _t('EditableOption.DRAG', 'Drag to rearrange order of options') %>" />
	<label class="option-label" for="$FieldName-Title"><% _t('EditableOption.Title', 'Label') %></label>
	<input type="text" id="$FieldName-Title" name="{$FieldName}[Title]" value="$Title" />
	<label class="option-label" for="$FieldName-Value"><% _t('EditableOption.Value', 'Value') %></label>
	<input type="text" id="$FieldName-Value" name="{$FieldName}[Value]" value="$Value"
	       placeholder="(<% _t('EditableOption.optional', 'optional') %>)" />
	<input type="hidden" class="sortOptionHidden hidden" name="{$FieldName}[Sort]" value="$Sort" />
	
	<% if canEdit %>
		<a href="$ID" class="deleteOption"><img src="$ModulePath(framework)/images/delete.gif" alt="<% _t('EditableOption.DELETE', 'Remove this option') %>" /></a>
	<% else %>
		<img src="cms/images/locked.gif" alt="<% _t('EditableOption.LOCKED', 'These fields cannot be modified') %>" />	
	<% end_if %>
</li>