<input $AttributesHTML<% if $RightTitle %>aria-describedby="{$Name}_right_title" <% end_if %>/>
<% if $Title %><label class="left" for="$ID">$Title <% if $Required %><span class="req-indicator">(required)</span><% end_if %></label><% end_if %>
