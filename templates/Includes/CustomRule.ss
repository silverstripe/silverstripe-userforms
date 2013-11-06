<select class="displayOption customRuleField" name="{$FieldName}[CustomRules][$Pos][Display]">
	<option value="Show" <% if Display = Show %>selected="selected"<% end_if %>><% _t('CustomRule.SHOWTHISFIELD', 'Show This Field') %></option>
	<option value="Hide" <% if Display = Hide %>selected="selected"<% end_if %>><% _t('CustomRule.HIDETHISFIELD', 'Hide This Field') %></option>
</select>
	
<label><% _t('CustomRule.WHEN', 'When') %></label>
<select class="fieldOption customRuleField" name="{$FieldName}[CustomRules][$Pos][ConditionField]">
	<option value="" selected="selected"></option>
	<% loop Fields %>
		<option value="$Name" <% if isSelected %>selected="selected"<% end_if %>>$Title</option>
	<% end_loop %>
</select>

<label><% _t('CustomRule.IS', 'Is') %></label>
<select class="conditionOption customRuleField" name="{$FieldName}[CustomRules][$Pos][ConditionOption]">
	<option value="IsBlank" <% if ConditionOption = IsBlank %>selected="selected"<% end_if %>><% _t('CustomRule.BLANK', 'Blank') %></option>
	<option value="IsNotBlank" <% if ConditionOption = IsNotBlank %>selected="selected"<% end_if %>><% _t('CustomRule.NOTBLANK', 'Not Blank') %></option>
	<option value="HasValue" <% if ConditionOption = HasValue %>selected="selected"<% end_if %>><% _t('CustomRule.VALUE', 'Value') %></option>
	<option value="ValueNot" <% if ConditionOption = ValueNot %>selected="selected"<% end_if %>><% _t('CustomRule.NOTVALUE', 'Not Value') %></option>
	<option value="ValueLessThan" <% if ConditionOption = ValueLessThan %>selected="selected"<% end_if %>><% _t('CustomRule.LESSTHAN', 'Value Less Than') %></option>
	<option value="ValueLessThanEqual" <% if ConditionOption = ValueLessThanEqual %>selected="selected"<% end_if %>><% _t('CustomRule.LESSTHANEQUAL', 'Value Less Than Or Equal') %></option>
	<option value="ValueGreaterThan" <% if ConditionOption = ValueGreaterThan %>selected="selected"<% end_if %>><% _t('CustomRule.GREATERTHAN', 'Value Greater Than') %></option>
	<option value="ValueGreaterThanEqual" <% if ConditionOption = ValueGreaterThanEqual %>selected="selected"<% end_if %>><% _t('CustomRule.GREATERTHANEQUAL', 'Value Greater Than Or Equal') %></option>
</select>

<input type="text" class="ruleValue <% if ConditionOption %><% if ConditionOption = IsBlank %>hidden<% else_if ConditionOption = IsNotBlank %>hidden<% end_if %><% else %>hidden<% end_if %> customRuleField" name="{$FieldName}[CustomRules][$Pos][Value]" value="$Value" />

<a href="#" class="deleteCondition" title="<% _t('CustomRule.DELETE', 'Delete') %>"><img src="cms/images/delete.gif" alt="<% _t('CustomRule.DELETE', 'Delete') %>" /></a>
