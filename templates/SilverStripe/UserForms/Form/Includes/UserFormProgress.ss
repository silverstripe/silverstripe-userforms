<% if $Steps.Count > 1 %>
	<div id="userform-progress" class="userform-progress" aria-hidden="true" style="display:none;">
		<p class="page-progress">Page <span class="current-step-number">1</span> of <span class="total-step-number">$Steps.Count</span></p>
		<div class="progress">
			<div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="$Steps.Count"></div>
		</div>
		<nav aria-label="Pages in this form">
			<ul class="step-buttons">
				<% loop $Steps %>
				<li class="step-button-wrapper<% if $IsFirst %> current<% end_if %>" data-for="$Name">
					<%-- Remove js-align class to remove javascript positioning --%>
					<%-- Accessibility patch (silverstripeltd fork): aria-disabled keeps screen readers informed rather than removing the disabled attribute. --%>
					<button class="step-button-jump js-align" aria-disabled="true" data-step="$Pos"><% if $Top.ButtonText %>$Top.ButtonText <% end_if %>$Pos</button>
				</li>
				<% end_loop %>
			</ul>
		</nav>
	</div>
	<h2 class="progress-title"></h2>
<% end_if %>
