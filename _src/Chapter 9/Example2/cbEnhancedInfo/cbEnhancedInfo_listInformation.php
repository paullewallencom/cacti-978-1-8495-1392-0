<?php
/*******************************************************************************

 File:         cbEnhancedInfo_listInformation.php
 Author:       Thomas Urban
 Language:     PHP
 Encoding:     UTF-8
 Status:       -
 License:      GPLv2
 
*******************************************************************************/

$dir = dirname(__FILE__);
$mainDir = preg_replace("@plugins.cbEnhancedInfo@","",$dir);
chdir($mainDir);
include_once("./include/auth.php");
include_once("./lib/tree.php");
include_once("./lib/data_query.php");
$_SESSION['custom']=false;

/* set default action */
if (!isset($_REQUEST["drp_action"])) { $_REQUEST["drp_action"] = ""; }
if (!isset($_REQUEST["sort_column"])) { $_REQUEST["sort_column"] = ""; }
if (!isset($_REQUEST["sort_direction"])) { $_REQUEST["sort_direction"] = ""; }

switch ($_REQUEST["drp_action"]) {
	case '1':
		form_delete();
		break;
	default:
		include_once("./include/top_header.php");
		form_display();
		include_once("./include/bottom_footer.php");
		break;
}


function form_delete() {
    global $colors, $hash_type_names;
    
    /* loop through each of the selected tasks and delete them*/
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */
            db_execute("DELETE FROM `plugin_cbEnhancedInfo_dataTable` where `Id`='" . $matches[1] . "'");
        }
	}
    header("Location: cbEnhancedInfo_listInformation.php");
}

function form_display() {
    global $colors, $hash_type_names;
    print "<font size=+1>cbEnhancedInfo - Enhanced Information Items</font><br>\n";
    print "<hr>\n";
	
    $where_clause = "";
 
    // Take care of the sorting, did the user select any column
    // to be sorted ?
    if ( isset($_REQUEST["sort_column"]))
    {
	// Did the user select a column that is actually sortable ?
        if (
            ( $_REQUEST["sort_column"] == 'Id' )
            || ( $_REQUEST["sort_column"] == 'hostId' )
            || ( $_REQUEST["sort_column"] == 'contactAddress' )
            || ( $_REQUEST["sort_column"] == 'longitude' )
            || ( $_REQUEST["sort_column"] == 'latitude' )
           )
        {
	    // What direction should the table be sorted, ascending or
            // descending ?
            if (
                ( $_REQUEST["sort_direction"] == 'ASC' )
                || ( $_REQUEST["sort_direction"] == 'DESC' )
            )
            {
		// Finally, we can build the sort order sql statement
                $where_clause  .= ' ORDER BY ' .
                    $_REQUEST["sort_column"] .
                    ' ' .$_REQUEST["sort_direction"];
            }
        }
    }
    // Select all data items from the table. The data will be stored
    // in an array. Note the $where_clause being used
    $a_enhancedInfos = db_fetch_assoc("
        SELECT
          `plugin_cbEnhancedInfo_dataTable`.`Id`,
          `host`.`description` as hostDescription,
          `plugin_cbEnhancedInfo_dataTable`.`longitude`,
          `plugin_cbEnhancedInfo_dataTable`.`latitude`,
          `plugin_cbEnhancedInfo_dataTable`.`contactAddress`,
          `plugin_cbEnhancedInfo_dataTable`.`additionalInformation`
        FROM
          `plugin_cbEnhancedInfo_dataTable` INNER JOIN
          `host` ON `plugin_cbEnhancedInfo_dataTable`.`hostId` = `host`.`Id`
	$where_clause
    ");

    // Start the web form
    print "<form name=chk method=POST action=cbEnhancedInfo_listInformation.php>\n";

    // Print a nice looking html start box :-)
    html_start_box("<strong>Enhanced Information Items</strong>", "100%", $colors["header"], "3", "center", "cbEnhancedInfo_addInformation.php?action=add");

    if ( sizeof( $a_enhancedInfos ) > 0 ) 
    {
	// The table needs some menu, this will also be used for the sorting
        $menu_text = array(
            "Id" => array("Id", "ASC"),
            "hostId" => array("Host", "ASC"),
            "longitude" => array("Longitude", "ASC"),
            "latitude" => array("Latitude", "ASC"),
            "contactAddress" => array("Contact Address", "ASC")
        );
    
	// The html header will contain a checkbox, so the end-user can
	// select all items on the table at once.
        html_header_sort_checkbox($menu_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);
    
	// This variable will be used to create te alternate colored
	// rows on the table
        $i = 0;
    
	// Let�s cycle through the items !
        foreach ($a_enhancedInfos as $a_enhancedInfo)
        {
            form_alternate_row_color($colors["alternate"], $colors["light"], $i, 'line' . $a_enhancedInfo['Id']); $i++;
            form_selectable_cell($a_enhancedInfo['Id'], $a_enhancedInfo["Id"]);
            form_selectable_cell("<a href='cbEnhancedInfo_addInformation.php?action=update&dataId=".$a_enhancedInfo["Id"]."'>".$a_enhancedInfo['hostDescription']."</b></a>",$a_enhancedInfo['Id'],250);
            form_selectable_cell($a_enhancedInfo['longitude'], $a_enhancedInfo["Id"]);
            form_selectable_cell( $a_enhancedInfo["latitude"], $a_enhancedInfo["Id"]);
            form_selectable_cell( $a_enhancedInfo["contactAddress"], $a_enhancedInfo["Id"]);
            form_checkbox_cell('selected_items', $a_enhancedInfo["Id"]);
            form_end_row();
        }
        html_end_box(false);

	// Let's define some actions for the user:
	$task_actions = array(
	    1 => "Delete"
	);
	draw_actions_dropdown($task_actions);
    }
    else
    {
	// Hm, we didn't find any items ? Let's notify our user !
	print "<tr><td><em>No enhanced information records exist</em></td></tr>";
        html_end_box(false);
    }
    
    print "</form>";
}    


?>
