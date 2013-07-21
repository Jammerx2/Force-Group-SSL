<?php
	
	/**
	*	Force Group SSL
	*
	*	Will force specified groups to use SSL.
	*	Created by Josh Medeiros (Jammerx2)
	*	http://www.joshmedeiros.net/
	*/
	
	/**
	*	Add plugin hooks.
	*/
	$plugins->add_hook('global_start', 'force_group_ssl_global_start');
	$plugins->add_hook('admin_user_groups_edit', 'force_group_ssl_usergroup');
	$plugins->add_hook('admin_user_groups_edit_commit', 'force_group_ssl_usergroup_commit');
	
	/**
	*	Returns the plugin information.
	*
	*	@return mixed An array of information about the plugin.
	*/
	function force_group_ssl_info()
	{
		global $lang;
		
		$lang->load('force_group_ssl');
		
		return array(
			"name"			=> $lang->force_group_ssl,
			"description"	=> $lang->force_group_ssl_desc,
			"website"		=> "http://www.joshmedeiros.net",
			"author"		=> "Josh Medeiros (Jammerx2)",
			"authorsite"	=> "http://www.joshmedeiros.net",
			"version"		=> "1.0",
			"compatibility" => "16*"
		);
	}
	
	/**
	*	Creates necessary modifications for the plugin.
	*/
	function force_group_ssl_activate()
	{
		global $db, $cache;
		
		if(!$db->field_exists("forcessl", "usergroups"))
		{
			$db->query("ALTER TABLE ".TABLE_PREFIX."usergroups ADD forcessl INT(1) NOT NULL DEFAULT 1");
		}
		
		$cache->update_usergroups();
	}
	
	/**
	*	Removes modifications created by the plugin.
	*/
	function force_group_ssl_deactivate()
	{
		global $db, $cache;
		
		if($db->field_exists("forcessl", "usergroups"))
		{
			$db->query("ALTER TABLE ".TABLE_PREFIX."usergroups DROP forcessl");
		}
		
		$cache->update_usergroups();
	}
	
	/**
	 *  Forces specified usergroups to use SSL.
	 */
	function force_group_ssl_global_start()
	{
		global $mybb;
		
		if($mybb->usergroup['forcessl'] == 1 && ($_SERVER['HTTPS'] != "on" && !(isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https'))))
		{
			$url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; 
			header("Location: $url"); 
			exit;
		}
		
		if($_SERVER['HTTPS'] == "on")
		{
			$mybb->settings['bburl'] = preg_replace("/^http:\/\//i", "https://", $mybb->settings['bburl']);
		}
	}
	
	/**
	 *  Hook the output of the row.
	 */
	function force_group_ssl_usergroup()
	{
		global $plugins;
		//Hook the individual row output
		$plugins->add_hook("admin_formcontainer_output_row", "force_group_ssl_row");
	}
	
	/**
	 *  Updates the usergroup setting in the database.
	 */
	function force_group_ssl_usergroup_commit()
	{
		global $mybb, $db, $usergroup;
		
		$db->update_query("usergroups", array("forcessl" => ($mybb->input['forcessl'] == 1 ? 1 : 0)), "gid='".$usergroup['gid']."'");
	}
	
	/**
	 *  Output options to user.
	 *  
	 *  @param mixed Plugin arguments.
	 */
	function force_group_ssl_row(&$pluginargs)
	{
		global $mybb, $lang, $form_container, $form, $usergroup;
		
		if($pluginargs['title'] == $lang->misc)
		{
			$lang->load("force_group_ssl");
			if(!isset($mybb->input['forcessl'])) $mybb->input['forcessl'] = $usergroup['forcessl'];
			$form_container->output_row($lang->force_group_ssl, "", "<div class=\"group_settings_bit\">".$form->generate_check_box("forcessl", 1, $lang->force_group_ssl_checkbox, array("checked" => $mybb->input['forcessl']))."</div>", "forcessl");
		}
	}
	
?>