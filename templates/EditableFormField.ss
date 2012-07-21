<!-- JS Relys on EditableFormField as a class - and the 3 ids in this order - do not change -->
<li class="$ClassName EditableFormField" id="$Name.ATT EditableItem_$Pos $Name">
	<div class="fieldInfo">
		<% if canEdit %>
			<img class="fieldHandler" src="$ModulePath(framework)/images/drag.gif" alt="<% _t('DRAG', 'Drag to rearrange order of fields') %>" />
		<% else %>
			<img class="fieldHandler" src="$ModulePath(framework)/images/drag_readonly.gif" alt="<% _t('LOCKED', 'These fields cannot be modified') %>" />
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
		<div class="extraOptions hidden" id="$Name.ATT-extraOptions">
			<% if HasAddableOptions %>
				<fieldset class="fieldOptionsGroup">
					<legend><% _t('OPTIONS', 'Options') %></legend>
					<ul class="editableOptions" id="$FieldName.ATT-list">

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
				<legend><% _t('CUSTOMRULES', 'Custom Rules') %></legend>
				<ul id="{$FieldName}-customRules">
					<li>
						<a href="#" class="addCondition" title="<% _t('ADD', 'Add') %>"><% _t('ADDRULE', 'Add Rule') %></a>
					</li>
					<li class="addCustomRule">					
						<select name="{$FieldName}[CustomSettings][ShowOnLoad]">
							<option value="Show" <% if ShowOnLoad %>selected="selected"<% end_if %>><% _t('SHOW', 'Show') %></option>
							<option value="Hide" <% if ShowOnLoad %><% else %>selected="selected"<% end_if %>><% _t('HIDE', 'Hide') %></option>
						</select>

						<label class="left"><% _t('FIELDONDEFAULT', 'Field On Default') %></label>
					</li>
					<li class="hidden">
						<select class="displayOption customRuleField" name="{$FieldName}[CustomRules][Display]">
							<option value="Show"><% _t('SHOWTHISFIELD', 'Show This Field') %></option>
							<option value="Hide"><% _t('HIDETHISFIELD', 'Hide This Field') %></option>
						</select>

						<label><% _t('WHEN', 'When') %></label>
						<select class="fieldOption customRuleField" name="{$FieldName}[CustomRules][ConditionField]">
							<option></option>
							<% control Parent %>
								<% if Fields %>
									<% control Fields %>
										<option value="$Name"><% if Title %>$Title<% else %>$Name<% end_if %></option>
									<% end_control %>
								<% end_if %>
							<% end_control %>
						</select>

						<label><% _t('IS', 'Is') %></label>
						<select class="conditionOption customRuleField" name="{$FieldName}[CustomRules][ConditionOption]">
							<option value=""></option>
							<option value="IsBlank"><% _t('BLANK', 'Blank') %></option>
							<option value="IsNotBlank"><% _t('NOTBLANK', 'Not Blank') %></option>
							<option value="HasValue"><% _t('VALUE', 'Value') %></option>
							<option value="ValueNot"><% _t('NOTVALUE', 'Not Value') %></option>
							<option value="ValueLessThan"><% _t('LESSTHAN', 'Value Less Than') %></option>
							<option value="ValueLessThanEqual"><% _t('LESSTHANEQUAL', 'Value Less Than Or Equal') %></option>
							<option value="ValueGreaterThan"><% _t('GREATERTHAN', 'Value Greater Than') %></option>
							<option value="ValueGreaterThanEqual"><% _t('GREATERTHANEQUAL', 'Value Greater Than Or Equal') %></option>
						</select>

						<input type="text" class="ruleValue hidden customRuleField" name="{$FieldName}[CustomRules][Value]" />

						<a href="#" class="deleteCondition" title="<% _t('DELETE', 'Delete') %>"><img src="cms/images/delete.gif" alt="<% _t('DELETE', 'Delete') %>" /></a>
					</li>
					<% if CustomRules %>
						<% control CustomRules %>
							<li>
								<% include CustomRule %>
							</li>
						<% end_control %>
					<% end_if %>
				</ul>
			</fieldset>
		</div>
	<% end_if %>
	
	<!-- Hidden option Fields -->
	<input type="hidden" class="typeHidden" name="{$FieldName}[Type]" value="$ClassName" /> 
	<input type="hidden" class="sortHidden" name="{$FieldName}[Sort]" value="$Sort" />
</li>
