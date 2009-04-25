<!-- JS Relys on EditableFormField as a class - and the 3 ids in this order - do not change -->
<li class="$ClassName EditableFormField" id="$Name.Attr EditableItem_$Pos $BaseName">
	<div class="fieldInfo">
		<% if isReadonly %>
			<img class="fieldHandler" src="sapphire/images/drag_readonly.gif" alt="<% _t('LOCKED', 'These fields cannot be modified') %>" />
		<% else %>
			<img class="fieldHandler" src="sapphire/images/drag.gif" alt="<% _t('DRAG', 'Drag to rearrange order of fields') %>" />
		<% end_if %>
	
		<img class="icon" src="$Icon" alt="$ClassName" title="$singular_name" />
	
		$TitleField
	</div>
	
	<div class="fieldActions">
		<% if showExtraOptions %>
			<a class="moreOptions" href="#" title="<% _t('MOREOPTIONS', 'More Options') %>"><% _t('MOREOPTIONS','More Options') %></a>
		<% end_if %>
		
		<% if CanDelete %>
   			<a class="delete" href="#" title="<% _t('DELETE', 'Delete') %>">
				<% _t('DELETE', 'Delete') %>
			</a>
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
			
			<div class="customRules">
				<h4>Custom Rules</h4>
				<select name="$Name.Attr[ShowOnLoad]">
					<option value="Show" <% if ShowOnLoad %>selected="selected"<% end_if %>><% _t('SHOW', 'Show') %></option>
					<option value="Hide" <% if ShowOnLoad %><% else %><% if Title %><% else %>selected="selected"<% end_if %><% end_if %>><% _t('HIDE', 'Hide') %></option>
				</select>
				<label class="left">Field On Default</label>
				
				<ul id="$Name.Attr-customRules">
					<% control CustomRules %>
						<li class="customRule">
							<% include CustomRule %>
						</li>
					<% end_control %>
				</ul>
			</div>
		</div>
	<% end_if %>
	
	<!-- Hidden option Fields -->
  	<input type="hidden" class="canDeleteHidden" name="$Name.Attr[CanDelete]" value="$CanDelete" />
  	<input type="hidden" class="customParameterHidden" name="$Name.Attr[CustomParameter]" value="$CustomParameter" />
  	<input type="hidden" class="typeHidden" name="$Name.Attr[Type]" value="$ClassName" />   
	<input type="hidden" class="sortHidden" name="$Name.Attr[Sort]" value="$Sort" />
</li>