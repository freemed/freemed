<?php
require_once("gacl_admin.inc.php");

//GET takes precedence.
if ($_GET['group_type'] != '') {
	$group_type = $_GET['group_type'];
} else {
	$group_type = $_POST['group_type'];	
}

switch(strtolower(trim($group_type))) {
    case 'axo':
        $group_type = 'axo';
        $group_table = $gacl_api->_db_table_prefix . 'axo_groups';
        $group_map_table = $gacl_api->_db_table_prefix . 'groups_axo_map';
        $smarty->assign('current','axo_group');
        break;
    default:
        $group_type = 'aro';
        $group_table = $gacl_api->_db_table_prefix . 'aro_groups';
        $group_map_table = $gacl_api->_db_table_prefix . 'groups_aro_map';
        $smarty->assign('current','aro_group');
        break;
}

switch ($_POST['action']) {
    case 'Delete':
        //See edit_group.php    
        break;
    default:
        $formatted_groups = $gacl_api->format_groups($gacl_api->sort_groups($group_type), HTML);

        $query = '
        	SELECT		a.id, count(*)
        	FROM		'. $group_table .' a
        	INNER JOIN	'. $group_map_table .' b ON b.group_id=a.id
        	GROUP BY	a.id';
        $rs = $db->Execute($query);

        $rows = $rs->GetRows();
        foreach ($rows as $row) {
            $id = $row[0];
            $count = $row[1];
            
            $object_count[$id] = $count;
        }
        
        //showarray($);
        while (list($id,$name) = @each($formatted_groups)) {
            
            $group_data = $gacl_api->get_group_data($id, $group_type);
            
            $groups[] = array(
                'id' => $id,
                'parent_id' => $parent_id,
                'family_id' => $family_id,
                'name' => $name,
                'raw_name' => $group_data[2],
                'object_count' => $object_count[$id] + 0
            );
        }

        $smarty->assign('groups', $groups);

        break;
}

$smarty->assign('group_type', $group_type);
$smarty->assign('return_page', $_SERVER['REQUEST_URI']);

$smarty->assign('current', $group_type .'_group_admin');
$smarty->assign('page_title', strtoupper($group_type) .' Group Admin');

$smarty->assign("phpgacl_version", $gacl_api->get_version() );
$smarty->assign("phpgacl_schema_version", $gacl_api->get_schema_version() );

$smarty->display('phpgacl/group_admin.tpl');
?>
