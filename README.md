# DateTimeLocal
Extension of the PHP DateTime class for Determining Host Local Timezone

This PHP code extends the native PHP DateTime class to automatically set the returned object to the hosts local timezone. It is cross platform to both Windows and Linux. Access to exec() is needed for Windows as it uses the command-line tzutil.exe to determine the hosts timezone. 

Linux implementation uses the /etc/timezone file. 
