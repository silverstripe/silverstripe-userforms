<div class="reportfilter">
	$FilterForm
</div>
<div class="reports" id="FormSubmissions">

<% if ExportLink %>
	<a href="$ExportLink" title="Export CSV file"><strong><% _t('EXPORTSUBMISSIONS', 'Export submissions to CSV') %></strong></a>
<% end_if %>

<% control Submissions %>
	<div class="report">
		<span class="submitted"><% _t('SUBMITTED', 'Submitted at') %> $Created.Nice <% if Recipient %>to $Recipient<% end_if %></span>
		<table>
			<% control FieldValues %>
				<tr>
					<td class="field">$Title</td>
					<td class="value"><% if Link %><a href="$Link"><% end_if %>$Value<% if Link %></a><% end_if %></td>
				</tr>
			<% end_control %>	
		</table>
	</div>
<% end_control %>
</div>