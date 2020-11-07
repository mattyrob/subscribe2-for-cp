<?php
// Handles options for subscribe2
// DO NOT EDIT THIS FILE AS IT IS SET BY THE OPTIONS PAGE

global $mysubscribe2;

if ( ! isset( $mysubscribe2->subscribe2_options['autosub'] ) ) {
	$mysubscribe2->subscribe2_options['autosub'] = 'no';
} // option to autosubscribe registered users to new categories

if ( ! isset( $mysubscribe2->subscribe2_options['newreg_override'] ) ) {
	$mysubscribe2->subscribe2_options['newreg_override'] = 'no';
} // option to autosubscribe registered users to new categories

if ( ! isset( $mysubscribe2->subscribe2_options['wpregdef'] ) ) {
	$mysubscribe2->subscribe2_options['wpregdef'] = 'no';
} // option to check registration form box by default

if ( ! isset( $mysubscribe2->subscribe2_options['autoformat'] ) ) {
	$mysubscribe2->subscribe2_options['autoformat'] = 'post';
} // option for default auto-subscription email format

if ( ! isset( $mysubscribe2->subscribe2_options['show_autosub'] ) ) {
	$mysubscribe2->subscribe2_options['show_autosub'] = 'yes';
} // option to display auto-subscription option to registered users

if ( ! isset( $mysubscribe2->subscribe2_options['autosub_def'] ) ) {
	$mysubscribe2->subscribe2_options['autosub_def'] = 'no';
} // option for user default auto-subscription to new categories

if ( ! isset( $mysubscribe2->subscribe2_options['comment_subs'] ) ) {
	$mysubscribe2->subscribe2_options['comment_subs'] = 'no';
} // option for commenters to subscribe as public subscribers

if ( ! isset( $mysubscribe2->subscribe2_options['comment_def'] ) ) {
	$mysubscribe2->subscribe2_options['comment_def'] = 'no';
} // option for comments box to be checked by default

if ( ! isset( $mysubscribe2->subscribe2_options['one_click_profile'] ) ) {
	$mysubscribe2->subscribe2_options['one_click_profile'] = 'no';
} // option for displaying 'one-click' option on profile page

if ( ! isset( $mysubscribe2->subscribe2_options['bcclimit'] ) ) {
	$mysubscribe2->subscribe2_options['bcclimit'] = 1;
} // option for default bcc limit on email notifications

if ( ! isset( $mysubscribe2->subscribe2_options['admin_email'] ) ) {
	$mysubscribe2->subscribe2_options['admin_email'] = 'subs';
} // option for sending new subscriber notifications to admins

if ( ! isset( $mysubscribe2->subscribe2_options['tracking'] ) ) {
	$mysubscribe2->subscribe2_options['tracking'] = '';
} // option for tracking

if ( ! isset( $mysubscribe2->subscribe2_options['s2page'] ) ) {
	$mysubscribe2->subscribe2_options['s2page'] = 0;
} // option for default Page for Subscribe2 to use

if ( ! isset( $mysubscribe2->subscribe2_options['stylesheet'] ) ) {
	$mysubscribe2->subscribe2_options['stylesheet'] = 'yes';
} // option to include link to theme stylesheet from HTML notifications

if ( ! isset( $mysubscribe2->subscribe2_options['embed'] ) ) {
	$mysubscribe2->subscribe2_options['embed'] = 'no';
} // option to embed stylesheet and images into HTML emails

if ( ! isset( $mysubscribe2->subscribe2_options['pages'] ) ) {
	$mysubscribe2->subscribe2_options['pages'] = 'no';
} // option for sending notifications for Pages

if ( ! isset( $mysubscribe2->subscribe2_options['password'] ) ) {
	$mysubscribe2->subscribe2_options['password'] = 'no';
} // option for sending notifications for posts that are password protected

if ( ! isset( $mysubscribe2->subscribe2_options['stickies'] ) ) {
	$mysubscribe2->subscribe2_options['stickies'] = 'no';
} // option for including sticky posts in digest notifications

if ( ! isset( $mysubscribe2->subscribe2_options['private'] ) ) {
	$mysubscribe2->subscribe2_options['private'] = 'no';
} // option for sending notifications for posts that are private

if ( ! isset( $mysubscribe2->subscribe2_options['email_freq'] ) ) {
	$mysubscribe2->subscribe2_options['email_freq'] = 'never';
} // option for sending emails per-post or as a digest email on a cron schedule

if ( ! isset( $mysubscribe2->subscribe2_options['cron_order'] ) ) {
	$mysubscribe2->subscribe2_options['cron_order'] = 'desc';
} // option for ordering of posts in digest email

if ( ! isset( $mysubscribe2->subscribe2_options['compulsory'] ) ) {
	$mysubscribe2->subscribe2_options['compulsory'] = '';
} // option for compulsory categories

if ( ! isset( $mysubscribe2->subscribe2_options['exclude'] ) ) {
	$mysubscribe2->subscribe2_options['exclude'] = '';
} // option for excluded categories

if ( ! isset( $mysubscribe2->subscribe2_options['sender'] ) ) {
	$mysubscribe2->subscribe2_options['sender'] = 'blogname';
} // option for email notification sender

if ( ! isset( $mysubscribe2->subscribe2_options['reg_override'] ) ) {
	$mysubscribe2->subscribe2_options['reg_override'] = '1';
} // option for excluded categories to be overriden for registered users

if ( ! isset( $mysubscribe2->subscribe2_options['show_meta'] ) ) {
	$mysubscribe2->subscribe2_options['show_meta'] = '0';
} // option to display link to subscribe2 page from 'meta'

if ( ! isset( $mysubscribe2->subscribe2_options['show_button'] ) ) {
	$mysubscribe2->subscribe2_options['show_button'] = '1';
} // option to show Subscribe2 button on Write page

if ( ! isset( $mysubscribe2->subscribe2_options['ajax'] ) ) {
	$mysubscribe2->subscribe2_options['ajax'] = '0';
} // option to enable an AJAX style form

if ( ! isset( $mysubscribe2->subscribe2_options['widget'] ) ) {
	$mysubscribe2->subscribe2_options['widget'] = '1';
} // option to enable Subscribe2 Widget

if ( ! isset( $mysubscribe2->subscribe2_options['counterwidget'] ) ) {
	$mysubscribe2->subscribe2_options['counterwidget'] = '0';
} // option to enable Subscribe2 Counter Widget

if ( ! isset( $mysubscribe2->subscribe2_options['s2meta_default'] ) ) {
	$mysubscribe2->subscribe2_options['s2meta_default'] = '0';
} // option for Subscribe2 over ride postmeta to be checked by default

if ( ! isset( $mysubscribe2->subscribe2_options['barred'] ) ) {
	$mysubscribe2->subscribe2_options['barred'] = '';
} // option containing domains barred from public registration

if ( ! isset( $mysubscribe2->subscribe2_options['exclude_formats'] ) ) {
	$mysubscribe2->subscribe2_options['exclude_formats'] = '';
} // option for excluding post formats as supported by the current theme

if ( ! isset( $mysubscribe2->subscribe2_options['mailtext'] ) ) {
	$mysubscribe2->subscribe2_options['mailtext'] = __( "{BLOGNAME} has posted a new item, '{TITLE}'\n\n{POST}\n\nYou may view the latest post at\n{PERMALINK}\n\nYou received this e-mail because you asked to be notified when new updates are posted.\nBest regards,\n{MYNAME}\n{EMAIL}", 'subscribe2-for-cp' );
} // Default notification email text

if ( ! isset( $mysubscribe2->subscribe2_options['notification_subject'] ) ) {
	$mysubscribe2->subscribe2_options['notification_subject'] = '[{BLOGNAME}] {TITLE}';
} // Default notification email subject

if ( ! isset( $mysubscribe2->subscribe2_options['confirm_email'] ) ) {
	$mysubscribe2->subscribe2_options['confirm_email'] = __( "{BLOGNAME} has received a request to {ACTION} for this email address. To complete your request please click on the link below:\n\n{LINK}\n\nIf you did not request this, please feel free to disregard this notice!\n\nThank you,\n{MYNAME}.", 'subscribe2-for-cp' );
} // Default confirmation email text

if ( ! isset( $mysubscribe2->subscribe2_options['confirm_subject'] ) ) {
	$mysubscribe2->subscribe2_options['confirm_subject'] = '[{BLOGNAME}] ' . __( 'Please confirm your request', 'subscribe2-for-cp' );
} // Default confirmation email subject

if ( ! isset( $mysubscribe2->subscribe2_options['remind_email'] ) ) {
	$mysubscribe2->subscribe2_options['remind_email'] = __( "This email address was subscribed for notifications at {BLOGNAME} ({BLOGLINK}) but the subscription remains incomplete.\n\nIf you wish to complete your subscription please click on the link below:\n\n{LINK}\n\nIf you do not wish to complete your subscription please ignore this email and your address will be removed from our database.\n\nRegards,\n{MYNAME}", 'subscribe2-for-cp' );
} // Default reminder email text

if ( ! isset( $mysubscribe2->subscribe2_options['remind_subject'] ) ) {
	$mysubscribe2->subscribe2_options['remind_subject'] = '[{BLOGNAME}] ' . __( 'Subscription Reminder', 'subscribe2-for-cp' );
} // Default reminder email subject

if ( ! isset( $mysubscribe2->subscribe2_options['ajax'] ) ) {
	$mysubscribe2->subscribe2_options['ajax'] = '';
} // Default frontend form setting

if ( ! isset( $mysubscribe2->subscribe2_options['js_ip_updater'] ) ) {
	$mysubscribe2->subscribe2_options['js_ip_updater'] = '';
} // Default setting for using javascript to update form ip address
