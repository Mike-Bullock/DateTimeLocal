<?
require("DateTimeLocal.php");

$dtNow = new DateTimeLocal();

echo $dtNow->format("Ymd H:i:s.u") . "\n";
echo "Local Timezone: " . $dtNow->getLocalTimezone() , "\n";
echo "Timezone Offset: " . ($dtNow->getOffset()/3600) . "\n";

?>
