<% if $Fields %>
    <tr class="grid-field__filter-header" style="display:none;">
        <td colspan="{$ColSpan}" class="grid-field__uf-filter-header">
            <table>
                <tr>
                    <% loop $Fields %>
                        <th class="extra">$Field</th>
                    <% end_loop %>
                </tr>
            </table>
        </td>
    </tr>
<% end_if %>
