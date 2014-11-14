<?php

class Customer
{
	private $information;
	
	public function create($first, $last, $address)
	{
		$args = func_get_args();
		echo "Hey! You called Customer::create with the following parameters: " . implode(", ", $args);
	}
}
