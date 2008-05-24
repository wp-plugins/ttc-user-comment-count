<?php
/*
Plugin Name: TTC User Comment Count
Plugin URI: http://herselfswebtools.com/2008/05/wordpress-plugin-to-sort-through-users.html
Description: Creates a list of all users, registration date, and number of comments See link under 'Manage' to access page and checks email address against www.stopforumspam.com database.
Author: Linda MacPhee-Cobb
Version: 2.21
Author URI: http://timestocome.com
*/



//draw page for user
// Hook for adding admin menus
add_action('admin_menu', 'ttc_add_user_manager_pages');

// action function for above hook
function ttc_add_user_manager_pages() {
    // Add a new submenu under Manage:
    add_management_page('Test Manage', 'User Comment Count', 8, 'User Comment Count', 'ttc_manage_users_page');
}


// mt_manage_page() displays the page content for the Test Manage submenu
function ttc_manage_users_page() {

	global $wpdb;
	$table_prefix = $wpdb->prefix;
	
	
	$users_with_comments = (array)$wpdb->get_results("select count(*) user_login, comment_author, user_email, 
			date_format( user_registered, '%M %d %Y') as registration_date,  date_format( max(comment_date), '%M %d %Y' ) as last_comment_date  from {$table_prefix}users, 
			{$table_prefix}comments where user_login = comment_author group by comment_author order by user_registered;
	");

	$users_with_no_comments = (array)$wpdb->get_results("select user_login, user_email, date_format( user_registered, '%M %d %Y' ) as user_registration_date
		from {$table_prefix}users where {$table_prefix}users.user_login not in ( select comment_author from {$table_prefix}comments );
	");
	
	print "<h2>User Comment Count</h2>";


	print "<table border='3' width='700'><th colspan='5'>Users who comment</th>";
	print "<tr><td><b>Number of Posts</b></td><td><b>User Name</b></td><td><b>User Email</b></td><td><b>Date Registered</b></td><td><b>Most recent comment</b></td></tr>";
	foreach ( $users_with_comments as $users ){
			$number_of_posts = $users->user_login;
			$user_name = $users->comment_author;
			$user_email = $users->user_email;
			$date_registered = $users->registration_date;
			$last_comment = $users->last_comment_date;
			
		print "\n<tr><td>$number_of_posts</td><td>$user_name</td><td><a href=\"mailto:$user_email\">$user_email</a></td><td>$date_registered</td><td>$last_comment</td></tr>";
			
	}
	print "</table>";
	print "<br><br>";


	print "<table border='3' width='700'><th colspan='3'>Users with no comments</th>";
	print "<tr><td><b>User Name</b></td><td><b>User Email</b></td><td><b>Date Registered</b></td><td><b>Known Spammer?</td></tr>";
		
	foreach ( $users_with_no_comments as $users ){
		$user_name = $users->user_login;
		$user_email = $users->user_email;
		$date_registered = $users->user_registration_date;
		$check = file_get_contents ( "http://www.stopforumspam.com/api?email=$user_email" );	
		$test = "<appears>yes</appears>";
		
		if ( strpos($check, $test) > 0 ) { $check = '<b>yes</b>'; } else { $check = 'no'; }
			print "\n<tr><td>$user_name</td><td><a href=\"mailto:$user_email\">$user_email</a></td><td>$date_registered</td><td>$check</td></tr>";
			
	}
	
		print "</table>";
	
		
}


?>