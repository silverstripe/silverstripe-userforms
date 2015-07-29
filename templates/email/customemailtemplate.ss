<div style="color:blue;">$Body</div>

<% if HideFormData %>
<% else %>
	<dl>
		<% loop Fields %>
			<dt style="color:red;"><strong><% if Title %>$Title<% else %>$Name<% end_if %></strong></dt>
			<dd style="margin: 4px 0 14px 0">$FormattedValue</dd>
		<% end_loop %>
	</dl>
<% end_if %>