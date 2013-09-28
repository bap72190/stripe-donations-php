<?
header("HTTP/1.1 301 Moved Permanently");
header("Location: http://" . $_SERVER["SERVER_NAME"]);
?>