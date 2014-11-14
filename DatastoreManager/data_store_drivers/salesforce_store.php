<?php defined('BASE_DIR') OR die('No direct access allowed.');

class salesforce_store extends data_store
{
	public function save()
	{
		$this->validate();
		
		$curl = new curl_wrapper;
		$curl->set_url($this->get_option('url'));
		$curl->set_method(curl_wrapper::POST);
		$curl->add_fields($this->_fields);
		
		$result = $curl->send();
		
		$last_result = $curl->get_last_result();
		
		if ($result && stristr($last_result, 'your request has been queued'))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	protected function validate()
	{
		parent::validate();
		
		if ($this->get_option('url') == '')
		{
			throw new data_store_exception('Error saving to datastore. URL must be set.');
		}
	}
}