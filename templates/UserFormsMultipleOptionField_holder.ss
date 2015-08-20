<fieldset id="$Name" class="field<% if $extraClass %> $extraClass<% end_if %>">
	<% if $Title %><legend class="left">$Title</legend><% end_if %>

	<div class="middleColumn">
		$Field
	</div>

	<% if $RightTitle %>
	<aside class="right" role="complementary">
		<p>$RightTitle</p>
	</aside>
	<% end_if %>

	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>

	<% if $Description %><span class="description">$Description</span><% end_if %>
</fieldset>
