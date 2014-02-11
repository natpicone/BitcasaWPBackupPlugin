<html><head></head><body>
<?php
include_once("../BitcasaClient.php");
include_once("config.php");

$client = new BitcasaClient();
//$client->setAccessToken($_GET["access_token"]);
$client->setAccessTokenFromRequest();


/*
 * EXAMPLE 1 - listing the contents of the Bitcasa Infinite Drive
 */
echo "<h2>Example 1 - list infinite drive</h2>";


try {
	$items = BitcasaInfiniteDrive::listAll($client);
}
catch (Exception $ex) {
	var_dump($ex);
	die("example 1 failed");
}

echo "<table border=1>";
?>
<tr><th>Name</th><th>Type</th><th>Category</th><th>UUID Path</th></tr>
<?php
foreach ($items as $key => $item) {
	echo "<tr><td>" . $item->getName()
		. "</td><td>" . $item->getType()
		. "</td><td>" . $item->getCategory()
		. "</td><td>" . $item->getPath() . "</td></tr>";
}

echo "</table>";

/*
 * EXAMPLE 2 - listing the contents of the Bitcasa Mirrors (device list)
 */

echo "<h2>Example 2 - list mirrored folders</h2>";

$items = BitcasaMirrors::listAll($client);

echo "<table border=1>";
?>
<tr><th>Name</th><th>Type</th><th>Category</th><th>Device</th></tr>
<?php
foreach ($items as $key => $item) {
	echo "<tr><td>" . $item->getName()
		. "</td><td>" . $item->getType()
		. "</td><td>" . $item->getCategory()
		. "</td><td>" . $item->getOriginDevice() . "</td></tr>";
}

echo "</table>";

/*
 * EXAMPLE 3 - add folder to BitcasaInfinteDrive
 */

echo "<h2>Example 3 - add a folder</h2>";

$bid = $client->getInfiniteDrive();
$item = $bid->add($client, "wordpress_backup");

echo "<table border=1>";
?>
<tr><th>Name</th><th>Mtime</th><th>type</th><th>Path</th></tr>
<?php
echo "<tr><td>" . $item->getName()
. "</td><td>" . $item->getMTime()
. "</td><td>" . $item->getType()
. "</td><td>" . $item->getPath() . "</td></tr>";

echo "</table>";





/*
 * EXAMPLE 6 - upload a file from BitcasaInfinteDrive
 */
echo "<h2>Example 6 - upload a file</h2>";

try {
	$bid = BitcasaInfiniteDrive::getInfiniteDrive($client);
	//$result = $bid->upload($client, "/etc/hosts", "UpFile.pdf");
	$result = $bid->upload($client, "/home/demo/public_html/bitcasa_jonchang", "readme.html");
}

catch (Exception $ex) {
	var_dump($ex);
}

echo "<table border=1>";





 
?>
<tr><th>Name</th><th>Mtime</th><th>type</th><th>Path</th></tr>

<?php
echo "<tr><td>" . $result->getName()
. "</td><td>" . $result->getMTime()
. "</td><td>" . $result->getType()
. "</td><td>" . $result->getPath() . "</td></tr>";

echo "</table>";

?>
</body></html>
