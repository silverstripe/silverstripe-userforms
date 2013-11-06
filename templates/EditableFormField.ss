<!-- JS Relys on EditableFormField as a class - and the 3 ids in this order - do not change -->
<li class="$ClassName EditableFormField" id="$Name.ATT EditableItem_$Pos $Name">
	<div class="fieldInfo">
		<% if canEdit %>
			<img class="fieldHandler" src="$ModulePath(framework)/images/drag.gif" alt="<% _t('EditableFormField.DRAG', 'Drag to rearrange order of fields') %>" />
		<% else %>
			<img class="fieldHandler" src="$ModulePath(framework)/images/drag_readonly.gif" alt="<% _t('EditableFormField.LOCKED', 'These fields cannot be modified') %>" />
		<% end_if %>
	
		<img class="icon" src="$Icon" alt="$ClassName" title="$singular_name" />
	
		$TitleField
	</div>
	
	<div class="fieldActions">
		<% if showExtraOptions %>
			<a class="moreOptions" href="#" title="<% _t('EditableFormField.SHOWOPTIONS', 'Show Options') %>"><% _t('EditableFormField.SHOWOPTIONS','Show Options') %></a>
		<% end_if %>
		
		<% if canDelete %>
			<a class="delete" href="#" title="<% _t('EditableFormField.DELETE', 'Delete') %>"><% _t('EditableFormField.DELETE', 'Delete') %></a>
		<% end_if %> 	
	</div>
	
	<% if showExtraOptions %>
		<div class="extraOptions hidden" id="$Name.ATT-extraOptions">
			<% if HasAddableOptions %>
				<fieldset class="fieldOptionsGroup">
					<legend><% _t('EditableFormField.OPTIONS', 'Options') %></legend>
					<ul class="editableOptions" id="$FieldName.ATT-list">

						<% if canEdit %>
							<% loop Options %>
								$EditSegment
							<% end_loop %>
							<% if HasAddableOptions %>
								<li class="{$ClassName}Option">
									<a href="#" rel="$ID" class="addableOption" title="<% _t('EditableFormField.ADD', 'Add option to field') %>">
										<% _t('EditableFormField.ADDLabel', 'Add option') %>
									</a>
								</li>
							<% end_if %>
						<% else %>
							<% loop Options %>
								$ReadonlyOption
							<% end_loop %>
						<% end_if %>
					</ul>
				</fieldset>
			<% end_if %>
			
			<% if FieldConfiguration %>
				<fieldset class="fieldOptionsGroup">
					<legend><% _t('EditableFormField.FIELDCONFIGURATION', 'Field Configuration') %></legend>
					<% loop FieldConfiguration %>
						$FieldHolder
					<% end_loop %>
				</fieldset>
			<% end_if %>
			
			<% if FieldValidationOptions %>
				<fieldset class="fieldOptionsGroup">
					<legend><% _t('EditableFormField.VALIDATION', 'Validation') %></legend>
					<% loop FieldValidationOptions %>
						$FieldHolder
					<% end_loop %>
				</fieldset>
			<% end_if %>
		
			<fieldset class="customRules fieldOptionsGroup">
				<legend><% _t('EditableFormField.CUSTOMRULES', 'Custom Rules') %></legend>
				<ul id="{$FieldName}-customRules">
					<li>
						<a href="#" class="addCondition" title="<% _t('EditableFormField.ADD', 'Add') %>"><% _t('EditableFormField.ADDRULE', 'Add Rule') %></a>
					</li>
					<li class="addCustomRule">					
						<select name="{$FieldName}[CustomSettings][ShowOnLoad]">
							<option value="Show" <% if ShowOnLoad %>selected="selected"<% end_if %>><% _t('EditableFormField.SHOW', 'Show') %></option>
							<option value="Hide" <% if ShowOnLoad %><% else %>selected="selected"<% end_if %>><% _t('EditableFormField.HIDE', 'Hide') %></option>
						</select>

						<label class="left"><% _t('EditableFormField.FIELDONDEFAULT', 'Field On Default') %></label>
					</li>
					<li class="hidden">
						<select class="displayOption customRuleField" name="{$FieldName}[CustomRules][Display]">
							<option value="Show"><% _t('EditableFormField.SHOWTHISFIELD', 'Show This Field') %></option>
							<option value="Hide"><% _t('EditableFormField.HIDETHISFIELD', 'Hide This Field') %></option>
						</select>

						<label><% _t('EditableFormField.WHEN', 'When') %></label>
						<select class="fieldOption customRuleField" name="{$FieldName}[CustomRules][ConditionField]">
							<option></option>
							<% loop Parent %>
								<% if Fields %>
									<% loop Fields %>
										<option value="$Name"><% if Title %>$Title<% else %>$Name<% end_if %></option>
									<% end_loop %>
								<% end_if %>
							<% end_loop %>
						</select>

						<label><% _t('EditableFormField.IS', 'Is') %></label>
						<select class="conditionOption customRuleField" name="{$FieldName}[CustomRules][ConditionOption]">
							<option value=""></option>
							<option value="IsBlank"><% _t('EditableFormField.BLANK', 'Blank') %></option>
							<option value="IsNotBlank"><% _t('EditableFormField.NOTBLANK', 'Not Blank') %></option>
							<option value="HasValue"><% _t('EditableFormField.VALUE', 'Value') %></option>
							<option value="ValueNot"><% _t('EditableFormField.NOTVALUE', 'Not Value') %></option>
							<option value="ValueLessThan"><% _t('EditableFormField.LESSTHAN', 'Value Less Than') %></option>
							<option value="ValueLessThanEqual"><% _t('EditableFormField.LESSTHANEQUAL', 'Value Less Than Or Equal') %></option>
							<option value="ValueGreaterThan"><% _t('EditableFormField.GREATERTHAN', 'Value Greater Than') %></option>
							<option value="ValueGreaterThanEqual"><% _t('EditableFormField.GREATERTHANEQUAL', 'Value Greater Than Or Equal') %></option>
						</select>

						<input type="text" class="ruleValue hidden customRuleField" name="{$FieldName}[CustomRules][Value]" />

						<a href="#" class="deleteCondition" title="<% _t('EditableFormField.DELETE', 'Delete') %>"><img src="cms/images/delete.gif" alt="<% _t('EditableFormField.DELETE', 'Delete') %>" /></a>
					</li>
					<% if CustomRules %>
						<% loop CustomRules %>
							<li>
								<% include CustomRule %>
							</li>
						<% end_loop %>
					<% end_if %>
				</ul>
			</fieldset>
		</div>
	<% end_if %>
	
	<!-- Hidden option Fields -->
	<input type="hidden" class="typeHidden" name="{$FieldName}[Type]" value="$ClassName" /> 
	<input type="hidden" class="sortHidden" name="{$FieldName}[Sort]" value="$Sort" />
</li>
