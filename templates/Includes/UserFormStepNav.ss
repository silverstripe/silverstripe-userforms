<% if $FirstLast == "first last" %>
<% else %>
<nav class="step-navigation" aria-hidden="true" style="display:none;">
	<ul class="step-buttons">
		<% if $FirstLast == "first" %>
		<% else %>
		<li class="step-button-wrapper">
			<button class="step-button-prev">Prev</button>
		</li>
		<% end_if %>

		<% if $FirstLast == "last" %>

		<% if $ContainingPage.Actions %>
		<% loop $ContainingPage.Actions %>
		<li class="step-button-wrapper">
			$Field
		</li>
		<% end_loop %>
		<% end_if %>

		<% else %>
		<li class="step-button-wrapper">
			<button class="step-button-next">Next</button>
		</li>
		<% end_if %>
	</ul>
</nav>
<% end_if %>
