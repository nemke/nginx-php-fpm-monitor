<?php

	class NginxInfo
	{
		const NGINX_STATUS_PAGE = 'nginx_status_page';

		private $properties;
		private $curl_connection;

		public function __construct()
		{
			// Init cURL object
			$this->curl_connection = curl_init();

			// Usually admins use self signed SSL certs
			curl_setopt($this->curl_connection, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($this->curl_connection, CURLOPT_SSL_VERIFYPEER, FALSE);
		}

		/**
		* GetProperties - Main Getter function
		*
		* @return void
		* @author Nemanja Andrejevic
		*/
		public function GetProperties($props)
		{
			// In case properties is array, get array of properties
			if(is_array($props))
			{
				foreach($props as $name)
					$result[$name] = $this->properties[$name];
			}
			else
				$result = $this->properties[$props];

			return $result;
		}

		/**
		* SetProperties - Main Setter function
		*
		* @return void
		* @author Nemanja Andrejevic
		*/
		public function SetProperties($property, $value = '')
		{
			// In case properties is array, set array of properties
			if(is_array($property))
			{
				foreach($property as $key => $value)
					$this->properties[$key] = $value;
			}
			else
				$this->properties[$property] = $value;
		}

		public function GetNginxData()
		{
			try
			{
				$nginx_data = array();

				// Setting cURL data
				curl_setopt($this->curl_connection, CURLOPT_URL, $this->properties[self::NGINX_STATUS_PAGE]);
				curl_setopt($this->curl_connection, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($this->curl_connection, CURLOPT_TIMEOUT, 15);

				// Executing URL request
				$response_text = curl_exec($this->curl_connection);

				// Checking if there was any errors
				if($response_text === FALSE)
					throw new Exception(curl_error($this->curl_connection), curl_errno($this->curl_connection));

				$response_text = explode("\n", $response_text);

				$response_text[0] = explode(':', $response_text[0]);
				$nginx_data['active_connections'] = (int) $response_text[0][1];

				$response_text[2] = explode(' ', trim($response_text[2]));
				$nginx_data['total_accepted_connections'] = $response_text[2][0];
				$nginx_data['total_handled_connections'] = $response_text[2][0];
				$nginx_data['total_requests'] = $response_text[2][0];
				$nginx_data['requests_per_connection'] = number_format($nginx_data['total_requests'] / $nginx_data['total_handled_connections'], 2);

				$response_text[3] = explode(' ', trim($response_text[3]));
				$nginx_data['reading'] = (int) $response_text[3][1];
				$nginx_data['writing'] = (int) $response_text[3][3];
				$nginx_data['waiting'] = (int) $response_text[3][5];

				return $nginx_data;
			}
			catch(Exception $e)
			{
				return FALSE;
			}
		}

		public function NginxConnectionsPerIP() 
		{
			$connections = shell_exec('netstat -taupen | grep nginx');
			$connections = explode("\n", $connections);
			$ips = array();
			$ip_count_column = array();

			foreach($connections as $key => $data)
			{
				$connections[$key] = explode('+++', preg_replace("/\s+/", "+++", $data));

				if(!isset($connections[$key][4]) || $connections[$key][4] == '0.0.0.0:*')
					continue;

				$ip = explode(':', $connections[$key][4]);
				$port = $ip[1];
				$ip = $ip[0];

				if(isset($ips[$ip]))
					$ips[$ip]['count']++;
				else
					$ips[$ip] = array('ip' => $ip, 'count' => 1);

				$ip_count_column[$ip] = $ips[$ip]['count'];
			}

			array_multisort($ip_count_column, SORT_DESC, $ips);

			return $ips;
		}

		public function PHPRamInfo()
		{
			$processes = shell_exec('ps aux | grep php-fpm');
			$processes = explode("\n", $processes);

			$totals = array(
				'average_ram' => 0,
				'number_of_processes' => 0,
			);

			foreach($processes as $key => $data)
			{
				if(mb_strpos($data, 'grep') !== FALSE)
					continue;

				$data = explode('+++', preg_replace("/\s+/", "+++", $data));

				if(!isset($data[5]))
					continue;

				$totals['average_ram'] += $data[5];
				$totals['number_of_processes']++;
			}

			$totals['average_ram'] = $totals['average_ram'] / $totals['number_of_processes'];

			$size_unit = 0;

			while ($totals['average_ram'] > 1024)
			{
				$totals['average_ram'] = $totals['average_ram'] / 1024;
				$size_unit++;
			}

			$totals['average_ram'] = round($totals['average_ram'], 1) . ' ' . $this->size_units[$size_unit];

			return $totals;
		}
	}

// END