<script type="text/x-tmpl" class="$TemplateName">
	<tr class="ss-gridfield-inline-new $ExtraClass.ATT">
		<% loop $Columns %>
			<% if $IsActions %>
				<td$Attributes>
					<button class="ss-gridfield-delete-inline gridfield-button-delete ss-ui-button" data-icon="cross-circle"></button>
				</td>
			<% else %>
				<td$Attributes>$Content</td>
			<% end_if %>
		<% end_loop %>
	</tr>
</script>
