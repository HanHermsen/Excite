<!doctype html>
<html>
<head>
</head>
<body>


<?php

	$userAgent = strtolower ($_SERVER['HTTP_USER_AGENT']);

	if ( preg_match('/.*android/' , $userAgent) )
	{
		echo <<< EOS
		$userAgent
EOS;
	}
	else
		echo "nee hoor"
	
?>

</body>
</html>