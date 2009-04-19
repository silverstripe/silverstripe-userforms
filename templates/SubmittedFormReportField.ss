<div class="reportfilter">
	$FilterForm
</div>
<div class="reports" id="FormSubmissions">

	<% if Submissions %>
	
		<ul class="formSubmissionActions">
			<!-- @todo work out why url_handlers dont like /export/2 -->
			<li><a href="{$Top.Link}/export/?id={$RecordID}"><% _t('EXPORTSUBMISSIONS', 'Export submissions to CSV') %></a></li>
			<li><a href="{$Top.Link}/deletesubmissions/?id={$RecordID}" class="deleteSubmission"><% _t('DELETEALLSUBMISSIONS', 'Delete All Submissions') %></a></li>
		</ul>
		
		<% control Submissions %>
			<div class="report">
				<h4 class="submitted"><% _t('SUBMITTED', 'Submitted at') %> $Created.Nice. <a href="{$Top.Link}/deletesubmission/?id={$ID}" class="deleteSubmission"><% _t('DELETESUBMISSION', 'Delete Submission') %></a></h4>
				<table>
					<% control FieldValues %>
						<tr>
							<td class="field">$Title</td>
							<td class="value">$Value.RAW</td>
						</tr>
					<% end_control %>	
				</table>
			</div>
		<% end_control %>
	<% else %>
		<p><% _t('NOSUBMISSIONS', 'No Submissions') %></p>
	<% end_if %>
</div>