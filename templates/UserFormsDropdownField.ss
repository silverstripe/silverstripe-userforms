<select $AttributesHTML <% if $RightTitle %>aria-describedby="{$Name}_right_title"<% end_if %>>
<% loop $Options %>
	<option value="$Value.XML"<% if $Selected %> selected="selected"<% end_if %><% if $Disabled %> disabled="disabled"<% end_if %>>$Title.XML</option>
<% end_loop %>
</select>
