<% if $Steps.Count > 1 %>
	<div id="userform-progress" class="userform-progress" aria-hidden="true" style="display:none;">
		<h2 class="progress-title"></h2>
		<p>Step <span class="current-step-number">1</span> of $NumberOfSteps.Count</p>
		<div class="progress">
			<div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="$NumberOfSteps.Count"></div>
		</div>
		<nav>
			<ul class="step-buttons">
				<% loop $Steps %>
					<li class="step-button-wrapper<% if $First %> current<% end_if %>">
					<button class="step-button-jump js-align" disabled="disabled">$Pos</button><!-- Remove js-align class to remove javascript positioning -->
					</li>
				<% end_loop %>
			</ul>
		</nav>
	</div>
<% end_if %>
