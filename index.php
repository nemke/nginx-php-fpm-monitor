<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en" >
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Nginx - PHP FPM Monitor</title>
		<link rel="stylesheet" href="css/normalize.css">
		<link rel="stylesheet" href="css/foundation.min.css" />
		<link rel="stylesheet" href="js/table-sorter/style.css" />
		<link rel="stylesheet" href="css/app.css" />
		<script src="js/vendor/modernizr.js"></script>
		<script src="js/vendor/jquery.js"></script>
		<script src="js/vendor/fastclick.js"></script>
		<script src="js/jquery.tablesorter.min.js"></script>
	</head>
	<body>
		<div class="row">
			<div class="large-24 columns">
				<span class="copyright">
					Designed by <a href="http://www.nmdesign.rs/">NM Design</a>.
					<br />
					All rights reserved.
				</span>
				<h1>Nginx - PHP FPM Monitor</h1>
			</div>
		</div>
		<div class="row">
			<div class="large-24 columns">
				<input type="checkbox" checked id="autorefresh" style="margin-left:0; margin-bottom:15px; margin-right: 6px" checked /> Auto refresh
			</div>
		</div>
		<div class="row data-presenter">
			<div class="large-24 columns">
			</div>
		</div>
		<script src="js/foundation.min.js" type="text/javascript"></script>
		<script>
			$(document).foundation();
		</script>
		<script>
			var refresh_interval = 1000;

			function RefreshData()
			{
				if($('#autorefresh').prop('checked'))
				{
					$.get('get_data.php', function(data)
					{
						$('.data-presenter').html(data);
						$('table.tablesorter').tablesorter(); 
						$(document).foundation();

						setTimeout(RefreshData, refresh_interval);
					});
				}
			}

			$(document).ready(function() 
			{
				RefreshData();

				$(document).keydown(function(event)
				{
					if(event.which == 27)
						$('#autorefresh').prop('checked', false);
				});

				$("#autorefresh").change(function()
				{
					if($(this).prop('checked'))
					{
						RefreshData();
					}
				});
			});
		</script>
	</body>
</html>
