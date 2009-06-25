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
			<a class="moreOptions" href="#" title="<% _t('SHOWOPTIONS', 'Show Options') %>"><% _t('SHOWOPTIONS','Show Options') %></a>
		<% end_if %>
		
		<% if canDelete %>
			<a class="delete" href="#" title="<% _t('DELETE', 'Delete') %>"><% _t('DELETE', 'Delete') %></a>
		<% end_if %> 	
	</div>
	
	<% if showExtraOptions %>
		<div class="extraOptions hidden" id="$Name.Attr-extraOptions">
			<% if HasAddableOptions %>
				<fieldset class="fieldOptionsGroup">
					<legend><% _t('OPTIONS', 'Options') %></legend>
					<ul class="editableOptions" id="$FieldName.Attr-list">

						<% if canEdit %>
							<% control Options %>
								$EditSegment
							<% end_control %>
							<% if HasAddableOptions %>
								<li class="{$ClassName}Option">
									<a href="#" rel="$ID" class="addableOption" title="<% _t('ADD', 'Add option to field') %>">
										Add Option
									</a>
								</li>
							<% end_if %>
						<% else %>
							<% control Options %>
								$ReadonlyOption
							<% end_control %>
						<% end_if %>
					</ul>
				</fieldset>
			<% end_if %>
			
			<% if FieldConfiguration %>
				<fieldset class="fieldOptionsGroup">
					<legend><% _t('FIELDCONFIGURATION', 'Field Configuration') %></legend>
					<% control FieldConfiguration %>
						$FieldHolder
					<% end_control %>
				</fieldset>
			<% end_if %>
			
			<% if FieldValidationOptions %>
				<fieldset class="fieldOptionsGroup">
					<legend><% _t('VALIDATION', 'Validation') %></legend>
					<% control FieldValidationOptions %>
						$FieldHolder
					<% end_control %>
				</fieldset>
			<% end_if %>
		
			<fieldset class="customRules fieldOptionsGroup">
				<legend>Custom Rules</legend>
				<ul id="{$FieldName}-customRules">
					<li>
						<a href="#" class="addCondition" title="<% _t('ADD', 'Add') %>">
							Add Rule
						</a>
					</li>
					<li class="addCustomRule">
						<select name="{$FieldName}[CustomSettings][ShowOnLoad]">
							<option value="Show" <% if ShowOnLoad %>selected="selected"<% end_if %>><% _t('SHOW', 'Show') %></option>
							<option value="Hide" <% if ShowOnLoad %><% else %>selected="selected"<% end_if %>><% _t('HIDE', 'Hide') %></option>
						</select>

						<label class="left">Field On Default</label>
					</li>
					<li class="hidden">
						<select class="displayOption customRuleField" name="{$FieldName}[CustomRules][Display]">
							<option value="Show"><% _t('SHOWTHISFIELD', 'Show This Field') %></option>
							<option value="Hide"><% _t('HIDETHISFIELD', 'Hide This Field') %></option>
						</select>

						<label><% _t('WHEN', 'When') %></label>
						<select class="fieldOption customRuleField" name="{$FieldName}[CustomRules][ConditionField]">
				
						</select>

						<label><% _t('IS', 'Is') %></label>
						<select class="conditionOption customRuleField" name="{$FieldName}[CustomRules][ConditionOption]">
							<option value=""></option>
							<option value="IsBlank"><% _t('BLANK', 'Blank') %></option>
							<option value="IsNotBlank"><% _t('NOTBLANK', 'Not Blank') %></option>
							<option value="HasValue"><% _t('VALUE', 'Value') %></option>
							<option value="ValueNot"><% _t('NOTVALUE', 'Not Value') %></option>
						</select>

						<input type="text" class="ruleValue hidden customRuleField" name="{$FieldName}[CustomRules][Value]" />

						<a href="#" class="deleteCondition" title="<% _t('DELETE', 'Delete') %>"><img src="cms/images/delete.gif" alt="<% _t('DELETE', 'Delete') %>" /></a>
					</li>
					<% control CustomRules %>
						<li>
							<% include CustomRule %>
						</li>
					<% end_control %>
				</ul>
			</fieldset>
		</div>
	<% end_if %>
	
	<!-- Hidden option Fields -->
  	<input type="hidden" class="canDeleteHidden" name="{$FieldName}[CanDelete]" value="$CanDelete" />
  	<input type="hidden" class="typeHidden" name="{$FieldName}[Type]" value="$ClassName" />   
		<input type="hidden" class="sortHidden" name="{$FieldName}[Sort]" value="$Sort" />
</li>