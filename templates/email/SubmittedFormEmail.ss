<h1>$Subject</h1>
$Body

<% if HideFormData %>
<% else %>
	<dl>
		<% control Fields %>
			<dt><strong><% if Title %>$Title<% else %>$Name<% end_if %></strong></dt>
			<dd style="margin: 4px 0 14px 0">$FormattedValue</dd>
		<% end_control %>
	</dl>
<% end_if %>
