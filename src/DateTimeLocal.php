<?
class DateTimeLocal extends DateTime {
	private $LocalTimezone;

	public function __construct($time = "now") {
		// Get local timezone in linux format.
		$LinuxTZ = $this->getLocalLinuxTimezone();

		$this->LocalTimezone = $LinuxTZ;

		if ($time == "now") {
			// Get local time in microseconds (Source: https://stackoverflow.com/questions/33691428/datetime-with-microseconds)
			$t = microtime(true);
			$micro = sprintf("%06d",($t - floor($t)) * 1000000);
			parent::__construct(date('Y-m-d H:i:s.'.$micro, $t));
		} else { // using time passed to DateTimeLocal
			parent::__construct($time);
		}

		// set timezone to local time.
		$this->setTimezone(new DateTimeZone($LinuxTZ));
	}

	public function getLocalTimezone() {
		return $this->LocalTimezone;
	}


	private function getLocalLinuxTimezone() {

		if (PHP_OS == "WINNT") { // Windows
			// execute tzutil to get local timezone
			exec("tzutil /g", $CmdOutputArray, $CmdRetVal);
			if ($CmdRetVal != 0) { // Error!
				throw new Exception("tzutil returned non-zero '$CmdRetVal'");
				return false;
			}

			if (!isset($CmdOutputArray[0])) {
				throw new Exception("Cannot parse tzutil output");
				return false;
			}

			$LocalWindowsTZ = $CmdOutputArray[0];

			$LinuxTZ = $this->WinToLinuxTZ($LocalWindowsTZ);

			if ($LinuxTZ == false) {
				throw new Exception("Unknown windows timezone '$LocalWindowsTZ'");
				return false;
			}
			return $LinuxTZ;
		} else if (!strcasecmp(PHP_OS,"LINUX")) { // Linux

			// see if /etc/timezone exists
			if (is_file("/etc/timezone")) {
				$LinuxTZ = @file_get_contents("/etc/timezone");
				if ($LinuxTZ !== false) {
					$LinuxTZ = trim(rtrim($LinuxTZ));
					if (strlen($LinuxTZ) > 0) {
						return $LinuxTZ;
					}
				}
			}

			// See if timedatectl exists and extract timezone
			if (!empty(shell_exec("which timedatectl"))) {
				exec("timedatectl", $CmdOutputArray, $CmdRetVal);
				if ($CmdRetVal == 0) { // timedatectl success
					foreach ($CmdOutputArray as $CmdLine) {
						$CmdLine = trim(rtrim($CmdLine));
						$ret = preg_match("/Time zone: (.+)\/(.+) \(/", $CmdLine, $matches); // extract timezone
						if ($ret && (count($matches) == 3)) {
							return $matches[1] . "/" . $matches[2];
						}
					}
				}
			}

			throw new Exception("No method found to extract local timezone");
		} else {
			throw new Exception("Unhandled OS '" . PHP_OS . "'");
		}
	}
	private function WinToLinuxTZ($WinTZ) {
		// Source: https://gist.github.com/dejanstojanovic/75808fe04453988bd2960707a6ff7e4a
		// Converted from json to PHP array
		$WinToLinuxTZArray = array(
                   "Dateline Standard Time"             => "Etc/GMT+12",
		   "UTC-11"         			=> "Etc/GMT+11",
		   "Aleutian Standard Time"         	=> "America/Adak",
		   "Hawaiian Standard Time"         	=> "Pacific/Honolulu",
		   "Marquesas Standard Time"        	=> "Pacific/Marquesas",
		   "Alaskan Standard Time"  		=> "America/Anchorage",
		   "UTC-09"         			=> "Etc/GMT+9",
		   "Pacific Standard Time (Mexico)"     => "America/Tijuana",
		   "UTC-08"        			=> "Etc/GMT+8",
		   "Pacific Standard Time"  		=> "America/Los_Angeles",
		   "US Mountain Standard Time"      	=> "America/Phoenix",
		   "Mountain Standard Time (Mexico)"    => "America/Chihuahua",
		   "Mountain Standard Time"         	=> "America/Denver",
		   "Central America Standard Time"  	=> "America/Guatemala",
		   "Central Standard Time"  		=> "America/Chicago",
		   "Easter Island Standard Time"   	=> "Pacific/Easter",
		   "Central Standard Time (Mexico)"     => "America/Mexico_City",
		   "Canada Central Standard Time"   	=> "America/Regina",
		   "SA Pacific Standard Time"       	=> "America/Bogota",
		   "Eastern Standard Time (Mexico)"     => "America/Cancun",
		   "Eastern Standard Time"  		=> "America/New_York",
		   "Haiti Standard Time"    		=> "America/Port-au-Prince",
		   "Cuba Standard Time"     		=> "America/Havana",
		   "US Eastern Standard Time"       	=> "America/Indiana/Indianapolis",
		   "Turks And Caicos Standard Time"     => "America/Grand_Turk",
		   "Paraguay Standard Time"         	=> "America/Asuncion",
		   "Atlantic Standard Time"         	=> "America/Halifax",
		   "Venezuela Standard Time"        	=> "America/Caracas",
		   "Central Brazilian Standard Time"    => "America/Cuiaba",
		   "SA Western Standard Time"       	=> "America/La_Paz",
		   "Pacific SA Standard Time"       	=> "America/Santiago",
		   "Newfoundland Standard Time"     	=> "America/St_Johns",
		   "Tocantins Standard Time"        	=> "America/Araguaina",
		   "E. South America Standard Time"     > "America/Sao_Paulo",
		   "SA Eastern Standard Time"       	=> "America/Cayenne",
		   "Argentina Standard Time"        	=> "America/Argentina/Buenos_Aires",
		   "Greenland Standard Time"        	=> "America/Godthab",
		   "Montevideo Standard Time"       	=> "America/Montevideo",
		   "Magallanes Standard Time"       	=> "America/Punta_Arenas",
		   "Saint Pierre Standard Time"     	=> "America/Miquelon",
		   "Bahia Standard Time"    		=> "America/Bahia",
		   "UTC-02"         			=> "Etc/GMT+2",
		   "Mid-Atlantic Standard Time"     	=> "Etc/GMT+2",
		   "Azores Standard Time"   		=> "Atlantic/Azores",
		   "Cape Verde Standard Time"       	=> "Atlantic/Cape_Verde",
		   "UTC"    				=> "Etc/UTC",
		   "Morocco Standard Time"  		=> "Africa/Casablanca",
		   "GMT Standard Time"      		=> "Europe/London",
		   "Greenwich Standard Time"        	=> "Atlantic/Reykjavik",
		   "W. Europe Standard Time"        	=> "Europe/Berlin",
		   "Central Europe Standard Time"   	=> "Europe/Budapest",
		   "Romance Standard Time"  		=> "Europe/Paris",
		   "Sao Tome Standard Time"         	=> "Africa/Sao_Tome",
		   "Central European Standard Time"     => "Europe/Warsaw",
		   "W. Central Africa Standard Time"    => "Africa/Lagos",
		   "Jordan Standard Time"   		=> "Asia/Amman",
		   "GTB Standard Time"      		=> "Europe/Bucharest",
		   "Middle East Standard Time"      	=> "Asia/Beirut",
		   "Egypt Standard Time"    		=> "Africa/Cairo",
		   "E. Europe Standard Time"        	=> "Europe/Chisinau",
		   "Syria Standard Time"    		=> "Asia/Damascus",
		   "West Bank Standard Time"        	=> "Asia/Hebron",
		   "South Africa Standard Time"     	=> "Africa/Johannesburg",
		   "FLE Standard Time"      		=> "Europe/Kiev",
		   "Israel Standard Time"   		=> "Asia/Jerusalem",
		   "Kaliningrad Standard Time"      	=> "Europe/Kaliningrad",
		   "Sudan Standard Time"    		=> "Africa/Khartoum",
		   "Libya Standard Time"    		=> "Africa/Tripoli",
		   "Namibia Standard Time"  		=> "Africa/Windhoek",
		   "Arabic Standard Time"   		=> "Asia/Baghdad",
		   "Turkey Standard Time"   		=> "Europe/Istanbul",
		   "Arab Standard Time"     		=> "Asia/Riyadh",
		   "Belarus Standard Time"  		=> "Europe/Minsk",
		   "Russian Standard Time"  		=> "Europe/Moscow",
		   "E. Africa Standard Time"        	=> "Africa/Nairobi",
		   "Iran Standard Time"     		=> "Asia/Tehran",
		   "Arabian Standard Time"  		=> "Asia/Dubai",
		   "Astrakhan Standard Time"        	=> "Europe/Astrakhan",
		   "Azerbaijan Standard Time"       	=> "Asia/Baku",
		   "Russia Time Zone 3"     		=> "Europe/Samara",
		   "Mauritius Standard Time"        	=> "Indian/Mauritius",
		   "Saratov Standard Time"  		=> "Europe/Saratov",
		   "Georgian Standard Time"         	=> "Asia/Tbilisi",
		   "Caucasus Standard Time"         	=> "Asia/Yerevan",
		   "Afghanistan Standard Time"      	=> "Asia/Kabul",
		   "West Asia Standard Time"        	=> "Asia/Tashkent",
		   "Ekaterinburg Standard Time"     	=> "Asia/Yekaterinburg",
		   "Pakistan Standard Time"         	=> "Asia/Karachi",
		   "India Standard Time"    		=> "Asia/Kolkata",
		   "Sri Lanka Standard Time"        	=> "Asia/Colombo",
		   "Nepal Standard Time"    		=> "Asia/Kathmandu",
		   "Central Asia Standard Time"     	=> "Asia/Almaty",
		   "Bangladesh Standard Time"       	=> "Asia/Dhaka",
		   "Omsk Standard Time"     		=> "Asia/Omsk",
		   "Myanmar Standard Time"  		=> "Asia/Yangon",
		   "SE Asia Standard Time"  		=> "Asia/Bangkok",
		   "Altai Standard Time"    				    => "Asia/Barnaul",
								   "W. Mongolia Standard Time"  		    => "Asia/Hovd",
								   "North Asia Standard Time"   		    => "Asia/Krasnoyarsk",
								   "N. Central Asia Standard Time"			=> "Asia/Novosibirsk",
								   "Tomsk Standard Time"    			    	=> "Asia/Tomsk",
								   "China Standard Time"    			    	=> "Asia/Shanghai",
								   "North Asia East Standard Time"  		=> "Asia/Irkutsk",
								   "Singapore Standard Time"        		=> "Asia/Singapore",
								   "W. Australia Standard Time"     		=> "Australia/Perth",
								   "Taipei Standard Time"  					    => "Asia/Taipei",
								   "Ulaanbaatar Standard Time"      		=> "Asia/Ulaanbaatar",
								   "North Korea Standard Time"      		=> "Asia/Pyongyang",
								   "Aus Central W. Standard Time"   		=> "Australia/Eucla",
								   "Transbaikal Standard Time"      		=> "Asia/Chita",
								   "Tokyo Standard Time"    			    	=> "Asia/Tokyo",
								   "Korea Standard Time"    			    	=> "Asia/Seoul",
								   "Yakutsk Standard Time"  			    	=> "Asia/Yakutsk",
								   "Cen. Australia Standard Time"   		=> "Australia/Adelaide",
								   "AUS Central Standard Time"      		=> "Australia/Darwin",
								   "E. Australia Standard Time"     		=> "Australia/Brisbane",
								   "AUS Eastern Standard Time"      		=> "Australia/Sydney",
								   "West Pacific Standard Time"     		=> "Pacific/Port_Moresby",
								   "Tasmania Standard Time"         		=> "Australia/Hobart",
								   "Vladivostok Standard Time"      		=> "Asia/Vladivostok",
								   "Lord Howe Standard Time"        		=> "Australia/Lord_Howe",
								   "Bougainville Standard Time"     		=> "Pacific/Bougainville",
								   "Russia Time Zone 10"    			    	=> "Asia/Srednekolymsk",
								   "Magadan Standard Time"  			    	=> "Asia/Magadan",
								   "Norfolk Standard Time"  				    => "Pacific/Norfolk",
								   "Sakhalin Standard Time"         		=> "Asia/Sakhalin",
								   "Central Pacific Standard Time"  		=> "Pacific/Guadalcanal",
								   "Russia Time Zone 11"    			    	=> "Asia/Kamchatka",
								   "New Zealand Standard Time"      		=> "Pacific/Auckland",
								   "UTC+12"         						        => "Etc/GMT-12",
								   "Fiji Standard Time"     			    	=> "Pacific/Fiji",
								   "Kamchatka Standard Time"        		=> "Asia/Kamchatka",
								   "Chatham Islands Standard Time"  		=> "Pacific/Chatham",
								   "UTC+13"         						        => "Etc/GMT-13",
								   "Tonga Standard Time"					      => "Pacific/Tongatapu",
								   "Samoa Standard Time"					      => "Pacific/Apia",
								   "Line Islands Standard Time"			  	=> "Pacific/Kiritimati");

		if(!isset($WinToLinuxTZArray[$WinTZ])) return false;
		return $WinToLinuxTZArray[$WinTZ];
	}
}
?>
