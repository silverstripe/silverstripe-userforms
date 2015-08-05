<% if $FirstLast == "first last" %>
<% else %>
<nav class="step-navigation" aria-hidden="true" style="display:none;">
	<ul>
		<% if $FirstLast == "first" %>
		<% else %>
		<li><button class="step-button-prev">Prev</button><li>
		<% end_if %>

		<% if $FirstLast == "last" %>
		<% if $ContainingPage.Actions %>
		<div class="Actions">
			<% loop $ContainingPage.Actions %>
			$Field
			<% end_loop %>
		</div>
		<% end_if %>
		<% else %>
		<li><button class="step-button-next">Next</button></li>
		<% end_if %>
	</ul>
</nav>
<% end_if %>
