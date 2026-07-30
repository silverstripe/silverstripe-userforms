<div id="<% if $getAttribute(htmlID) %>$getAttribute(htmlID)<% else %>$Name<% end_if %>" class="field<% if $extraClass %> $extraClass<% end_if %>">
	<% if $Title %><label class="left" for="$ID">$Title</label><% end_if %>
	<div class="middleColumn">
		$Field
	</div>
	<% if $RightTitle %><span id="<% if $getAttribute(htmlID) %>$getAttribute(htmlID)<% else %>$Name<% end_if %>_right_title" class="right-title">$RightTitle</span><% end_if %>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
</div>
