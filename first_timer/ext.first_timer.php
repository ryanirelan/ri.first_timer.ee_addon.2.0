<?php  

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * RI First Timer
 * 
 * An ExpressionEngine extensions that lets you redirect a user to a specific page the first time they log in.
 * 
 * @package				first_timer
 * @author				Ryan Irelan <ryan@mijingo.com>
 * @copyright			Copyright (c) 2010 Mijingo, LLC
 * @license				n/a
 * @link 					https://github.com/ryanirelan/ri.first_timer.ee_addon.2.
 * @since					Version 2.0
 *
 */

/**
 *  Changelog
 * 
 * Version 2.0 20100113
 * ---------------------
 * First public release
 */

// -----------------------------------------
//	Begin class
// -----------------------------------------

class First_timer_ext
{
		var $settings        = array();
    
		var $name            = 'First Timer';
		var $version         = '2.0.1';
		var $description     = 'Lets you redirect a user to a specific page the first time they log in.';
		var $settings_exist  = 'y';
		var $docs_url        = 'https://github.com/ryanirelan/ri.first_timer.ee_addon.2.0';
    

	 	// ------------------------------
		// Settings Pour mon Extension
		// ------------------------------
		
		function settings()
		{
			$this->EE->lang->loadfile('first_timer');
			
			$settings = array();
			
			// set the base url so we can use it as the default for both fields
			$r = $this->EE->functions->create_url('');
			$settings['first_redirect'] = array('i', '', $r);
			$settings['normal_redirect'] = array('i', '', $r);
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
				'method'       => "redirect_user",
				'hook'         => "member_member_login_single",
				'settings'     => "",
				'priority'     => 10,
				'version'      => $this->version,
				'enabled'      => "y"
				)
			); 
			
			// create new column in exp_members so we can track first timers
	
			// but first we need to check that the column doesn't already exist
	
			$column_check = $this->EE->db->query('SHOW COLUMNS FROM exp_members');
	
			$first_timer_exists = FALSE;
	
			foreach($column_check->result() as $column)
			{
				if ($column->Field == "first_time")
				{
					$first_timer_exists = TRUE;
				}
			}                              
	
			//if column doesn't already exist, let's create it
	
			if (! $first_timer_exists)
			{
				$this->EE->db->query("ALTER TABLE exp_members ADD COLUMN first_time INT(1) DEFAULT 0");
			}
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
		
			$this->EE->db->select('first_time')->from('exp_members')->where('member_id', $this_user);
			$last_visit = $this->EE->db->get();		
		 
			foreach($last_visit->result() as $visit)
			{
				$last_visit = $visit->first_time;
			}                                    
				
			if ($last_visit == 0)
			{
				$this->EE->db->where('member_id', $this_user);
				$this->EE->db->update('exp_members', array('first_time' => 1));

				// redirect based on the control panel setting
				$this->EE->functions->redirect($this->settings['first_redirect']);
			}                                                           
			else
			{
				$this->EE->functions->redirect($this->settings['normal_redirect']);
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

			if ($current < '2.0.1')
			{
				// Update to next version
			}

			$DB->query("UPDATE exp_extensions SET version = '".$DB->escape_str($this->version)."' WHERE class = '".get_class($this)."'");
		}
		// END
	
		// --------------------------------
		//  Disable Extension
		// --------------------------------

		function disable_extension()
		{

			$this->EE->db->delete('exp_extensions', array('class' => get_class($this)));
		}
		// END
}
// END CLASS

/* End of file ext.first_timer.php */
/* Location: ./system/expressionengine/third_party/first_timer/ext.first_timer.php */