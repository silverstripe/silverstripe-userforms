<div id="userforms-submissions">

	<% if Submissions %>
		<ul class="userforms-submission-actions">
			<li><a href="{$Top.Link}/export/?id={$RecordID}"><% _t('EXPORTSUBMISSIONS', 'Export submissions to CSV') %></a></li>
			<li><a href="{$Top.Link}/deletesubmissions/?id={$RecordID}" class="deleteAllSubmissions"><% _t('DELETEALLSUBMISSIONS', 'Delete All Submissions') %></a></li>
		</ul>
		
		<% loop Submissions %>
			<div class="userform-submission">
				<h5><% _t('SUBMITTED', 'Submitted at') %> {$Created.Nice} <a href="{$Top.Link}/deletesubmission/?id={$ID}" class="deleteSubmission"><% _t('DELETESUBMISSION', 'Delete Submission') %></a></h5>
					<% loop Values %>
						<div id="Text_readonly" class="field readonly text">
							<label class="left" for="Form_EditForm_Text_readonly">$Title</label>
							<div class="middleColumn">
								<span id="Form_EditForm_Text_readonly" class="readonly text">$Value</span>
							</div>
	
						</div>
					<% end_loop %>	
			</div>
		<% end_loop %>
		
		<% if Submissions.MoreThanOnePage %>
			<div class="userforms-submissions-pagination"> 
			
				<% if Submissions.NotFirstPage %>
					<a class="prev" href="javascript:void(0)" onclick="jQuery('#userforms-submissions').parent().load(jQuery('base').get(0).href+'/{$Top.Link}/getMoreSubmissions/?start={$Submissions.PrevStart}');" title="View the previous page">Previous page</a> 
				<% end_if %>
				
				<span>Viewing rows $Submissions.Start - $Submissions.StartPlusOffset of $Submissions.TotalCount rows</span>
				
				<% if Submissions.NotLastPage %>
					<a class="next" href="javascript:void(0)" onclick="jQuery('#userforms-submissions').parent().load(jQuery('base').get(0).href+'/{$Top.Link}/getMoreSubmissions/?start={$Submissions.NextStart}');" title="View the next page">Next page</a> 
				<% end_if %>
			</div>
		<% end_if %>
	<% else %>
		<p class="userforms-nosubmissions" <% if Submissions %>style="display: none"<% end_if %>><% _t('NOSUBMISSIONS', 'No Submissions') %></p>
	<% end_if %>
</div>
