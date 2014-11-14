<?php

class Product
{
	public $name = 'Test Product';
	private $id = '123';
	
	public function edit($id)
	{
		echo "Hey! You called Product::edit with ID " . $id;
	}
}
