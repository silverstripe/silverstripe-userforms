<% loop $Options %>
    <div class="$Class">
        <input id="$ID" class="radio" name="$Name" type="radio" value="$Value.ATT"<% if $isChecked %>
               checked<% end_if %><% if $isDisabled %> disabled<% end_if %> <% if $Up.Required %>required<% end_if %>
               $Top.getValidationAttributesHTML().RAW />
        <label for="$ID">$Title</label>
    </div>
<% end_loop %>
