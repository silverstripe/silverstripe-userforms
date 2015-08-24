<div id="$Name" class="field<% if $extraClass %> $extraClass<% end_if %>">
	<div class="middleColumn">
		$Field
	</div>
	<% if $RightTitle %><span id="{$Name}_right_title" class="right-title">$RightTitle</span><% end_if %>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
</div>
