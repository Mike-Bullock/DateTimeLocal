# DateTimeLocal
Extension of the PHP DateTime class for determining the host local timezone

This PHP code extends the native PHP DateTime class to automatically set the returned object to the hosts local timezone. It is cross platform to both Windows and Linux. Access to exec() is needed for Windows as it uses the command-line tzutil.exe to determine the hosts timezone. 

Linux implementation uses the /etc/timezone file and if not available, timedatectl (may have to install systemd-services package).

Extends all DateTime class functions. 
Added function getLocalTimezone() which returns local timezone.

Simple test.php
  <pre>
  require("DateTimeLocal.php");

  $dtNow = new DateTimeLocal();

  echo $dtNow->format("Ymd H:i:s.u") . "\n";
  echo "Local Timezone: " . $dtNow->getLocalTimezone() , "\n";
  echo "Timezone Offset: " . ($dtNow->getOffset()/3600) . "\n";
  </pre>
Returned data: 
  <pre>
  php test.php 
  20210212 13:17:05.370913
  Local Timezone: America/New_York
  Timezone Offset: -5
  </pre>
