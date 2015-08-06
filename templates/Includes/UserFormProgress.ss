<% cached "UserForms_Navigation", $LastEdited %>
<% if $NumberOfSteps.Count > "1" %>
<div id="userform-progress" aria-hidden="true" style="display:none;">
	<p>Step <span class="current-step-number">1</span> of $NumberOfSteps.Count</p>
	<div class="progress">
		<div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="$NumberOfSteps.Count"></div>
	</div>
	<nav>
		<ul class="step-buttons">
			<% loop $NumberOfSteps %>
				<li class="step-button-wrapper<% if $Pos == '1' %> current<% end_if %>">
					<button class="step-button-jump" disabled="disabled">$Pos</button>
				</li>
			<% end_loop %>
		<ul>
	</nav>
</div>
<% end_if %>
<% end_cached %>
