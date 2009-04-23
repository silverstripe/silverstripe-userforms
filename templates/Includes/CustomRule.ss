<select class="displayOption customRuleField" name="{$Name}[CustomRules]<% if First %><% else %><% if Pos %>[$Pos]<% end_if %><% end_if %>[Display]">
	<option value="Show" <% if Display = Show %>selected="selected"<% end_if %>><% _t('SHOWTHISFIELD', 'Show This Field') %></option>
	<option value="Hide" <% if Display = Hide %><% if First %><% else %>selected="selected"<% end_if %><% end_if %>><% _t('HIDETHISFIELD', 'Hide This Field') %></option>
</select>

<label><% _t('WHEN', 'When') %></label>
<select class="fieldOption customRuleField" name="{$Name}[CustomRules]<% if First %><% else %><% if Pos %>[$Pos]<% end_if %><% end_if %>[ConditionField]">
	<option value="" selected="selected"></option>
	<% control Fields %>
		<option value="$BaseName" <% if isSelected %>selected="selected"<% end_if %>>$Title</option>
	<% end_control %>
</select>

<label><% _t('IS', 'Is') %></label>
<select class="conditionOption customRuleField" name="{$Name}[CustomRules]<% if First %><% else %><% if Pos %>[$Pos]<% end_if %><% end_if %>[ConditionOption]">
	<option value=""></option>
	<option value="IsBlank" <% if ConditionOption = IsBlank %>selected="selected"<% end_if %>><% _t('BLANK', 'Blank') %></option>
	<option value="IsNotBlank" <% if ConditionOption = IsNotBlank %>selected="selected"<% end_if %>><% _t('NOTBLANK', 'Not Blank') %></option>
	<option value="HasValue" <% if ConditionOption = HasValue %>selected="selected"<% end_if %>><% _t('VALUE', 'Value') %></option>
	<option value="ValueNot" <% if ConditionOption = ValueNot %>selected="selected"<% end_if %>><% _t('NOTVALUE', 'Not Value') %></option>
</select>

<input type="text" class="ruleValue <% if Value %><% else %>hidden<% end_if %> customRuleField" name="{$Name}[CustomRules]<% if First %><% else %><% if Pos %>[$Pos]<% end_if %><% end_if %>[Value]" value="$Value" />

<a href="#" class="addCondition <% if First %><% else %>hidden<% end_if %>" title="<% _t('ADD', 'Add') %>"><img src="cms/images/add.gif" alt="<% _t('ADD', 'Add') %>" /></a>
<a href="#" class="deleteCondition <% if First %>hidden<% end_if %>" title="<% _t('DELETE', 'Delete') %>"><img src="cms/images/delete.gif" alt="<% _t('DELETE', 'Delete') %>" /></a>
