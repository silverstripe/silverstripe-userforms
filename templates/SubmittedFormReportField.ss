<div class="reportfilter">
	$FilterForm
</div>
<div class="reports" id="FormSubmissions">

<% if ExportLink %>
	<a href="$ExportLink" title="<% _t('EXPORTCSVFILE', 'Export CSV file') %>"><strong><% _t('EXPORTSUBMISSIONS', 'Export submissions to CSV') %></strong></a>
<% end_if %>

<% if DeleteLink %>
	<a href="$DeleteLink" title="<% _t('DELETEALLSUBMISSIONS', 'Delete Submissions') %>"><strong><% _t('DELETEALLSUBMISSIONS', 'Delete Submissions') %></strong></a>
<% end_if %>

<% control Submissions %>
	<div class="report">
		<span class="submitted"><% _t('SUBMITTED', 'Submitted at') %> $Created.Nice. <a href="$DeleteLink"><% _t('DELETESUBMISSION', 'Delete Submission') %></a></span>
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