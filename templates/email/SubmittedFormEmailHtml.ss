<%-- Note: content is already escaped in UserDefinedFormController::process --%>
$Body.RAW

<% if not $HideFormData %>
	<dl>
		<% loop $Fields %>
			<dt><strong><% if $Title %>$Title<% else %>$Name<% end_if %></strong></dt>
			<dd style="margin: 4px 0 14px 0">$FormattedValue</dd>
		<% end_loop %>
	</dl>
<% end_if %>
