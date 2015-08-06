<% cached "UserForms_Navigation", $LastEdited %>
<% if $NumberOfSteps.Count > "1" %>
<nav id="userform-progress" aria-hidden="true" style="display:none;">
	<p>Step <span class="current-step-number">1</span> of $NumberOfSteps.Count</p>
	<div class="progress">
		<div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="$NumberOfSteps.Count"></div>
	</div>
	<ul>
		<% loop $NumberOfSteps %>
			<li <% if $Pos == '1' %>class="current"<% end_if %>>
				<button class="step-button-jump">$Pos</button>
			</li>
		<% end_loop %>
	<ul>
</nav>
<% end_if %>
<% end_cached %>
