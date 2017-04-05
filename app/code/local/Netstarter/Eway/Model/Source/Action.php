<?php

class Netstarter_Eway_Model_Source_Action
{
	public function toOptionArray()
	{
		return array(
			array(
				'value' => 'authorize_capture',
				'label' => 'Authorise and Capture'
			),
			array(
				'value' => 'authorize',
				'label' => 'Authorise'
			)
		);
	}
}