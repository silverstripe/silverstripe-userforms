<div data-id="$ID" class="field<% if extraClass %> $extraClass<% end_if %>">
	$Field
	<label class="right" for="$Name">$Title</label>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
	<% if $Description %><span class="description">$Description</span><% end_if %>
</div>
