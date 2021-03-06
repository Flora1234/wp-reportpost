<?php
### Load WordPress required files if called directly
require_once '../../../wp-load.php';

### Use WordPress 2.6 Constants
if (!defined('WP_CONTENT_DIR')) {
	define( 'WP_CONTENT_DIR', ABSPATH.'wp-content');
}
if (!defined('WP_CONTENT_URL')) {
	define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
}
if (!defined('WP_PLUGIN_DIR')) {
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
}
if (!defined('WP_PLUGIN_URL')) {
	define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
}

// Handle the POST

/* This function to be Called When a Report Rquest Received
----------------------------------------*/
function wprp_handle_reports()
{
	global $wprp_message, $blog_id;
	
	// get Post PARAM
	$post_id=(int)$_POST['postID'];
	$report_as=$_POST['report_as'];
	$description=$_POST['description'];
	$ipaddress=get_ipaddress();
	$nonce=$_POST['wpnonce'];
	$comment_id=$_POST['commentID'];
	
	if ($comment_id>-1){
		$report_as = 'Comment '.$comment_id.': '.$report_as;	
	}
	
	// Get the Post
	$post=get_blog_post($blog_id, $post_id);
	// Check for POST
	if(!$post_id || !$post)
	{
		echo "<strong>Invalid Post</strong>";
		return;
	}
	// Security CHECK
	if (!wp_verify_nonce($nonce, $post_id) )
	{
		echo "<strong>Security Check Failed, Please Submit again...</strong>";
		return;
	}
	
	include_once('ReportPost.class.php');
	
	$rp = new ReportPost();
	
	if($rp->add($post_id, $report_as, $description, $comment_id))
	{
		$reported=true;
	}else{
		echo "Sorry, Unable to Process your Request. Please contact Site Administrator via Email to Report this Issue";
	}
	
	/*
	// tpValirable
	$reported=false;
	
	// Check for Existing Post Report
	$post_count=$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->reportpost WHERE post_id=%s",$post_id));
	
	if(is_numeric($post_count) && $post_count>0)
	{
		// Update the Description
		$result=$wpdb->query( $wpdb->prepare("UPDATE $wpdb->reportpost SET description=CONCAT(description,%s) WHERE post_id=%s"," <br />[".$ipaddress."] : ".$report_as." | ".$description,$post_id));
		
		$reported=true;
	}else{
		// Do Report!
		$result=$wpdb->query( $wpdb->prepare("INSERT INTO $wpdb->reportpost(post_id,post_title,user_ip,description,stamp) VALUES(%s,%s,%s,%s,%s)",$post_id, $post->post_title, $ipaddress,"[".$ipaddress."] : ".$report_as." | ".$description,time()));
		$reported=true;
		
		// Send Mail
		$send_email=get_option("rp_send_email");
		if($send_email==1)
		{
			// SEND EMAIL
			$mail_to=get_option("rp_email_address");
			$mail_subject="[REPORT] : ".$post->post_title;
			$mail_body="Following Post has been Reported through ".get_option("blogname")."\n-----\n";
			$mail_body.="POST ID: ".$post_id."\n";
			$mail_body.="POST TITLE: ".$post->post_title."\n";
			$mail_body.="Reported As: ".$report_as."\n";
			$mail_body.="Description: \n".$description."\n";
			$mail_body.="\n-----\nThank You";
			
			$mail_header="From: Admin <".get_option("admin_email").">";
			
			// Send mail // @ Prvent from Showing Any Error Message JUST in CASE
			@mail($mail_to,$mail_subject,$mail_body,$mail_header);
		}
		
	}*/
	
	if($reported)
	{
		// get thanks Option
		$thanksMsg = get_site_option('rp_thanks_msg');
		if(empty($thanksMsg)) {
			if ($comment_id > -1){
				$thanksMsg="<strong>Thanks for reporting this comment</strong>";
			} else {
				$thanksMsg="<strong>Thanks for reporting [post_title]</strong>";
			}
		}
		$thanksMsg = str_replace("[post_title]", $post->post_title,$thanksMsg);
		echo $thanksMsg;
		echo "<br />Reported as : " . $report_as;
		if(!empty($description)){
			echo "<br />Comments : " . $description;
		}

	}
}


### Function: Get IP Address
if(!function_exists('get_ipaddress')) {
	function get_ipaddress() {
		if (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip_address = $_SERVER["REMOTE_ADDR"];
		} else {
			$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		if(strpos($ip_address, ',') !== false) {
			$ip_address = explode(',', $ip_address);
			$ip_address = $ip_address[0];
		}
		return $ip_address;
	}
}


// Determin How to Call POST
if(isset($_POST['do_ajax_report']))
{
	wprp_handle_reports();
}
?>