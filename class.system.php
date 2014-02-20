<?php

	class SystemInfo
	{
		const MEMORY_INFORMATION = 'memory_information';

		private $size_units = array('KB', 'MB', 'GB', 'PB');
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

		public function GetNginxData($nginx_url)
		{
			$nginx_data = array();

			// Setting cURL data
			curl_setopt($this->curl_connection, CURLOPT_URL, $nginx_url);
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

		public function GetPHPFPMData($php_fpm_url)
		{
			// Setting cURL data
			curl_setopt($this->curl_connection, CURLOPT_URL, $php_fpm_url);
			curl_setopt($this->curl_connection, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($this->curl_connection, CURLOPT_TIMEOUT, 15);

			// Executing URL request
			$response_text = curl_exec($this->curl_connection);

			// Checking if there was any errors
			if($response_text === FALSE)
				throw new Exception(curl_error($this->curl_connection), curl_errno($this->curl_connection));

			// Decoding json data
			$php_fpm_data = json_decode(utf8_encode($response_text), TRUE);

			return $php_fpm_data;
		}

		public function GetLoad()
		{
			$load = sys_getloadavg();

			$load_string = number_format($load[0], 2) . ', ' . number_format($load[1], 2) . ', ' . number_format($load[2], 2);

			return $load_string;
		}

		public function GetUptime()
		{
			$file = fopen('/proc/uptime', 'r');

			if (!$file)
				return FALSE;

			$data = fread($file, 128);

			if ($data === false)
				return FALSE;

			$upsecs = (int) mb_substr($data, 0, mb_strpos($data, ' '));
			$uptime = array ( 'days' => floor($data/60/60/24), 'hours' => $data/60/60%24, 'minutes' => $data/60%60, 'seconds' => $data%60 );

			if($uptime['days'] > 0)
				$uptime_string = $uptime['days'] . ' days ' . $uptime['hours'] . ' hours ' . $uptime['minutes'] . ' min ' . $uptime['seconds'] . ' sec';
			elseif($uptime['days'] == 0)
				$uptime_string = $uptime['hours'] . ' hours ' . $uptime['minutes'] . ' min ' . $uptime['seconds'] . ' sec';			
			elseif($uptime['hours'] == 0)
				$uptime_string = $uptime['minutes'] . ' min ' . $uptime['seconds'] . ' sec';
			else
				$uptime_string = $uptime['seconds'] . ' sec';

			return $uptime_string;
		}

		public function GetMemoryInfo() 
		{
			$meminfo = array();
			$data = explode("\n", file_get_contents("/proc/meminfo"));

			if (empty($data))
				return FALSE;

			foreach ($data as $line)
			{
				if(empty($line))
					continue;

				list($key, $val) = explode(":", $line);

				$val = (int) $val;
				$size_unit = 0;

				while ($val > 1024)
				{
					$val = $val / 1024;
					$size_unit++;
				}

				$meminfo[$key] = round($val, 1) . ' ' . $this->size_units[$size_unit];
			}

			$this->properties[self::MEMORY_INFORMATION] = $meminfo;

			return $meminfo;
		}
	}

