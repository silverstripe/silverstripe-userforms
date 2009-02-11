<html>
	<head>
	</head>
	<body>
		
		$Body
		
		<p>
			<% _t('SUBMITTED',"You have submitted the following information:") %>
		</p>

			<table>
			<% control Fields %>
			<tr>
				<td style="padding: 5px"><b>$Title</b></td>
				<td style="padding: 5px">$Value</td>
			</tr>			
			<% end_control %>
			</table>
	</body>
</html>
