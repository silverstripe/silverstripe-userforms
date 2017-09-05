<$Tag class="CompositeField $extraClass <% if ColumnCount %>multicolumn<% end_if %>"<% if $Tag == 'fieldset' && $RightTitle %>aria-describedby="{$Name}_right_title"<% end_if %>>
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

	<% if $RightTitle %><span id="{$Name}_right_title" class="right-title">$RightTitle</span><% end_if %>
</$Tag>
