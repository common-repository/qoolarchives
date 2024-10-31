<?php
/*
* Plugin Name: QoolArchives
* Version: 1.0
* Plugin URI: http://www.qoolsoft.gr/blog/qoolarchives-wordpress-plugin
* Description: The archives Wordpress plugin. This plugin creates real posts archives and greatly reduces the DB load since old posts are called only when needed. This plugin is great for sites with thousands of posts!!!
* Author: Kerasiotis Vasileios
* Author URI: http://www.qoolsoft.gr/
*/
//error_reporting(E_ALL);


function qool_archives_update_requested () {
	global $wpdb;
	//get now date
	$nowdate = date("Y-m-d H:i:s",time());
	//see if the time in now to create archives
	$date_range = get_option("qoolarchives_date_range");
	switch ($date_range){
		case "1 Week":
			$nowdate = date("Y-m-d H:i:s",($now-(14*86400)));
			break;
		case "15 Days":
			$nowdate = date("Y-m-d H:i:s",($now-(30*86400)));
			break;
		case "1 Month":
			$nowdate = date("Y-m-d H:i:s",($now-(60*86400)));
			break;
	}


	//first check if there is actually a previous archived date
	$check1 = $wpdb->query("SELECT * FROM `{$wpdb->prefix}qoolarchives_config`","ARRAY_A");
	if($check1[0]['id']){
	$check = $wpdb->query("SELECT * FROM `{$wpdb->prefix}qoolarchives_config` WHERE `enddate`<='$nowdate'","ARRAY_A");
	if($check[0]['id']){
		return true;
	}
	return false;
	}else{
		return true;
	}
}


add_action('admin_menu', 'qool_archives_plugin_menu');

function qool_archives_register_settings(){
	register_setting( 'qool_archives_option-group', 'qoolarchives_date_range' );
}


function qool_archives_plugin_menu() {
	add_menu_page('QoolArchives', 'QoolArchives', 'administrator', 'Qoolarchives', 'qool_archives_general_settings',get_option('home').'/wp-content/plugins/qoolarchives/archive.png',32);
	add_submenu_page( 'edit.php', 'Archived Posts', 'Archived Posts', 'administrator', 'archivedPosts', 'qool_archives_get_archived_posts');
	//call register settings function
	add_action( 'admin_init', 'qool_archives_register_settings' );
}

function qool_archives_get_archived_posts(){
	global $wpdb,$wp;
	if($_GET['pagenum']){
		$num = $_GET['pagenum']*20;
	}else{
		$_GET['pagenum'] = 1;
		$num = 0;
	}
	?>
	<div class="wrap">
	<div id="icon-edit" class="icon32"><br></div>
<h2>Archived Posts </h2>
<form id="posts-filter" action="http://localhost/wordpress/wp-admin/edit.php" method="get">
<div class="clear"></div>
<div class="tablenav">
<div class="tablenav-pages" style="float:right">
<?php if($_GET['pagenum']>1):?><a class="page-numbers" href="/wordpress/wp-admin/edit.php?page=archivedPosts&pagenum=<?php echo $_GET['pagenum']-1;?>">Previous</a><?php endif;?>
<span class="displaying-num">Displaying 20 Posts Per page (
<?php 
if($_GET['pagenum']>1){
	echo (($num-20)+1)."-"."$num";
}else{
	echo "1-20";
}

?>)</span>

<a class="page-numbers" href="/wordpress/wp-admin/edit.php?page=archivedPosts&pagenum=<?php echo $_GET['pagenum']+1;?>">Next</a>
</div></div></div>


<div class="clear"></div>
<table class="widefat post fixed" cellspacing="0">
	<thead>
	<tr>

	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
	<th scope="col" id="title" class="manage-column column-title" style="">Title</th>
	<th scope="col" id="author" class="manage-column column-author" style="">Author</th>
	<th scope="col" id="date" class="manage-column column-date" style="">Date</th>

	</tr>
	</thead>

	<tfoot>
	<tr>
	<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
	<th scope="col" class="manage-column column-title" style="">Title</th>
	<th scope="col" class="manage-column column-author" style="">Author</th>

	<th scope="col" class="manage-column column-date" style="">Date</th>
	</tr>
	</tfoot>
	<tbody>
	<?php
	
	$posts = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}qool_archives_posts` WHERE `post_type`='post' AND `post_status`='publish' AND `post_name`!='' LIMIT $num,20");
	foreach ($posts as $post){
		?>
		<tr id="post-<?php echo $post->ID; ?>" class="alternate author-self status-publish iedit" valign="top">
		<th scope="row" class="check-column"><input name="post[]" value="<?php echo $post->ID; ?>" type="checkbox"></th>
				<td class="post-title column-title"><strong><a target="_blank" class="row-title" href="<?php echo get_option('home');?>/?p=<?php echo $post->ID; ?>" title="<?php echo $post->post_title; ?>"><?php echo $post->post_title; ?></a></strong>
		<div class="row-actions"></div>

		</td>
				<td class="author column-author"><a href="edit.php?post_type=post&amp;author=<?php echo $post->post_author; ?>"><?php echo the_author_meta( 'display_name', $post->post_author );?></a></td>
				
				

		<td class="date column-date"><abbr title="<?php echo $post->post_date; ?>">Published<br><?php echo $post->post_date; ?></abbr></td>	</tr>
		<?php
	}
	?>
	</tbody>
	</table>

	<?php
}

function qool_archives_general_settings() {
?>
  <div class="wrap">
  <div style="float:left; display:inline; width:80%">
<h2>QoolArchives Options</h2>
<form method="post" action="options.php">
    <?php settings_fields( 'qool_archives_option-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Archive Every:</th>
        <td><select name="qoolarchives_date_range">
        <option value="<?php echo get_option('qoolarchives_date_range'); ?>"><?php echo get_option('qoolarchives_date_range'); ?></option>
        <option value="1 Week">1 Week</option>
        <option value="15 Days">15 Days</option>
        <option value="1 Month">1 Month</option>
        </td>
        </tr> 
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p></form>
    <div style="width:200px; float:left; background:#f5f5f5; border:1px solid #eeefff; padding:6px; margin:10px; font-size:9pt;">
		<b style="font-size:130%;">QoolArchives</b>
		<p><b>Version:</b> 1.0.0</p>
		<p><b>Author:</b> Kerasiotis Vasileios</p>

		<p><b>Contact:</b> <a href="http://www.qoolsoft.gr/contact/">Contact QoolSoft</a></p>
		<p><em>QoolArchives</em> is a WordPress plugin that creates real archives for your Wordpress.</p>
		<p>If you like this plugin, concider buying me a beer. In Greece a beer costs &euro;3 :)
		</p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="J54FP932D9XCC">
<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

		<em>Powered by</em>
		<a href="http://www.qoolsoft.gr/"><img src="http://qerdizo.gr/template/css/qoolsoft.png" alt="QoolSoft Powered" /></a>
	</div>


</div>


<?php
}

function qool_archives_install(){
	global $wpdb;
	$table = $wpdb->prefix."qool_archives_posts";
	$table1 = $wpdb->prefix."qool_archives_postmeta";

	$structure = "
    CREATE TABLE IF NOT EXISTS `$table` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext NOT NULL,
  `post_title` text NOT NULL,
  `post_excerpt` text NOT NULL,
  `post_status` varchar(20) NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) NOT NULL DEFAULT 'open',
  `post_password` varchar(20) NOT NULL DEFAULT '',
  `post_name` varchar(200) NOT NULL DEFAULT '',
  `to_ping` text NOT NULL,
  `pinged` text NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` text NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `guid` varchar(255) NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT '0',
  `post_type` varchar(20) NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	$wpdb->query($structure);

	$structure = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}qoolarchives_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `startdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	
	$wpdb->query($structure);
	
	

}

function qool_archives_show_info(){
	if(get_option('qoolarchives_date_range')==''){
	?>
	<div id='qoolarchives_info' class='updated fade'><p><strong>QoolArchives has been installed</strong> We have added some functionality to Wordpress but we need your help. You must now <a href="<?php echo get_option('home')?>/wp-admin/admin.php?page=Qoolarchives">set the archive date range</a> in order to finish the setup.</p></div>
	<?php
	}
}

add_action('activate_qoolarchives/qoolarchives.php', 'qool_archives_install');
add_action('admin_notices','qool_archives_show_info');

function qool_archives_start(){
	global $wpdb;
	$posts_archive = $wpdb->prefix."qool_archives_posts";
	$meta_archive = $wpdb->prefix."qool_archives_postmeta";

	$date_range = get_option("qoolarchives_date_range");

	//debug
	//$date_range = "1week";

	$now = time();
	$nowdate = date("Y-m-d H:i:s",$now);


	switch ($date_range){
		case "1 Week":
			$prevdate = date("Y-m-d H:i:s",($now-(37*86400)));
			$nowdate = date("Y-m-d H:i:s",($now-(7*86400)));
			break;
		case "15 Days":
			$prevdate = date("Y-m-d H:i:s",($now-(45*86400)));
			$nowdate = date("Y-m-d H:i:s",($now-(15*86400)));
			break;
		case "1 Month":
			$prevdate = date("Y-m-d H:i:s",($now-(60*86400)));
			$nowdate = date("Y-m-d H:i:s",($now-(30*86400)));
			break;
	}

	$prefix = $wpdb->prefix;
	if(qool_archives_update_requested()){
		//we get the posts
		$posts = $wpdb->get_results("SELECT * FROM `{$prefix}posts` WHERE `post_type`='post' AND `post_status`='publish' AND `post_name`!='' AND `post_date` >= '$prevdate' AND `post_date` <= '$nowdate'","ARRAY_A");

		foreach ($posts as $post){

			// %s as string; %d as decimal number; and %f as float.
			$format = array('%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','','%s','%d','%s','%s','%d');
			$id = $post['ID'];
			//insert the archived post
			$wpdb->insert( $posts_archive, $post, $format );
			//delete the post
			$wpdb->query("DELETE FROM `{$prefix}posts` WHERE `ID` ='$id'");
			//delete the post's revisions
			$wpdb->query("DELETE FROM `{$prefix}posts` WHERE `post_parent`='$id' AND `post_type`='revision'");

			//get metadata for this post

			/*
			$metas = $wpdb->get_results("SELECT * FROM `{$prefix}postmeta` WHERE `post_id`='$id'","ARRAY_A");
			foreach ($metas as $meta){
				$mid = $meta['meta_id'];
				unset($meta['meta_id']);
				$format = array('%d','%s','%s');
				//insert the archived meta
				$wpdb->insert( $meta_archive, $meta, $format );
				//delete the meta
				$wpdb->query("DELETE FROM `{$prefix}postmeta` WHERE `meta_id` ='$mid'");
			}
			*/
		}

		//check if the config is correct
		$config = $wpdb->get_results("SELECT * FROM `{$prefix}qoolarchives_config`","ARRAY_A");
		if($config[0]['id']){
			$wpdb->query("UPDATE `{$prefix}qoolarchives_config` SET `enddate`='$nowdate'");
		}else{
			$wpdb->query("INSERT INTO `{$prefix}qoolarchives_config` (`id` ,`startdate` ,`enddate` ) VALUES ('','$prevdate','$nowdate')");
		}
		//run the optimization commands
		$wpdb->query("OPTIMIZE TABLE `{$prefix}posts` ");
	}
}

function qool_archives_prefix_change($sql){
	global $wpdb,$wp;
	//get the archives config
	$prefix = $wpdb->prefix;
	$config = $wpdb->get_results("SELECT * FROM `{$prefix}qoolarchives_config`","ARRAY_A");

	$latest = $config[0]['enddate'];
	$oldest = $config[0]['startdate'];

	//search check
	if(is_search() || is_home() || is_page() || is_category() || is_author() || is_feed()){
		$results = $wpdb->get_results($sql,"ARRAY_A");

		if($results[0]['ID']){
			//if exists then return
			return $sql;
		}else{
			//else change the query
			$sql = str_replace("$wpdb->posts","{$wpdb->prefix}qool_archives_posts",$sql);
		}
	}

	//archives check
	if(is_date()){
		//see if the request fits our archives
		$latest = explode("-",$latest);
		$year = $latest[0];
		$month =  $latest[1];
		$day = explode(" ",$latest[2]);
		$day =  $day[0];

		//see if the year is ok
		if($wp->query_vars['year']<=$year){
			if($wp->query_vars['monthnum']){
				if($wp->query_vars['monthnum']<=$month){

					//go for the date
					if($wp->query_vars['day']){
						if($wp->query_vars['day']<=$day){

							$sql = str_replace("$wpdb->posts","{$wpdb->prefix}qool_archives_posts",$sql);
						}else{
							return $sql;
						}
					}else{
						//we want posts from the month archive only
						$sql = str_replace("$wpdb->posts","{$wpdb->prefix}qool_archives_posts",$sql);
					}
				}else{
					return $sql;
				}
			}else{

				//we want posts from the year archive only
				$sql = str_replace("$wpdb->posts","{$wpdb->prefix}qool_archives_posts",$sql);
			}
		}else{
			return $sql;
		}
	}

	if(is_single()){
		//check if the post exists in the genuine posts table
		$results = $wpdb->get_results($sql,"ARRAY_A");

		if($results[0]['ID']){
			//if exists then return
			return $sql;
		}else{
			//else change the query
			$sql = str_replace("$wpdb->posts","{$wpdb->prefix}qool_archives_posts",$sql);
		}
	}
	//echo $sql;
	return $sql;
}

//create archives hook
add_action('pre_get_posts', 'qool_archives_start');
//hook to interecpt the loop


add_action('posts_request',"qool_archives_prefix_change");

?>