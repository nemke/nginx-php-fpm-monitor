<?php

	class PhpCacheInfo
	{
		const MEMORY_TOTAL = 'memory_total';
		const MEMORY_FREE = 'memory_free';
		const OPCACHE_TYPE = 'opcache_type';
		const OPCACHE_HIT_RATE = 'opcache_hit_rate';

		private $size_units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

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

		public function GetOpcodeCacheData()
		{
			$opcode_data = FALSE;

			if(function_exists('opcache_get_status'))
			{
				$opcode_data = $this->GetZendOpCacheData();
			}

			return $opcode_data;
		}

		private function GetZendOpCacheData()
		{
			$opcache_data = opcache_get_status();
			$opcache_configuration = opcache_get_configuration();

			$status_data[self::OPCACHE_TYPE] = $opcache_configuration['version']['opcache_product_name'] . ' ' . $opcache_configuration['version']['version'];
			$status_data[self::OPCACHE_HIT_RATE] = round($opcache_data['opcache_statistics']['opcache_hit_rate'], 2);
			$status_data[self::MEMORY_TOTAL] = $this->CalculateSize($opcache_configuration['directives']['opcache.memory_consumption']);
			$status_data[self::MEMORY_FREE] = $this->CalculateSize($opcache_data['memory_usage']['free_memory']);

			return $status_data;
		}

		private function CalculateSize($size)
		{
			// Final totals calculations
			$size_unit = 0;

			while ($size > 1023)
			{
				$size = $size / 1024;
				$size_unit++;
			}

			return round($size, 1) . ' ' . $this->size_units[$size_unit];
		}
	}

// END