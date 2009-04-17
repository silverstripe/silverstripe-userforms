<div class="$ClassName EditableFormField" id="$Name.Attr">
	<div class="FieldInfo">
		<% if isReadonly %>
			<img class="handle" src="sapphire/images/drag_readonly.gif" alt="<% _t('LOCKED', 'These fields cannot be modified') %>" />
		<% else %>
			<img class="handle" src="sapphire/images/drag.gif" alt="<% _t('DRAG', 'Drag to rearrange order of fields') %>" />
		<% end_if %>
		
		<img class="icon" src="userforms/images/{$ClassName.LowerCase}.png" alt="$ClassName" title="$singular_name" />
		
		$TitleField
		
		<% if showExtraOptions %>
			<a class="moreOptions" href="#" title="<% _t('MORE', 'More options') %>">
				<img src="cms/images/edit.gif" alt="<% _t('MORE', 'More options') %>" />
			</a>
		<% end_if %>
		
		<% if isReadonly %>
			<img src="cms/images/locked.gif" alt="<% _t('LOCKED', 'These fields cannot be modified') %>" />
		<% else %>
			<% if CanDelete %>
    			<a class="delete" href="#" title="<% _t('DELETE', 'Delete this field') %>"><img src="cms/images/delete.gif" alt="<% _t('DELETE', 'Delete this field') %>" /></a>
	  		<% else %>
    			<img src="cms/images/locked.gif" alt="<% _t('REQUIRED', 'This field is required for this form and cannot be deleted') %>" />
    		<% end_if %>
    	<% end_if %>
  	</div>
	
	<% if showExtraOptions %>
		<div class="extraOptions hidden" id="$Name.Attr-extraOptions">
			<ul class="editableOptions" id="$Name.Attr-list">

				<% if isReadonly %>
					<% control Options %>
						$ReadonlyOption
					<% end_control %>			
				<% else %>
					<% control Options %>
						$EditSegment
					<% end_control %>
					<% if hasAddableOptions %>
						<li class="{$ClassName}Option">
							<input class="text" type="text" name="$Name.Attr[NewOption]" value="" />
							<a href="#" rel="$ID" class="addableOption" title="<% _t('ADD', 'Add option to field') %>"><img src="cms/images/add.gif" alt="<% _t('ADD', 'Add new option') %>" /></a>
						</li>
					<% end_if %>
				<% end_if %>
			</ul>

			<% control ExtraOptions %>
				$FieldHolder
			<% end_control %>
		</div>
	<% end_if %>
	
  	<input type="hidden" name="$Name.Attr[CanDelete]" value="$CanDelete" />
  	<input type="hidden" name="$Name.Attr[CustomParameter]" value="$CustomParameter" />
  	<input type="hidden" name="$Name.Attr[Type]" value="$ClassName" />   
	<input type="hidden" name="$Name.Attr[Sort]" value="-1" />
</div>