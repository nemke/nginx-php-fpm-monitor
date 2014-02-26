<?php

	class SystemInfo
	{
		const MEMORY_INFORMATION = 'memory_information';

		private $size_units = array('KB', 'MB', 'GB', 'TB', 'PB');
		private $properties;

		public function __construct()
		{
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

		public function GetLoad()
		{
			if(function_exists('sys_getloadavg') === FALSE)
				return 'Can\'t get load data.';

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

// END