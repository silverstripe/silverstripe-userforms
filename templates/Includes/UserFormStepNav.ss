<% if $Form.Steps.Count > 1 %>
	<nav class="step-navigation" aria-hidden="true" style="display:none;">
		<ul class="step-buttons">
			<% if $StepNumber > 1 %>
				<li class="step-button-wrapper">
					<button class="step-button-prev">Prev</button>
				</li>
			<% end_if %>

			<% if $StepNumber < $Form.Steps.Count %>
				<li class="step-button-wrapper">
					<button class="step-button-next">Next</button>
				</li>
			<% else_if $Form.Actions %><% loop $Form.Actions %>
				<li class="step-button-wrapper">
					$Field
				</li>
			<% end_loop %><% end_if %>
		</ul>
	</nav>
<% end_if %>
