<% if $Steps.Count > 1 %>
	<div id="userform-progress" class="userform-progress" aria-hidden="true" style="display:none;">
		<h2 class="progress-title"></h2>
		<p>Page <span class="current-step-number">1</span> of <span class="total-step-number">$Steps.Count</span></p>
		<div class="progress">
			<div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="$Steps.Count"></div>
		</div>
		<nav>
			<ul class="step-buttons">
				<% loop $Steps %>
				<li class="step-button-wrapper<% if $First %> current<% end_if %>" data-for="$Name">
					<%-- Remove js-align class to remove javascript positioning --%>
					<button class="step-button-jump js-align" disabled="disabled">$Pos</button>
				</li>
				<% end_loop %>
			</ul>
		</nav>
	</div>
<% end_if %>
