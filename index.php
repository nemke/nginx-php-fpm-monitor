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
					Designed by <a href="http://www.nmdesign.rs/">NM Design</a>. All rights reserved.
				</span>
				<h3>Nginx - PHP FPM Monitor</h3>
			</div>
		</div>
		<div class="row">
			<div class="small-2 columns">
				<a id="autorefresh" href="#" class="button tiny radius" data-refresh="On">Stop Refresh</a>
			</div>
			<div class="small-22 columns">
				<div class="row collapse">
					<div class="small-2 small-offset-20 columns">
						<input type="text" id="refresh_time" placeholder="">
					</div>
					<div class="small-2 columns">
						<a id="change_refresh_interval" href="#" class="button postfix">Refresh rate (ms)</a>
					</div>
				</div>
			</div>
		</div>
		<div class="data-presenter">
		</div>
		<script src="js/foundation.min.js" type="text/javascript"></script>
		<script>
			$(document).foundation();
		</script>
		<script>
			var refresh_interval = 1000;

			$('#refresh_time').val(refresh_interval);

			$('#change_refresh_interval').click(function()
			{
				refresh_interval = $('#refresh_time').val();
			});

			function RefreshData()
			{
				if($('#autorefresh').attr('data-refresh') == 'On')
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
					{
						$(this).attr('data-refresh', 'Off');
						$(this).text('Start Refresh');
					}
				});

				$('#autorefresh').click(function()
				{
					if($(this).attr('data-refresh') == 'On')
					{
						$(this).attr('data-refresh', 'Off');
						$(this).text('Start Refresh');
					}
					else
					{
						$(this).attr('data-refresh', 'On');
						$(this).text('Stop Refresh');
						RefreshData();
					}
				});
			});
		</script>
	</body>
</html>
