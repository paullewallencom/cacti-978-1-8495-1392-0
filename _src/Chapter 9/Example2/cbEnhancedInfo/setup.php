<?php
/*******************************************************************************

 File:         setup.php
 Author:       Thomas Urban
 Language:     PHP
 Encoding:     UTF-8
 Status:       -
 License:      GPLv2
 
*******************************************************************************/

function plugin_cbEnhancedInfo_install () {
    api_plugin_register_hook('cbEnhancedInfo',
			     'draw_navigation_text',
			     'cbEnhancedInfo_draw_navigation_text',
			     'setup.php');
    api_plugin_register_hook('cbEnhancedInfo',
			     'config_arrays', 
			     'cbEnhancedInfo_config_arrays',
			     'setup.php');
    api_plugin_register_hook('cbEnhancedInfo',
			     'config_settings',
			     'cbEnhancedInfo_config_settings',
			     'setup.php');
    api_plugin_register_hook('cbEnhancedInfo',
			     'config_form',
			     'cbEnhancedInfo_config_form',
			     'setup.php');
    api_plugin_register_hook('cbEnhancedInfo',
			     'console_after',
			     'cbEnhancedInfo_console_after',
			     'setup.php');
    api_plugin_register_hook('cbEnhancedInfo',
			     'tree_after',
			     'cbEnhancedInfo_tree_after',
			     'setup.php');
    api_plugin_register_hook('cbEnhancedInfo',
			     'api_device_save',
			     'cbEnhancedInfo_api_device_save',
			     'setup.php');
    api_plugin_register_realm('cbEnhancedInfo',
			      '',
			      'Plugin - cbEnhancedInfo - View Information',
			      2701);
    api_plugin_register_realm('cbEnhancedInfo',
			      'cbEnhancedInfo_listInformation.php,cbEnhancedInfo_addInformation.php',
			      'Plugin - cbEnhancedInfo - Add Information',
			      2702);
    cbEnhancedInfo_setup_table_new ();
}

function cbEnhancedInfo_draw_navigation_text ( $nav ) {    
    // Report Scheduler
    $nav["cbEnhancedInfo_listInformation.php:"] = array(
	"title" => "Enhanced Information List",
	"mapping" => "index.php:",
	"url" => "cbEnhancedInfo_listInformation.php",
	"level" => "1"
    );
    $nav["cbEnhancedInfo_addInformation.php:add"] = array(
	"title" => "(Add)",
	"mapping" => "index.php:,?",
	"url" => "cbEnhancedInfo_addInformation",
	"level" => "2"
    );
    $nav["cbEnhancedInfo_addInformation.php:update"] = array(
	"title" => "(Edit)",
	"mapping" => "index.php:,?",
	"url" => "cbEnhancedInfo_addInformation.php",
	"level" => "2"
    );
    return $nav;
}

function cbEnhancedInfo_config_form() {
    global $fields_tree_edit,$fields_host_edit;

    $fields_host_edit2 = $fields_host_edit;
    $fields_host_edit3 = array();
    foreach ($fields_host_edit2 as $f => $a) {
	$fields_host_edit3[$f] = $a;
	if ($f == 'disabled') {
	    $fields_host_edit3["ebEnhancedInfo_country"] = array(
		"method" => "textbox",
		"friendly_name" => "Host Country",
		"description" => "The country where this host is situated at.",
		"value" => "|arg1:ebEnhancedInfo_country|",
		"max_length" => "255",
		"form_id" => false
	    );
	    $fields_host_edit3["ebEnhancedInfo_site"] = array(
		"method" => "textbox",
		"friendly_name" => "Host Site",
		"description" => "The site where this host is situated at.",
		"value" => "|arg1:ebEnhancedInfo_site|",
		"max_length" => "255",
		"form_id" => false
	    );	    
	    $fields_host_edit3["ebEnhancedInfo_room"] = array(
		"method" => "textbox",
		"friendly_name" => "Host Room",
		"description" => "The room where this host is situated at.",
		"value" => "|arg1:ebEnhancedInfo_room|",
		"max_length" => "255",
		"form_id" => false
	    );	    
	}
    }
    $fields_host_edit = $fields_host_edit3;
}

function cbEnhancedInfo_api_device_save ($save) {
        if (isset($_POST['ebEnhancedInfo_country'])) {
                $save["ebEnhancedInfo_country"] = form_input_validate($_POST['ebEnhancedInfo_country'], "ebEnhancedInfo_country", "", true, 255);
        } else {
                $save['ebEnhancedInfo_country'] = form_input_validate('', "ebEnhancedInfo_country", "", true, 3);
	}
        if (isset($_POST['ebEnhancedInfo_site'])) {
                $save["ebEnhancedInfo_site"] = form_input_validate($_POST['ebEnhancedInfo_site'], "ebEnhancedInfo_site", "", true, 255);
        } else {
                $save['ebEnhancedInfo_site'] = form_input_validate('', "ebEnhancedInfo_site", "", true, 3);
	}
        if (isset($_POST['ebEnhancedInfo_room'])) {
                $save["ebEnhancedInfo_room"] = form_input_validate($_POST['ebEnhancedInfo_room'], "ebEnhancedInfo_room", "", true, 255);
        } else {
                $save['ebEnhancedInfo_room'] = form_input_validate('', "ebEnhancedInfo_room", "", true, 3);
	}
    return $save;
}

    
function plugin_cbEnhancedInfo_uninstall () {
	// Do any extra Uninstall stuff here
}


function plugin_cbEnhancedInfo_check_config () {
	// Here we will check to ensure everything is configured
	cbEnhancedInfo_check_upgrade();

	return true;
}

function plugin_cbEnhancedInfo_upgrade () {
	// Here we will upgrade to the newest version
	cbEnhancedInfo_check_upgrade ();
	return false;
}

function plugin_cbEnhancedInfo_version () {
	return cbEnhancedInfo_version();
}

function cbEnhancedInfo_check_upgrade () {
	// We will only run this on pages which really need that data ...
	$files = array('cbEnhancedInfo_listInformation.php');
	if (isset($_SERVER['PHP_SELF']) && !in_array(basename($_SERVER['PHP_SELF']), $files))
		return;
	
	$current = cbEnhancedInfo_version ();
	$current = $current['version'];
	$old = db_fetch_cell("SELECT version FROM plugin_config WHERE directory='cbEnhancedInfo'");
	if ($current != $old) {
		cbEnhancedInfo_setup_table( $old );
	}
}

function cbEnhancedInfo_check_dependencies() {
    global $plugins, $config;
    return true;
}


function cbEnhancedInfo_setup_table_new () {
    global $config, $database_default;
    include_once($config["library_path"] . "/database.php");

    // Check if the cbEnhancedInfo tables are present
    $s_sql	= 'show tables from `' . $database_default . '`';
    $result = db_fetch_assoc( $s_sql ) or die ( mysql_error() );
    $a_tables = array();

    foreach($result as $index => $array) {
	    foreach($array as $table) {
		    $a_tables[] = $table;
	    }
    }

	/* The additional columns are missing here --->*/
	/* <--- */


    if (!in_array('plugin_cbEnhancedInfo_dataTable', $a_tables)) {
	    // Create Report Schedule Table
	    $data = array();
	    $data['columns'][] = array('name' => 'Id',
				       'type' => 'mediumint(25)',
				       'unsigned' => 'unsigned',
				       'NULL' => false,
				       'auto_increment' => true);
	    $data['columns'][] = array('name' => 'hostId',
				       'type' => 'mediumint(25)',
				       'unsigned' => 'unsigned',
				       'NULL' => false,
				       'default' => '0');
	    $data['columns'][] = array('name' => 'longitude',
				       'type' => 'varchar(1024)',
				       'NULL' => false);
	    $data['columns'][] = array('name' => 'latitude',
				       'type' => 'varchar(1024)',
				       'NULL' => false);
	    $data['columns'][] = array('name' => 'contactAddress',
				       'type' => 'varchar(1024)',
				       'NULL' => false);
	    $data['columns'][] = array('name' => 'additionalInformation',
				       'type' => 'text',
				       'NULL' => true);
	    $data['primary'] = 'Id';
	    $data['keys'][] = array('name' => 'hostId', 'columns' => 'hostId');
	    $data['type'] = 'MyISAM';
	    $data['comment'] = 'cbEnhancedInfo Data Table';
	    api_plugin_db_table_create ('cbEnhancedInfo', 'plugin_cbEnhancedInfo_dataTable', $data);
    }
}
	
function cbEnhancedInfo_config_settings () {
	global $tabs, $settings;
	$tabs["misc"] = "Misc";
	
	$temp = array(
            "cbEnhancedInfo_header" => array(
		"friendly_name" => "cbEnhancedInfo Plugin",
		"method" => "spacer",
		),
	    "cbEnhancedInfo_showInfo" => array(
                "friendly_name" => "Display enhanced information a the tree view",
                "description" => "This will display enhanced information after the tree view graph.",
                "method" => "checkbox",
                "max_length" => "255"
	        ),
	);

        if (isset($settings["misc"]))
                $settings["misc"] = array_merge($settings["misc"], $temp);
        else
                $settings["misc"] = $temp;
}


function cbEnhancedInfo_tree_after ($param)
{
    global $config, $database_default;
    include_once($config["library_path"] . "/adodb/adodb.inc.php");
    include_once($config["library_path"] . "/database.php");

    // Only show the enhanced information if it is enabled in the settings
    if ( read_config_option('cbEnhancedInfo_showInfo') ) {
	
	// Get the parameters
	preg_match("/^(.+),(\d+)$/",$param,$hit);
	
	// Check if there are some parameters
	if ( isset ( $hit[1] ) )
	{    
	    $host_name = $hit[1];
	    $host_leaf_id = $hit[2];
	    
	    // Retrieve the host id
	    $host_id = db_fetch_cell("SELECT host_id FROM graph_tree_items WHERE id=$host_leaf_id");
	    
	    // Retrieve the enhanced information for that host from the table
	    $host_longitude = db_fetch_cell("SELECT longitude FROM plugin_cbEnhancedInfo_dataTable WHERE hostId=$host_id");
	    $host_latitude = db_fetch_cell("SELECT latitude FROM plugin_cbEnhancedInfo_dataTable WHERE hostId=$host_id");
	    $host_contactAddress = db_fetch_cell("SELECT contactAddress FROM plugin_cbEnhancedInfo_dataTable WHERE hostId=$host_id");
	    $host_additionalInformation = db_fetch_cell("SELECT additionalInformation FROM plugin_cbEnhancedInfo_dataTable WHERE hostId=$host_id");
    
	    // Retrieve the host specific information from the host table
	    $host_country = db_fetch_cell("SELECT ebEnhancedInfo_country FROM host WHERE id=$host_id");
	    $host_site = db_fetch_cell("SELECT ebEnhancedInfo_site FROM host WHERE id=$host_id");
	    $host_room = db_fetch_cell("SELECT ebEnhancedInfo_room FROM host WHERE id=$host_id");
	    
	    ?>    
	    <tr bgcolor='#6d88ad'>
		<tr bgcolor='#a9b7cb'>
		    <td colspan='3' class='textHeaderDark'>
			    <strong>Enhanced Information</strong>
		    </td>
		</tr>			
		<tr align='center' style='background-color: #f9f9f9;'>
		    <td align='center'>
	    <?php
	    
	    print "<table>\n";
	    print "	<tr>\n";
	    print " 	  <td align=left><b>Contact Address</b></td>\n";
	    print " 	  <td align=left>".$host_contactAddress."</td>\n";
	    print "	</tr>\n";
	    print "	<tr>\n";
	    print " 	  <td align=left><b>Country/Site/Room</b></td>\n";
	    print " 	  <td align=left>".$host_country.'/'.$host_site.'/'.$host_room."</td>\n";
	    print "	</tr>\n";
	    print "	<tr>\n";
	    print " 	  <td align=left><b>Longitude/Latitude</b></td>\n";
	    print " 	  <td align=left>".$host_longitude.'/'.$host_latitude."</td>\n";
	    print "	</tr>\n";
	    print "	<tr>\n";
	    print " 	  <td align=left><b>Aditional Information</b></td>\n";
	    print " 	  <td align=left>".$host_additionalInformation."</td>\n";
	    print "	</tr>\n";
	    print "</table>\n";

	    print "</td></tr></tr>";
	}
    } 
    return $param;
}


function cbEnhancedInfo_config_arrays () {
	global $menu;

	$temp = array(
		"plugins/cbEnhancedInfo/cbEnhancedInfo_listInformation.php" => "Enhanced Info"
	);
        
    	if (isset($menu["cbPlugins"]))
		$menu["cbPlugins"] = array_merge($temp, $menu["cbPlugins"]);
	else
		$menu["cbPlugins"] = $temp;
}

function cbEnhancedInfo_version () {
	return array( 'name' 	=> 'cbEnhancedInfo',
			'version' 	=> '1.00',
			'longname'	=> 'cbEnhancedInfo',
			'author'	=> 'Thomas Urban',
			'homepage'	=> 'n/a',
			'email'		=> 'n/a',
			'url'		=> 'n/a'
			);
}

function cbEnhancedInfo_setup_table ( $old_version ) {
    global $config;
}




?>
