<?php 
/**
 * The template for displaying comment item
 *
 * @see 	https://developer.wordpress.org/reference/classes/wp_comment/
 * @author 	Pouriya Amjadzadeh
 * @version 3.0.0
 */

defined('ABSPATH') || exit; ?>

<!DOCTYPE html>
<head>
	<meta content="width=device-width,initial-scale=1"name=viewport>
	<style>body,html{height:100%;direction:rtl;}h1,h2,h3,h4,h5,h6,p{margin:0;padding:0}.wrapper{border-collapse:collapse;width:100%;max-width:800px;margin:0 auto}.text-left{text-align:left}.text-right{text-align:right}.text-center{text-align:center}.footer{padding:20px 0}.content{font-size:14px;padding:32px;line-height:30px;border-radius:6px;background-color:#fff;box-shadow:0 4px 8px rgba(0,0,0,.1)}.content :not(:last-child){margin-bottom:12px}.footer{font-size:13px}.footer p{padding:10px 0}@media only screen and (max-width:600px){table{width:95%}.footer,.header{padding:10px}}</style>
</head>
<body style=height:100%;margin:0;padding:0>
	<table style=color:#444;font-family:Tahoma,Tahoma;background-color:#f7f7f7;border-collapse:collapse;border:none;width:100%;height:100%>
		<tr>
			<td style=padding:16px;vertical-align:top>
				<table class=wrapper>
					<tr>
						<td class=content>
							<h2 class=mb>{{title}}</h2>
							{{content}}
						<tr>
						<td class="footer text-center">{{footer}}</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>