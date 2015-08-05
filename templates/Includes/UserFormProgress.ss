<% cached "UserForms_Navigation", $LastEdited %>
<% if $NumberOfSteps.Count > "1" %>
<nav id="userform-progress" aria-hidden="true" style="display:none;">
	<div class="progress">
		<div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="$NumberOfSteps.Count">
			<span class="sr-only">Step 1 of $NumberOfSteps.Count</span>
		</div>
	</div>
	<ul>
		<% loop $NumberOfSteps %>
			<li><button class="step-button-jump">$Pos</button></li>
		<% end_loop %>
	<ul>
</nav>
<% end_if %>
<% end_cached %>
