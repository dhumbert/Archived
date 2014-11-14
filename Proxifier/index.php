<?php

function __autoload($class)
{
	require_once($class . '.class.php');
}


$l = new FileLogger('/tmp/log.txt');

$p = new Product();
$pProxy = new Proxifier($p, $l);
$pProxy->edit(1);

echo '<br />';

$c = new Customer();
$cProxy = new Proxifier($c, $l);

$cProxy->create("Devin", "Humbert", "1 Main St.");
