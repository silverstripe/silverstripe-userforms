<% if $Options.Count %>
    <% loop $Options %>
        <div class="$Class">
            <input id="$ID" class="checkbox" name="$Name" type="checkbox" value="$Value.ATT"<% if $isChecked %>
                   checked="checked"<% end_if %><% if $isDisabled %> disabled="disabled"<% end_if %>
                   $Top.getValidationAttributesHTML().RAW />
            <label for="$ID">$Title.XML</label>
        </div>
    <% end_loop %>
<% else %>
    <p>No options available</p>
<% end_if %>
