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
								<span id="Form_EditForm_Text_readonly" class="readonly text">$FormattedValue</span>
							</div>
	
						</div>
					<% end_loop %>	
			</div>
		<% end_loop %>
		
	 	<% if Submissions.MoreThanOnePage %>
		<div class="userforms-submissions-pagination">
			<span><% _t('PAGES', 'Pages') %>:</span>
			
			<% loop Submissions.Pages() %>
				<% if CurrentBool %>
					$PageNum
				<% else %>
					<% if Link %>
						<a href="{$Top.Link(getMoreSubmissions)}?page=$PageNum">$PageNum</a>
					<% else %>
						...
					<% end_if %>
				<% end_if %>
			<% end_loop %>
		</div>
		<% end_if %>
	 	
	<% else %>
		<p class="userforms-nosubmissions" <% if Submissions %>style="display: none"<% end_if %>><% _t('NOSUBMISSIONS', 'No Submissions') %></p>
	<% end_if %>
</div>
