<li>
	<img class="handle" src="sapphire/images/drag.gif" alt="<% _t('DRAG', 'Drag to rearrange order of options') %>" />
	<input type="text" name="{$FieldName}[Title]" value="$Title" />
	<input type="hidden" class="sortOptionHidden hidden" name="{$FieldName}[Sort]" value="$Sort" />
	
	<% if isReadonly %>
		<img src="cms/images/locked.gif" alt="<% _t('LOCKED', 'These fields cannot be modified') %>" />	
	<% else %>
		<a href="$ID" class="deleteOption"><img src="cms/images/delete.gif" alt="<% _t('DELETE', 'Remove this option') %>" /></a>	
	<% end_if %>
</li>