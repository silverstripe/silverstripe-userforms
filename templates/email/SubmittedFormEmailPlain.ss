<%-- Note: content is already escaped in UserDefinedFormController::process --%>
$Body.RAW

<% if not $HideFormData %>
	*
	<% loop $Fields %>
		* <% if $Title %>$Title<% else %>$Name<% end_if %>
		* $FormattedValue
	<% end_loop %>
<% end_if %>
