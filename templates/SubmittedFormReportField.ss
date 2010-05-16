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
							<td class="value">$FormattedValue</td>
						</tr>
					<% end_control %>	
				</table>
			</div>
		<% end_control %>
		<% if Submissions.MoreThanOnePage %>
			<div class="pagination"> 
				<% if Submissions.NotFirstPage %>
					<a class="prev" href="javascript:void(0)" onclick="jQuery('.middleColumn').parent().load(jQuery('base').get(0).href+'/{$Top.Link}/getSubmissions/?start={$Submissions.PrevStart}');" title="View the previous page">Previous page</a> 
				<% end_if %>
				
				<span>Viewing rows $Submissions.Start - $Submissions.StartPlusOffset of $Submissions.TotalCount rows</span>
				
				<% if Submissions.NotLastPage %>
					<a class="next" href="javascript:void(0)" onclick="jQuery('#FormSubmissions').parent().load(jQuery('base').get(0).href+'/{$Top.Link}/getSubmissions/?start={$Submissions.NextStart}');" title="View the next page">Next page</a> 
				<% end_if %>
			</div>
		<% end_if %>
	<% end_if %>
	<p class="noSubmissions" <% if Submissions %>style="display: none"<% end_if %>"><% _t('NOSUBMISSIONS', 'No Submissions') %></p>
</div>