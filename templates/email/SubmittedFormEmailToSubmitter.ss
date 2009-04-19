$Body
		
<p><% _t('SUBMITTED',"You have submitted the following information:") %></p>

<table>
	<% control Fields %>
		<tr>
			<td style="padding: 5px"><b>$Title</b></td>
			<td style="padding: 5px">$Value.RAW</td>
		</tr>			
	<% end_control %>
</table>