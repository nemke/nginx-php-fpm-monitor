<?php

	class PhpFpmInfo
	{
		const MEMORY_INFORMATION = 'memory_information';
		const PHP_FPM_STATUS_PAGE = 'php_fpm_status_page';

		private $size_units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
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

		private function GetStatusPageData()
		{
			try
			{
				// Setting cURL data
				curl_setopt($this->curl_connection, CURLOPT_URL, $this->properties[self::PHP_FPM_STATUS_PAGE]);
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
			catch(Exception $e)
			{
				return FALSE;
 			}
		}

		public function GetPHPFPMData()
		{
			// Get PHP FPM status data
			$php_fpm_data = $this->GetStatusPageData();
			
			$php_data = array(
				'main_process_data' => '',
				'totals' => array(
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
				),
				'workers_data' => '',
				'requests_data' => '',
			);

			// PHP FPM Pools
			foreach ($php_fpm_data as $name => $value)
			{
				$row = '';
		
				if($name != 'processes')
				{
					switch ($name)
					{
						case 'start time':
							$row .= '<tr><td>Start time</td><td>' . strftime('%d/%m/%Y %H:%M:%S', $value) . '</td></tr>';
							$php_data['totals']['started'] = strftime('%d/%m/%Y %H:%M:%S', $value);
							break;
						case 'start since':
							$row .= '<tr><td>Start since</td><td>' . number_format($value / 60, 0) . ' m</td></tr>';
							break;
						case 'listen queue':
							$row .= '<tr><td>Listen queue</td><td>' . $value . '</td></tr>';
							$php_data['totals']['waiting_connections'] = $value;
							break;
						case 'total processes':
							$row .= '<tr><td>Total processes</td><td>' . $value . '</td></tr>';
							$php_data['totals']['workers'] = $value;
							break;
						default:
							$row .= '<tr><td>' . $name . '</td><td>' . $value . '</td></tr>';
							break;
					}

					$php_data['main_process_data'] .= $row;
				}
			}

			// Workers data
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
								$php_data['totals']['average_cpu'] += $value;
								$php_data['totals']['number_of_cpu_processes']++;
							}

							$workers_row .= '<td>' . $value . '</td>';
							break;
						case 'last request memory':
							if($value != 0)
							{
								$php_data['totals']['average_ram'] += $value;
								$php_data['totals']['number_of_ram_processes']++;
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
					$php_data['requests_data'] .= $requests_row . '</tr>';

					$php_data['totals']['number_of_requests']++;

					$url_md5 = md5($process['request uri']);

					if(isset($php_data['totals']['requests_by_uri'][$url_md5]))
						$php_data['totals']['requests_by_uri'][$url_md5]['counter']++;
					else
					{
						$php_data['totals']['requests_by_uri'][$url_md5] = array(
							'counter' => 1,
							'uri' => $process['request uri'],
						);
					}
				}

				$php_data['workers_data'] .= $workers_row . '</tr>';
			}

			// Final totals calculations
			$size_unit = 0;

			while ($php_data['totals']['average_ram'] > 1023)
			{
				$php_data['totals']['average_ram'] = $php_data['totals']['average_ram'] / 1024;
				$size_unit++;
			}

			$php_data['totals']['average_ram'] = round($php_data['totals']['average_ram'] / $php_data['totals']['number_of_ram_processes'], 1) . ' ' . $this->size_units[$size_unit];
			$php_data['totals']['average_cpu'] = round($php_data['totals']['average_cpu'] / $php_data['totals']['number_of_cpu_processes'], 1) . ' %';

			foreach($php_data['totals']['requests_by_uri'] as $md5_uri => $request_data)
				$php_data['totals']['requests_by_uri_string'] .= '<tr><td>' . $request_data['counter'] . '</td><td>' . $request_data['uri'] . '</td></tr>';

			return $php_data;
		}
	}

// END