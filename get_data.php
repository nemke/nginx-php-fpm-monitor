<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 'On');

	require_once 'config.php';
	require_once 'class.system.php';
	require_once 'class.nginx.php';
	require_once 'class.php.fpm.php';

	$system_info = new SystemInfo();
	$php_fpm_info = new PhpFpmInfo();
	$nginx_info = new NginxInfo();

	// Get Nginx statistics
	$nginx_info->SetProperties(NginxInfo::NGINX_STATUS_PAGE, $nginx_url);
	$nginx_data = $nginx_info->GetStatistics();
	$nginx_ips = $nginx_info->GetConnectionsPerIP();
	$nginx_sys_res = $nginx_info->GetSystemResources();

	// Get PHP FPM statistics
	$php_fpm_info->SetProperties(PhpFpmInfo::PHP_FPM_STATUS_PAGE, $php_fpm_url);
	$php_fpm_data = $php_fpm_info->GetPHPFPMData();

	// Get System Stats
	$system_load = $system_info->GetLoad();
	$system_uptime = $system_info->GetUptime();
	$memory_info = $system_info->GetMemoryInfo();
?>

<div class="row">
	<div class="large-5 columns">
		<h3>Server</h3>
		<table class="totals main-stats">
			<tbody>
				<tr><td>Load</td><td><?php echo $system_load; ?></td></tr>
				<tr><td>Uptime</td><td><?php echo $system_uptime; ?></td></tr>
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
				<tr><td>Total RAM usage</td><td><?php echo $nginx_sys_res['total_ram']; ?></td></tr>
				<tr><td>Total CPU usage</td><td><?php echo $nginx_sys_res['total_cpu']; ?></td></tr>
			</tbody>
		</table>
		<h3>PHP FPM Totals</h3>
		<table class="totals main-stats">
			<tbody>
				<tr><td>Number of requests</td><td><?php echo $php_fpm_data['totals']['number_of_requests']; ?></td></tr>
				<tr><td>Started</td><td><?php echo $php_fpm_data['totals']['started']; ?></td></tr>
				<tr><td>Waiting connections</td><td><?php echo $php_fpm_data['totals']['waiting_connections']; ?></td></tr>
				<tr><td>Workers</td><td><?php echo $php_fpm_data['totals']['workers']; ?></td></tr>
				<tr><td>Worker Average CPU</td><td><?php echo $php_fpm_data['totals']['average_cpu'] ?></td></tr>
				<tr><td>Worker Average RAM</td><td><?php echo $php_fpm_data['totals']['average_ram'] ?></td></tr>
				<tr><td>Worker Average Duration</td><td><?php echo $php_fpm_data['totals']['average_duration'] ?></td></tr>
			</tbody>
		</table>
		<h3>PHP FPM Pools</h3>
		<table class="totals main-stats">
			<tbody>
				<?php echo $php_fpm_data['main_process_data']; ?>
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
				<?php echo $php_fpm_data['requests_data']; ?>
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
				<?php echo $php_fpm_data['totals']['requests_by_uri_string'] ?>
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
				<?php echo $php_fpm_data['workers_data']; ?>
			</tbody>
		</table>
	</div>
</div>