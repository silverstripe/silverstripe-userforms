<nav id="step-navigation" class="step-navigation">
	<ul class="step-buttons">
		<%--
			If JavaScript is disabled multi-step forms are displayed as a single page
			so the 'prev' and 'next' button are not used. These buttons are made visible via JavaScript.
		--%>
		<li class="step-button-wrapper" aria-hidden="true" style="display:none;">
			<button class="step-button-prev">
                <%t SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFormStep.STEP_PREV "Prev" %>
            </button>
		</li>
		<li class="step-button-wrapper" aria-hidden="true" style="display:none;">
			<button class="step-button-next">
                <%t SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFormStep.STEP_NEXT "Next" %>
            </button>
		</li>

		<% if $Actions %>
		<li class="step-button-wrapper btn-toolbar Actions">
		<% loop $Actions %>
			$Field
		<% end_loop %>
		</li>
		<% end_if %>

	</ul>
</nav>
