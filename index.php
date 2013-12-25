<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 'On');

	require_once 'config.php';
	require_once 'class.system.php';

	$system_info = new SystemInfo();

	$nginx_data = $system_info->GetNginxData($nginx_url);
	$php_fpm_data = $system_info->GetPHPFPMData($php_fpm_url);

	$main_process_data = '';
	$workers_data = '';
	$requests_data = '';
	$totals = array(
		'number_of_requests' => 0,
		'started' => 0,
		'waiting_connections' => 0,
		'workers' => 0,
		'requests_by_uri' => array(),
		'requests_by_uri_string' => '',
	);

	foreach ($php_fpm_data as $name => $value)
	{
		$row = '';

		if($name != 'processes')
		{
			switch ($name)
			{
				case 'start time':
					$row .= '<td>' . strftime('%d/%m/%Y %H:%M:%S', $value) . '</td>';
					$totals['started'] = strftime('%d/%m/%Y %H:%M:%S', $value);
					break;
				case 'start since':
					$row .= '<td>' . number_format($value / 60, 0) . ' m</td>';
					break;
				case 'listen queue':
					$row .= '<td>' . $value . '</td>';
					$totals['waiting_connections'] = $value;
					break;
				case 'total processes':
					$row .= '<td>' . $value . '</td>';
					$totals['workers'] = $value;
					break;
				default:
					$row .= '<td>' . $value . '</td>';
					break;
			}
			
			$main_process_data .= $row;
		}
	}

	foreach ($php_fpm_data['processes'] as $process)
	{
		$requests_row = '<tr>';
		$workers_row = '<tr>';

		foreach ($process as $name => $value)
		{
			switch ($name)
			{
				case 'pid':
					$workers_row .= '<td>' . $value . '</td>';
					$requests_row .= '<td>' . $value . '</td>';
					break;
				case 'request method':
					$workers_row .= '<td>' . $value . '</td>';
					$requests_row .= '<td>' . $value . '</td>';
					break;
				case 'content length':
					$workers_row .= '<td>' . $value . '</td>';
					$requests_row .= '<td>' . $value . '</td>';
					break;
				case 'user':
					$workers_row .= '<td>' . $value . '</td>';
					$requests_row .= '<td>' . $value . '</td>';
					break;
							var_dump();exit;case 'script':
					$workers_row .= '<td>' . $value . '</td>';
					$requests_row .= '<td>' . $value . '</td>';
					break;
				case 'start time':
					$workers_row .= '<td>' . strftime('%d/%m/%Y %H:%M:%S', $value) . '</td>';
					break;
				case 'last request cpu':
					$workers_row .= '<td>' . $value . '</td>';
					break;
				case 'last request memory':
					$workers_row .= '<td>' . $value / 1024 . '</td>';
					break;
				case 'request uri':
					$workers_row .= '<td class="td-uri">' . $value . '</td>';
					$requests_row .= '<td class="td-uri-long">' . $value . '</td>';
					break;
				default:
					$workers_row .= '<td>' . $value . '</td>';
					break;
			}
		}

		if($process['state'] == 'Running')
		{
			$requests_data .= $requests_row . '</tr>';

			$totals['number_of_requests']++;

			$url_md5 = md5($process['request uri']);

			if(isset($totals['requests_by_uri'][$url_md5]))
				$totals['requests_by_uri'][$url_md5]['counter']++;
			else
			{
				$totals['requests_by_uri'][$url_md5] = array(
					'counter' => 1,
					'uri' => $process['request uri'],
				);
			}
		}

		$workers_data .= $workers_row . '</tr>';
	}

	foreach($totals['requests_by_uri'] as $md5_uri => $request_data)
		$totals['requests_by_uri_string'] .= '<tr><td>' . $request_data['counter'] . '</td><td>' . $request_data['uri'] . '</td></tr>';
?>
<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Nginx - PHP FPM Monitor</title>
		<link rel="stylesheet" href="css/normalize.css">
		<link rel="stylesheet" href="css/foundation.min.css" />
		<link rel="stylesheet" href="js/table-sorter/style.css" />
		<link rel="stylesheet" href="css/app.css" />
		<script src="js/modernizr.js"></script>
		<script src="js/jquery.js"></script>
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
				<input type="checkbox" checked id="autorefresh" style="margin-left:0; margin-bottom:15px; margin-right: 6px" /> Auto refresh
			</div>
		</div>
		<div class="row">
			<div class="large-24 columns">
				<h3>Nginx</h3>
				<table class="totals">
					<thead>
						<th>Active connections</th>
						<th>Total accepted connections</th>
						<th>Total handled connections</th>
						<th>Total requests</th>
						<th>Requests per connection</th>
						<th>Reading</th>
						<th>Writing</th>
						<th>Waiting</th>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $nginx_data['active_connections']; ?></td>
							<td><?php echo $nginx_data['total_accepted_connections']; ?></td>
							<td><?php echo $nginx_data['total_handled_connections']; ?></td>
							<td><?php echo $nginx_data['total_requests']; ?></td>
							<td><?php echo $nginx_data['requests_per_connection']; ?></td>
							<td><?php echo $nginx_data['reading']; ?></td>
							<td><?php echo $nginx_data['writing']; ?></td>
							<td><?php echo $nginx_data['waiting']; ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="large-24 columns">
				<h3>PHP FPM Totals</h3>
				<table class="totals">
					<thead>
						<th>Number of requests</th>
						<th>Started</th>
						<th>Waiting connections</th>
						<th>Workers</th>
						<th>Server Uptime</th>
						<th>Server Load</th>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $totals['number_of_requests']; ?></td>
							<td><?php echo $totals['started']; ?></td>
							<td><?php echo $totals['waiting_connections']; ?></td>
							<td><?php echo $totals['workers']; ?></td>
							<td><?php echo $system_info->GetUptime(); ?></td>
							<td><?php echo $system_info->GetLoad(); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="large-24 columns">
				<h3>PHP FPM Requests</h3>
				<table class="tablesorter">
					<thead>
						<tr>
							<th>PID</th>
							<th>Method</th>
							<th class="td-uri-long">URI</th>
							<th>Length</th>
							<th>User</th>
							<th>Script</th>
						</tr>
					</thead>
					<tbody>
						<?php echo $requests_data; ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="large-24 columns">
				<h3>PHP FPM Requests sorted by number of same URI</h3>
				<table class="tablesorter">
					<thead>
						<tr>
							<th>Number of requests</th>
							<th>URI</th>
						</tr>
					</thead>
					<tbody>
						<?php echo $totals['requests_by_uri_string'] ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="large-24 columns">
				<h3>PHP FPM Pools</h3>
				<table>
					<thead>
						<tr>
							<th>Pool</th>
							<th>Manager</th>
							<th>Start</th>
							<th>Working for</th>
							<th>accepted conn</th>
							<th>listen queue</th>
							<th>max listen queue</th>
							<th>listen queue len</th>
							<th>idle processes</th>
							<th>active processes</th>
							<th>total processes</th>
							<th>max active processes</th>
							<th>max children reached</th>
						</tr>
					</thead>
					<tbody>
						<?php echo $main_process_data; ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="large-24 columns">
				<h3>PHP FPM Workers</h3>
				<table class="tablesorter">
					<thead>
						<tr>
							<th>PID</th>
							<th>State</th>
							<th>Process started</th>
							<th>Start since</th>
							<th>Requests</th>
							<th>Request Duration</th>
							<th>Method</th>
							<th class="td-uri">URI</th>
							<th>Lenght</th>
							<th>User</th>
							<th>Script</th>
							<th>CPU</th>
							<th>Memory (kb)</th>
						</tr>
					</thead>
					<tbody>
						<?php echo $workers_data; ?>
					</tbody>
				</table>
			</div>
		</div>
		<script>
			function RefreshPage()
			{
				setTimeout(function()
				{
					if($('#autorefresh').prop('checked'))
						location.reload();
				}, 1000);
			}

			$(document).ready(function() 
			{
				$(document).keydown(function(event)
				{
					if(event.which == 27)
						$('#autorefresh').prop('checked', false);
				});

				$("#autorefresh").change(function()
				{
					if($(this).prop('checked'))
					{
						RefreshPage();
					}
				});

				RefreshPage();

				$('table.tablesorter').tablesorter(); 
			});
		</script>
	</body>
</html>
