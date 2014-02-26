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
		'average_ram' => 0,
		'number_of_ram_processes' => 0,
		'average_cpu' => 0,
		'number_of_cpu_processes' => 0,
	);

	foreach ($php_fpm_data as $name => $value)
	{
		$row = '';

		if($name != 'processes')
		{
			switch ($name)
			{
				case 'start time':
					$row .= '<tr><td>Start time</td><td>' . strftime('%d/%m/%Y %H:%M:%S', $value) . '</td></tr>';
					$totals['started'] = strftime('%d/%m/%Y %H:%M:%S', $value);
					break;
				case 'start since':
					$row .= '<tr><td>Start since</td><td>' . number_format($value / 60, 0) . ' m</td></tr>';
					break;
				case 'listen queue':
					$row .= '<tr><td>Listen queue</td><td>' . $value . '</td></tr>';
					$totals['waiting_connections'] = $value;
					break;
				case 'total processes':
					$row .= '<tr><td>Total processes</td><td>' . $value . '</td></tr>';
					$totals['workers'] = $value;
					break;
				default:
					$row .= '<tr><td>' . $name . '</td><td>' . $value . '</td></tr>';
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
					if($value != 0)
					{
						$totals['average_cpu'] += $value;
						$totals['number_of_cpu_processes']++;
					}

					$workers_row .= '<td>' . $value . '</td>';
					break;
				case 'last request memory':
					if($value != 0)
					{
						$totals['average_ram'] += $value;
						$totals['number_of_ram_processes']++;
					}

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

	$size_unit = 0;
	$size_units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

	while ($totals['average_ram'] > 1023)
	{
		$totals['average_ram'] = $totals['average_ram'] / 1024;
		$size_unit++;
	}

	$totals['average_ram'] = round($totals['average_ram'] / $totals['number_of_ram_processes'], 1) . ' ' . $size_units[$size_unit];
	$totals['average_cpu'] = round($totals['average_cpu'] / $totals['number_of_cpu_processes'], 1) . ' %';

	foreach($totals['requests_by_uri'] as $md5_uri => $request_data)
		$totals['requests_by_uri_string'] .= '<tr><td>' . $request_data['counter'] . '</td><td>' . $request_data['uri'] . '</td></tr>';

	$memory_info = $system_info->GetMemoryInfo();
	$nginx_ips = $system_info->NginxConnectionsPerIP();
	$php_ram_info = $system_info->PHPRamInfo();
?>

<div class="row">
	<div class="large-5 columns">
		<h3>Server</h3>
		<table class="totals main-stats">
			<tbody>
				<tr><td>Load</td><td><?php echo $system_info->GetLoad(); ?></td></tr>
				<tr><td>Uptime</td><td><?php echo $system_info->GetUptime(); ?></td></tr>
				<tr><td>Total / Free RAM</td><td><?php echo $memory_info['MemTotal'] . ' / ' . $memory_info['MemFree']; ?></td></tr>
				<tr><td>Cached / Buffers</td><td><?php echo $memory_info['Cached'] . ' / ' . $memory_info['Buffers']; ?></td></tr>
				<tr><td>Total / Free Swap</td><td><?php echo $memory_info['SwapTotal'] . ' / ' . $memory_info['SwapFree']; ?></td></tr>
			</tbody>
		</table>
		<h3>Nginx</h3>
		<table class="totals main-stats">
			<tbody>
				<tr><td>Active connections</td><td><?php echo $nginx_data['active_connections']; ?></td></tr>
				<tr><td>Total accepted connections</td><td><?php echo $nginx_data['total_accepted_connections']; ?></td></tr>
				<tr><td>Total handled connections</td><td><?php echo $nginx_data['total_handled_connections']; ?></td></tr>
				<tr><td>Total requests</td><td><?php echo $nginx_data['total_requests']; ?></td></tr>
				<tr><td>Requests per connection</td><td><?php echo $nginx_data['requests_per_connection']; ?></td></tr>
				<tr><td>Reading</td><td><?php echo $nginx_data['reading']; ?></td></tr>
				<tr><td>Writing</td><td><?php echo $nginx_data['writing']; ?></td></tr>
				<tr><td>Waiting</td><td><?php echo $nginx_data['waiting']; ?></td></tr>
			</tbody>
		</table>
		<h3>PHP FPM Totals</h3>
		<table class="totals main-stats">
			<tbody>
				<tr><td>Number of requests</td><td><?php echo $totals['number_of_requests']; ?></td></tr>
				<tr><td>Started</td><td><?php echo $totals['started']; ?></td></tr>
				<tr><td>Waiting connections</td><td><?php echo $totals['waiting_connections']; ?></td></tr>
				<tr><td>Workers</td><td><?php echo $totals['workers']; ?></td></tr>
				<tr><td>Worker Average CPU</td><td><?php echo $totals['average_cpu'] ?></td></tr>
				<tr><td>Worker Average RAM</td><td><?php echo $totals['average_ram'] ?></td></tr>
			</tbody>
		</table>
		<h3>PHP FPM Pools</h3>
		<table class="totals main-stats">
			<tbody>
				<?php echo $main_process_data; ?>
			</tbody>
		</table>
	</div>
	<div class="large-19 columns">
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
		<h3>Nginx connections per IP</h3>
		<table class="tablesorter">
			<thead>
				<tr>
					<th>Number of requests</th>
					<th>IP</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach ($nginx_ips as $key => $value)
						echo '<tr><td>' . $value['count'] . '</td><td>' . $value['ip'] . '</td></tr>';
				?>
			</tbody>
		</table>
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
					<th>CPU (%)</th>
					<th>Memory (kb)</th>
				</tr>
			</thead>
			<tbody>
				<?php echo $workers_data; ?>
			</tbody>
		</table>
	</div>
</div>