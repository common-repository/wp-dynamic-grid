<?php
/*
Plugin Name: Wp-Dynamic-Grid
Plugin URI: http://ecommerce-plugin.codeinterest.com/wordpress/wp-dynamic-grid
Description: Wp Dynamic Grid.
Version: 1.0
Author: SolverCircle
Author URI: http://www.solvercircle.com
*/
define('WP_DYNAMIC_GRID_URL', plugins_url('',__FILE__));
define('WP_DYNAMIC_GRID_PATH',plugin_dir_path( __FILE__ ));

function wpdg_init(){
  wp_enqueue_style('wqo-css',WP_DYNAMIC_GRID_URL.'/css/blue/style.css');
  wp_enqueue_style('colorbox-css',WP_DYNAMIC_GRID_URL.'/js/jquery.tablesorter.pager.css');
  
  wp_enqueue_script('jquery');
  wp_enqueue_script('wcp-jscolor', plugins_url( '/js/jquery.tablesorter.js', __FILE__ ));
  wp_enqueue_script('wqo-tooltip', plugins_url( '/js/jquery.tablesorter.pager.js', __FILE__ ));
}

add_action('init','wpdg_init');


function custom_column_table_query (){
	global $wpdb;
	$rst = "Select * from ".$wpdb->prefix."".get_option('cust_col_table_name')."";
	$table_column = "SHOW COLUMNS FROM ".$wpdb->prefix."".get_option('cust_col_table_name')."";
	$table_rst = $wpdb->get_results($rst);
	$column_name = $wpdb->get_results($table_column);
	$result_data['rst'] = $table_rst;
	$result_data['column_name'] = $column_name;
	return $result_data;
}

function get_table_column_name(){
	global $wpdb;
	$table_column = "SHOW COLUMNS FROM ".$wpdb->prefix."".get_option('cust_col_table_name')."";
	$column_name = $wpdb->get_results($table_column);
	return $column_name;
}

function wpcustom_column_table_form(){
		$resulted_domain = custom_column_table_query();
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){ 
					jQuery("#myTable").tablesorter({widthFixed: false, widgets: ['zebra']}); 
					jQuery("#myTable").tablesorterPager({container: jQuery("#pager")});			
				} 
			);

		</script>
		<div style="width:100%;">
			<table style="width:100%;" id="myTable" class="tablesorter">
				<thead style="background-color:#DBDBDB; cursor:pointer;"> 
				<tr>
					<?php
					$all_chk_col_field_nam = get_option('selected_column_name_array');
					if(!is_array($all_chk_col_field_nam)){$all_chk_col_field_nam = unserialize(get_option('selected_column_name_array'));}
					foreach($resulted_domain['column_name'] as $col_name){
						for($k=0;$k<count($all_chk_col_field_nam);$k++){
							$arr_val_chk_view = explode("#", $all_chk_col_field_nam[$k]);
							if(($arr_val_chk_view[1]==$col_name->Field)&&($arr_val_chk_view[0]=='1')){
					?>
					<th><?php echo $arr_val_chk_view[2];?></th>
					<?php	} 
						}
					}
					?>
				</tr>
				</thead>
				<?php
				$i=0;
				foreach($resulted_domain['rst'] as $dom){
				?>
				<tr>
					<?php
					foreach($resulted_domain['column_name'] as $col_name){
					$field_name = $col_name->Field;
						for($m=0;$m<count($all_chk_col_field_nam);$m++){
							$arr_val_chk_view = explode("#", $all_chk_col_field_nam[$m]);
							if(($arr_val_chk_view[1]==$col_name->Field)&&($arr_val_chk_view[0]=='1')){
					?>
					<td><?php echo $dom->$field_name;?></td>
					<?php	} 
						}
					}?>
				</tr>
				<?php
				$i++;
				}
				?>
			</table>
			<div id="pager" class="pager">
				<form>
					<img src="<?php echo WP_DYNAMIC_GRID_URL;?>/image/pager/first.png" class="first"/>
					<img src="<?php echo WP_DYNAMIC_GRID_URL;?>/image/pager/prev.png" class="prev"/>
					<input type="text" class="pagedisplay"/>
					<img src="<?php echo WP_DYNAMIC_GRID_URL;?>/image/pager/next.png" class="next"/>
					<img src="<?php echo WP_DYNAMIC_GRID_URL;?>/image/pager/last.png" class="last"/>
					<select class="pagesize">
						<option selected="selected"  value="25">25</option>
						<option value="50">50</option>
						<option value="100">100</option>
					</select>
				</form>
			</div>
		</div>
		<div style="clear:both;"></div>
		<?php
}

function custom_column_table_install(){
	$newoptions = get_option('custom_column_table_options');
	add_option('custom_column_table_options', $newoptions);
}
function custom_column_table_uninstall(){
	delete_option('custom_column_table_options');
}

function add_column_name_for_admin_view($column_name){
	$all_column_array = array();
	$serialize_col_arr= array();
	foreach($column_name as $col_name){
		array_push($all_column_array,$col_name->Field);
	}
	$serialize_col_arr=serialize($all_column_array);
	add_option('all_column_name_array', $serialize_col_arr);
}

function update_column_name_for_admin_view($column_name){
	if(get_option('all_column_name_array')!=''){
		$all_column_array = array();
		$serialize_col_arr= array();
		foreach($column_name as $col_name){
			array_push($all_column_array,$col_name->Field);
		}
		$serialize_col_arr=serialize($all_column_array);
		update_option('all_column_name_array', $serialize_col_arr);
	}
	else{
		add_column_name_for_admin_view($column_name);
	}
}

function load_column_name_view(){
global $wpdb;
if(isset($_POST['checked_col_btn'])){
	$selected_column_array = array();
	for($l=0;$l<$_POST['total_col'];$l++){
		$chk_val = '';
		if(isset($_POST['chk_'.$l])){
			$chk_val ='1';
		}
		else{
			$chk_val = '0';
		}	
		$sel_col_name = $chk_val.'#'.$_POST['tbl_'.$l].'#'.$_POST['txt_'.$l].'#'.$l;
		$selected_column_array[]= $sel_col_name;
	}	
	$selected_serialize_col_arr=serialize($selected_column_array);
	if(get_option('selected_column_name_array')!=''){
		update_option('selected_column_name_array', $selected_serialize_col_arr);
	}
	else{
		add_option('selected_column_name_array', $selected_serialize_col_arr);
	}
}
	$col_view = '';
	$col_field_name = get_option('all_column_name_array');
	if(!is_array($col_field_name)){$col_field_name = unserialize(get_option('all_column_name_array'));}
	
	
	$all_checked_col_field_name = get_option('selected_column_name_array');
	if(!is_array($all_checked_col_field_name)){$all_checked_col_field_name = unserialize(get_option('selected_column_name_array'));}
	
	$col_view.='<form method="post" enctype="multipart/form-data" action="" >';
	$col_view.='<table border="1" style="width:100%;" class="wp-list-table widefat fixed pages">';
	$col_view.='<tr style="background-color:#F0F0F0; font-size: 15px; font-weight: normal;">';
	$col_view.='<td colspan="4"><h3>Field name of <b>'.$wpdb->prefix.''.get_option('cust_col_table_name').'</b> table</h3></td>';
	$col_view.='</tr>';
	$col_view.='<tr style="font-weight:bold;"><td>SN</td><td>Column Name</td><td>Display Column Name</td><td>&emsp;Show</td></tr>';
	$j=1;
	for($i=0;$i<count($col_field_name);$i++){
		$arr_val_chk = explode("#", $all_checked_col_field_name[$i]);
		
		$col_view.='<tr><td class="manage-column column-cb check-column">&emsp;'.$j.'. </td><td>'.$col_field_name[$i].'</td> <td><input type="text" name="txt_'.$i.'" id="txt_'.$i.'" '.($arr_val_chk[1]==$col_field_name[$i] ? 'value="'.$arr_val_chk[2].'"':'').'  /><input type="hidden" value="'.$col_field_name[$i].'" name="tbl_'.$i.'" id="tbl_'.$i.'" /></td><td><input '.($arr_val_chk[0]=='1' && $arr_val_chk[1]==$col_field_name[$i] ? 'checked="checked"':'').' style="width:30%;" type="checkbox" name="chk_'.$i.'" id="chk_'.$i.'" value="'.$col_field_name[$i].'" /></td></tr>';
		$j++;
	}
	$col_view.='<tr><input type="hidden" name="total_col" id="total_col" value="'.count($col_field_name).'" /><td colspan="4" align="right"><input type="submit" name="checked_col_btn" id="checked_col_btn" value="Save" class="button-primary" style="width:100px; border:none;" /></td></tr>';
	$col_view.='</table>';
	$col_view.='</form>';
	if($col_field_name!=''){
	?>
		<?php wp_enqueue_script("jquery"); ?>
		<script type="text/javascript">
			jQuery(document).ready(function(){ 
				jQuery("#col_div").html('<?php echo $col_view;?>'); 
				jQuery("#col_div").show(); 
			} 
			);
		</script>
	<?php
	}
}

function custom_table_name_function(){
global $wpdb;
	if(isset($_POST['table_name_submit'])){
		if(get_option('cust_col_table_name')!=''){
			if($_POST['table_name']!=''){
				update_option('cust_col_table_name', $_POST['table_name']);
				$column_name = get_table_column_name();
				update_column_name_for_admin_view($column_name);
				load_column_name_view();
			}
		}
		else{
			add_option('cust_col_table_name', $_POST['table_name']);
			$column_name = get_table_column_name();
			add_column_name_for_admin_view($column_name);
			load_column_name_view();
		}
	}
		echo '<div class="wrap">';
		echo '<div class="icon32 nws_icon"><br></div><h2>Manage Dynamic Grid</h2><br />';
		echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
		echo '<div id="post-body"><div id="post-body-content"><div id="namediv" class="stuffbox">';
		echo '<h3>Add Table</h3>';
		echo '<div class="inside">';
		?><form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" ><?
		echo '<table><tr>';
		echo '<td width="200"><label>Table Name : </label></td>';
		echo '<td><input type="text" name="table_name" id="table_name" value="'.get_option('cust_col_table_name').'" /></td></tr>';
		echo '<tr><td colspan="2" align="right"><input type="submit" name="table_name_submit" id="table_name_submit" value="Submit" class="button-primary" style="width:100px; border:none;" /></td></tr>';
		echo '<tr><td  colspan="2">Current Table Name : '.$wpdb->prefix.''.get_option('cust_col_table_name').'</td></tr>';
		echo '<tr><td colspan="2">Use <strong>[wpcustomtable]</strong> Shortcode in the page or post to display the search form.</td></tr>';
		echo '</table>';
		?></form><?
		echo '<div class="col_div" id="col_div" style="width:100%; display:block; ">'.load_column_name_view().'</div>';
		echo '</div>';
		echo '</div></div></div>';
		echo '</div>';
		
		echo '</div>';
	//--------------------------------------------------
}
function add_custom_column_table_admin_page(){
	add_object_page('Manage Grid', 'Manage Grid', 8, __FILE__, 'custom_table_name_function');
}
add_action('admin_menu', 'add_custom_column_table_admin_page');
add_shortcode('wpcustomtable', 'wpcustom_column_table_form');
register_activation_hook( __FILE__, 'custom_column_table_install' );
register_deactivation_hook( __FILE__, 'custom_column_table_uninstall' );
?>