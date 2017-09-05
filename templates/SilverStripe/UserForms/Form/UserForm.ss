<form $AttributesHTML>

<% include SilverStripe\\UserForms\\Form\\UserFormProgress %>
<% include SilverStripe\\UserForms\\Form\\UserFormStepErrors %>

<% if $Message %>
	<p id="{$FormName}_error" class="message $MessageType">$Message</p>
<% else %>
	<p id="{$FormName}_error" class="message $MessageType" aria-hidden="true" style="display: none;"></p>
<% end_if %>

<% if $Legend %>
    <fieldset>
        <legend>$Legend</legend>
        <% include SilverStripe\\UserForms\\Form\\UserFormFields %>
    </fieldset>
<% else %>
    <div class="userform-fields">
        <% include SilverStripe\\UserForms\\Form\\UserFormFields %>
    </div>
<% end_if %>

<% if $Steps.Count > 1 %>
	<% include SilverStripe\\UserForms\\Form\\UserFormStepNav %>
<% else %>
	<% include SilverStripe\\UserForms\\Form\\UserFormActionNav %>
<% end_if %>

</form>
