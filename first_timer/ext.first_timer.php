<?php  

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
================================================================
	First Timer
	for EllisLab ExpressionEngine - by Ryan Irelan
----------------------------------------------------------------
	Copyright (c) 2009 Airbag Industries, LLC
================================================================
	THIS IS COPYRIGHTED SOFTWARE. PLEASE
	READ THE LICENSE AGREEMENT.
----------------------------------------------------------------
	This software is based upon and derived from
	EllisLab ExpressionEngine software protected under
	copyright dated 2005 - 2009. Please see
	http://expressionengine.com/docs/license.html
----------------------------------------------------------------
	USE THIS SOFTWARE AT YOUR OWN RISK. WE ASSUME
	NO WARRANTY OR LIABILITY FOR THIS SOFTWARE AS DETAILED
	IN THE LICENSE AGREEMENT.
================================================================
	File:			ext.first_timer.php
----------------------------------------------------------------
	Version:		1.1
----------------------------------------------------------------
	Purpose:		Lets you redirect a user to a specific page the first time they log in.
----------------------------------------------------------------
	Compatibility:	EE 1.5.2
----------------------------------------------------------------
	Created:		2009-11-01
================================================================
*/

// -----------------------------------------
//	Begin class
// -----------------------------------------

class First_timer_ext
{
    var $settings        = array();
    
    var $name            = 'First Timer';
    var $version         = '2.0';
    var $description     = 'Lets you redirect a user to a specific page the first time they log in.';
    var $settings_exist  = 'y';
    var $docs_url        = 'http://airbagindustries.com/software/ee/firsttimer/';
    

	 	// ------------------------------
		// Settings Pour mon Extension
		// ------------------------------
		
		function settings()
		{
			global $FNS;

			// set the base url so we can use it as the default for both fields
			$r = $FNS->create_url('');
			
			$settings = array();
			
			$settings['first_redirect'] = array('t', '', $r);
			$settings['normal_redirect'] = array('t', '', $r);
			$settings['site_id'] = array('t', '', $r); 
			
			return $settings;

		}


    // -------------------------------
    // Constructor
    // -------------------------------
    
    function First_timer_ext ( $settings='' )
    {
			// super object
			$this->EE =& get_instance();
			$this->settings = $settings;
    }
    // END

	// --------------------------------
	//  Activate Extension
	// --------------------------------

	function activate_extension()
	{

	    $this->EE->db->insert('exp_extensions',
	                                  array(
	                                        'extension_id' => '',
	                                        'class'        => get_class($this),
	                                        'method'       => "extension_method",
	                                        'hook'         => "member_member_login_single",
	                                        'settings'     => "",
	                                        'priority'     => 10,
	                                        'version'      => $this->version,
	                                        'enabled'      => "y"
	                                      )
	                                 );
	}
	
	// create new column in exp_members so we can track first timers
	
	// but first we need to check that the column doesn't already exist
	
	$column_check = $this->EE->db->query('SHOW COLUMNS FROM exp_members');
	
	$first_timer_exists = FALSE;
	
	foreach($column_check->result() as $column)
	{
		if ($column['Field'] == "first_time")
		{
			$first_timer_exists = TRUE;
		}
	}                              
	
	//if column doesn't already exist, let's create it
	
	if (! $first_timer_exists)
	{
		$this->EE->db->query("ALTER TABLE exp_members
													ADD COLUMN first_time INT(1) 
													DEFAULT 0");
	}
	
	// END
	
	// --------------------------------
	//  Do the redirect
	// --------------------------------

	function redirect_user()
	{
		// MSM support goes here (one day)
		
		// get the user that is logging in
		$this_user = $this->EE->session->userdata['member_id'];
		
		// check whether the user has logged in before
		$last_visit = $this->EE->db->select("SELECT first_time
																				 FROM exp_members
																				 WHERE member_id = $this_user");
		 
		foreach($last_visit->result() as $visit)
		{
			$last_visit = $visit['first_time'];
		}                                    
		
		if ($last_visit == 0)
		{
			$this->EE->db->query("UPDATE exp_members
														SET first_time = 1
														WHERE member_id = $this_user");

		// redirect based on the control panel setting
			$this->EE->functions->redirect($this->EE->settings['first_redirect']);
		}                                                           
		else
		{
			$this->EE->functions->redirect($this->EE->settings['normal_redirect']);
		}
}
	// END
	
	// --------------------------------
	//  Update Extension
	// --------------------------------  

	function update_extension ( $current='' )
	{
	    global $DB;

	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }

	    if ($current < '1.0.1')
	    {
	        // Update to next version
	    }

	    $DB->query("UPDATE exp_extensions 
	                SET version = '".$DB->escape_str($this->version)."' 
	                WHERE class = '".get_class($this)."'");
	}
	// END
	
	// --------------------------------
	//  Disable Extension
	// --------------------------------

	function disable_extension()
	{

	    $this->EE->db->query("DELETE FROM exp_extensions WHERE class = '".get_class($this)."'");
	}
	// END
}
// END CLASS