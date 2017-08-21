<form $AttributesHTML>

<% include UserFormProgress %>
<% include UserFormStepErrors %>

<% if $Message %>
	<p id="{$FormName}_error" class="message $MessageType">$Message</p>
<% else %>
	<p id="{$FormName}_error" class="message $MessageType" aria-hidden="true" style="display: none;"></p>
<% end_if %>

<% if $Legend %>
    <fieldset>
        <legend>$Legend</legend>
        <% include UserFormFields %>
    </fieldset>
<% else %>
    <div class="userform-fields">
        <% include UserFormFields %>
    </div>
<% end_if %>

<% if $Steps.Count > 1 %>
	<% include UserFormStepNav %>
<% else %>
	<% include UserFormActionNav %>
<% end_if %>

</form>
