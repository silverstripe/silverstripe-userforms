<$Tag class="CompositeField $extraClass <% if ColumnCount %>multicolumn<% end_if %>">
	<% if $Tag == 'fieldset' && $Legend %>
		<legend>$Legend</legend>
	<% end_if %>
	
	<div class="middleColumn">
	<% loop $FieldList %>
		<% if $ColumnCount %>
			<div class="column-{$ColumnCount} $FirstLast">
				$FieldHolder
			</div>
		<% else %>
			$FieldHolder
		<% end_if %>
	<% end_loop %>
	</div>

	<% if $RightTitle %>
	<aside class="right" role="complementary">
		<p>$RightTitle</p>
	</aside>
	<% end_if %>

	<% if $Description %><span class="description">$Description</span><% end_if %>
</$Tag>
