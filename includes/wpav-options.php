<?php
/*
 * Configuration for the options function
 */
function is_wpav_single() {
   if(!is_multisite())
	return true;
   elseif(is_multisite() && !defined('NETWORK_ADMIN_CONTROL'))
	return true;
   else return false;
}

function get_wpav_options() {
  $blog_email = get_option('admin_email');
  $blog_from_name = get_option('blogname');

  if(is_wpav_single()) {
    $wpav_options = (is_serialized(get_option(WPAV_OPTIONS_SLUG))) ? unserialize(get_option(WPAV_OPTIONS_SLUG)) : get_option(WPAV_OPTIONS_SLUG);
  }
  else {
    $wpav_options = (is_serialized(get_site_option(WPAV_OPTIONS_SLUG))) ? unserialize(get_site_option(WPAV_OPTIONS_SLUG)) : get_site_option(WPAV_OPTIONS_SLUG);
  }

  /**
  * get adminbar items
  *
  */
  if(is_wpav_single()) {
    $adminbar_items = (is_serialized(get_option(WPAV_ADMINBAR_LIST_SLUG))) ? unserialize(get_option(WPAV_ADMINBAR_LIST_SLUG)) : get_option(WPAV_ADMINBAR_LIST_SLUG);
  }
  else {
    $adminbar_items = (is_serialized(get_site_option(WPAV_ADMINBAR_LIST_SLUG))) ? unserialize(get_site_option(WPAV_ADMINBAR_LIST_SLUG)) : get_site_option(WPAV_ADMINBAR_LIST_SLUG);
  }

  //get all admin users
  $admin_users_array = (is_serialized(get_option(WPAV_ADMIN_USERS_SLUG))) ? unserialize(get_option(WPAV_ADMIN_USERS_SLUG)) : get_option(WPAV_ADMIN_USERS_SLUG);

  if(empty($admin_users_array) && !is_array($admin_users_array)) {
    $users_query = new WP_User_Query( array( 'role' => 'Administrator' ) );
    if(isset($users_query) && !empty($users_query)) {
        if ( ! empty( $users_query->results ) ) {
            foreach ( $users_query->results as $user_detail ) {
                $admin_users_array[$user_detail->ID] = $user_detail->data->display_name;
            }
        }
    }
  }

  //get dashboard widgets
  if(is_wpav_single()) {
    $dash_widgets_list = (is_serialized(get_option('wpav_widgets_list'))) ? unserialize(get_option('wpav_widgets_list')) : get_option('wpav_widgets_list');
  }
  else {
    $dash_widgets_list = (is_serialized(get_site_option('wpav_widgets_list'))) ? unserialize(get_site_option('wpav_widgets_list')) : get_site_option('wpav_widgets_list');
  }

  $wpav_dash_widgets = array();
  $wpav_dash_widgets['welcome_panel'] = "Welcome Panel";
  if(!empty($dash_widgets_list)) {
      foreach( $dash_widgets_list as $dash_widget ) {
          $dash_widget_name = (empty($dash_widget[1])) ? $dash_widget[0] : $dash_widget[1];
          $wpav_dash_widgets[$dash_widget[0]] = $dash_widget_name;
      }
  }

  $panel_tabs = array(
      'general' => __( 'General Options', 'wpav' ),
      'login' => __( 'Login Options', 'wpav' ),
      'dash' => __( 'Dashboard Options', 'wpav' ),
      'adminbar' => __( 'Adminbar Options', 'wpav' ),
      'adminop' => __( 'Admin Page Options', 'wpav' ),
      'adminmenu' => __( 'Admin menu Options', 'wpav' ),
      'footer' => __( 'Footer Options', 'wpav' ),
      'email' => __( 'Email Options', 'wpav' ),
      );

  $panel_fields = array();

  //General Options
  $panel_fields[] = array(
      'name' => __( 'General Options', 'wpav' ),
      'type' => 'openTab'
  );

  $panel_fields[] = array(
      'name' => __( 'Choose design type', 'wpav' ),
      'id' => 'design_type',
      'type' => 'radio',
      'options' => array(
          '3' => __( 'Neu Excite (Lite)', 'wpav' ),
          '1' => __( 'Flat design', 'wpav' ),
          '2' => __( 'Default design', 'wpav' ),
      ),
      'default' => '3',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H1 color', 'wpav' ),
      'id' => 'h1_color',
      'type' => 'wpcolor',
      'default' => '#333333',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H2 color', 'wpav' ),
      'id' => 'h2_color',
      'type' => 'wpcolor',
      'default' => '#222222',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H3 color', 'wpav' ),
      'id' => 'h3_color',
      'type' => 'wpcolor',
      'default' => '#222222',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H4 color', 'wpav' ),
      'id' => 'h4_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H5 color', 'wpav' ),
      'id' => 'h5_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

  $panel_fields[] = array(
      'name' => __( 'Heading H6 color', 'wpav' ),
      'id' => 'h6_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

  $panel_fields[] = array(
      'name' => __( 'Remove unwanted items', 'wpav' ),
      'id' => 'admin_generaloptions',
      'type' => 'multicheck',
      'desc' => __( 'Select whichever you want to remove.', 'wpav' ),
      'options' => array(
          '1' => __( 'Wordpress Help tab.', 'wpav' ),
          '2' => __( 'Screen Options.', 'wpav' ),
          '3' => __( 'Wordpress update notifications.', 'wpav' ),
      ),
      );

  $panel_fields[] = array(
      'name' => __( 'Disable automatic updates', 'wpav' ),
      'id' => 'disable_auto_updates',
      'type' => 'checkbox',
      'desc' => __( 'Select to disable all automatic background updates (Not recommended).', 'wpav' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Disable update emails', 'wpav' ),
      'id' => 'disable_update_emails',
      'type' => 'checkbox',
      'desc' => __( 'Select to disable emails regarding automatic updates.', 'wpav' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Hide update notifications', 'wpav' ),
      'id' => 'hide_update_note_plugins',
      'type' => 'checkbox',
      'desc' => __( 'Select to hide update notifications on plugins page (Not recommended).', 'wpav' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Hide Admin bar', 'wpav' ),
      'id' => 'hide_admin_bar',
      'type' => 'checkbox',
      'desc' => __( 'Select to hideadmin bar on frontend.', 'wpav' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Hide Color picker from user profile', 'wpav' ),
      'id' => 'hide_profile_color_picker',
      'type' => 'checkbox',
      'desc' => __( 'Select to hide Color picker from user profile.', 'wpav' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Menu Customization options', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
          'name' => __( 'Menu display', 'wpav' ),
          'id' => 'show_all_menu_to_admin',
          'type' => 'radio',
      'options' => array(
          '1' => __( 'Show all Menu links to all admin users', 'wpav' ),
          '2' => __( 'Show all Menu links to specific admin users', 'wpav' ),
      ),
      );

  $panel_fields[] = array(
      'name' => __( 'Select Privilege users', 'wpav' ),
      'id' => 'privilege_users',
      'type' => 'multicheck',
      'desc' => __( 'Select admin users who can have access to all menu items. Note: Atleast one user must be selected in order to activate Privilege feature.', 'wpav' ),
      'options' => $admin_users_array,
      );


  //Login Options
  $panel_fields[] = array(
      'name' => __( 'Login Options', 'aof' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => __( 'Disable custom styles for login page.', 'wpav' ),
      'id' => 'disable_styles_login',
      'type' => 'checkbox',
      'desc' => __( 'Check to disable', 'wpav' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => __( 'Login page title', 'wpav' ),
      'id' => 'login_page_title',
      'type' => 'text',
      'default' => get_bloginfo('name'),
      );

  $panel_fields[] = array(
      'name' => __( 'Background color', 'wpav' ),
      'id' => 'login_bg_color',
      'type' => 'wpcolor',
      'default' => '#292931',
      );

  $panel_fields[] = array(
      'name' => __( 'External background url', 'wpav' ),
      'id' => 'login_external_bg_url',
      'type' => 'text',
      'desc' => __( 'Load image from external source.', 'wpav' ),
  );

  $panel_fields[] = array(
      'name' => __( 'Background image', 'wpav' ),
      'id' => 'login_bg_img',
      'type' => 'upload',
      );

  $panel_fields[] = array(
      'name' => __( 'Background Repeat', 'wpav' ),
      'id' => 'login_bg_img_repeat',
      'type' => 'checkbox',
      'desc' => __( 'Check to repeat', 'wpav' ),
      'default' => true,
      );

  $panel_fields[] = array(
      'name' => __( 'Scale background image', 'wpav' ),
      'id' => 'login_bg_img_scale',
      'type' => 'checkbox',
      'desc' => __( 'Scale image to fit Screen size.', 'wpav' ),
      'default' => true,
      );

  $panel_fields[] = array(
      'name' => __( 'Login Form Top margin', 'wpav' ),
      'id' => 'login_form_margintop',
      'type' => 'number',
      'default' => '100',
      'min' => '0',
      'max' => '700',
      );

  $panel_fields[] = array(
      'name' => __( 'Login Form Width in %', 'wpav' ),
      'id' => 'login_form_width',
      'type' => 'number',
      'default' => '30',
      'min' => '20',
      'max' => '100',
      );

  $panel_fields[] = array(
      'name' => __( 'External Logo url', 'wpav' ),
      'id' => 'login_external_logo_url',
      'type' => 'text',
      'desc' => __( 'Load image from external source.', 'wpav' ),
  );

  $panel_fields[] = array(
      'name' => __( 'Upload Logo', 'wpav' ),
      'id' => 'admin_login_logo',
      'type' => 'upload',
      'desc' => __( 'Image to be displayed on login page. Maximum width should be under 450pixels.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Resize Logo?', 'wpav' ),
      'id' => 'admin_logo_resize',
      'type' => 'checkbox',
      'default' => false,
      'desc' => __( 'Select to resize logo size.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Set Logo size in %', 'wpav' ),
      'id' => 'admin_logo_size_percent',
      'type' => 'number',
      'default' => '1',
      'max' => '100',
      );

  $panel_fields[] = array(
      'name' => __( 'Logo Height', 'wpav' ),
      'id' => 'admin_logo_height',
      'type' => 'number',
      'default' => '50',
      'max' => '150',
      );

  $panel_fields[] = array(
      'name' => __( 'Logo url', 'wpav' ),
      'id' => 'login_logo_url',
      'type' => 'text',
      'default' => get_bloginfo('url'),
      );

  $panel_fields[] = array(
      'name' => __( 'Transparent Form', 'wpav' ),
      'id' => 'login_divbg_transparent',
      'type' => 'checkbox',
      'default' => false,
      'desc' => __( 'Select to show transparent form background.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Login div bacground color', 'wpav' ),
      'id' => 'login_divbg_color',
      'type' => 'wpcolor',
      'default' => '#f5f5f5',
      );

  $panel_fields[] = array(
      'name' => __( 'Login form bacground color', 'wpav' ),
      'id' => 'login_formbg_color',
      'type' => 'wpcolor',
      'default' => '#423143',
      );

  $panel_fields[] = array(
      'name' => __( 'Form border color', 'wpav' ),
      'id' => 'form_border_color',
      'type' => 'wpcolor',
      'default' => '#e5e5e5',
      );

  $panel_fields[] = array(
      'name' => __( 'Form text color', 'wpav' ),
      'id' => 'form_text_color',
      'type' => 'wpcolor',
      'default' => '#cccccc',
      );

  $panel_fields[] = array(
      'name' => __( 'Form link color', 'wpav' ),
      'id' => 'form_link_color',
      'type' => 'wpcolor',
      'default' => '#777777',
      );

  $panel_fields[] = array(
      'name' => __( 'Form link hover color', 'wpav' ),
      'id' => 'form_link_hover_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

  $panel_fields[] = array(
      'name' => __( 'Hide Back to blog link', 'wpav' ),
      'id' => 'hide_backtoblog',
      'type' => 'checkbox',
      'default' => false,
      'desc' => __( 'select to hide', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Hide Remember me', 'wpav' ),
      'id' => 'hide_remember',
      'type' => 'checkbox',
      'default' => false,
      'desc' => __( 'select to hide', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Custom Footer content', 'wpav' ),
      'id' => 'login_footer_content',
      'type' => 'wpeditor',
      );

  $panel_fields[] = array(
      'name' => __( 'Custom CSS', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Custom CSS for Login page', 'wpav' ),
      'id' => 'login_custom_css',
      'type' => 'textarea',
      );


  //Dash Options
  $panel_fields[] = array(
      'name' => __( 'Dashboard Options', 'aof' ),
      'type' => 'openTab'
      );

  if(!empty($wpav_dash_widgets) && is_array($wpav_dash_widgets)) {
      $panel_fields[] = array(
          'name' => __( 'Remove unwanted Widgets', 'wpav' ),
          'id' => 'remove_dash_widgets',
          'type' => 'multicheck',
          'desc' => __( 'Select whichever you want to remove.', 'wpav' ),
          'options' => $wpav_dash_widgets,
          );
  }

  $panel_fields[] = array(
      'name' => __( 'Create New Widgets', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'type' => 'note',
      'desc' => __( 'Widget 1', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Type', 'wpav' ),
      'id' => 'wpav_widget_1_type',
      'options' => array(
          '1' => __( 'RSS Feed', 'wpav' ),
          '2' => __( 'Text Content', 'wpav' ),
          '3' => __( 'Video Content', 'wpav' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
    'name' => __( 'Widget Position', 'wpav' ),
    'id' => 'wpav_widget_1_position',
    'options' => array(
        'normal' => __( 'Left', 'wpav' ),
        'side' => __( 'Right', 'wpav' ),
    ),
    'type' => 'select',
  );

  $panel_fields[] = array(
      'name' => __( 'Widget Title', 'wpav' ),
      'id' => 'wpav_widget_1_title',
      'type' => 'text',
  );

  $panel_fields[] = array(
      'name' => __( 'RSS Feed url', 'wpav' ),
      'id' => 'wpav_widget_1_rss',
      'type' => 'text',
      'desc' => __( 'Put your RSS feed url here if you want to show your own RSS feeds. Otherwise fill your static contents in the below editor.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Content', 'wpav' ),
      'id' => 'wpav_widget_1_content',
      'type' => 'wpeditor',
      );

  $panel_fields[] = array(
      'type' => 'note',
      'desc' => __( 'Widget 2', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Type', 'wpav' ),
      'id' => 'wpav_widget_2_type',
      'options' => array(
          '1' => __( 'RSS Feed', 'wpav' ),
          '2' => __( 'Text Content', 'wpav' ),
          '3' => __( 'Video Content', 'wpav' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
          'name' => __( 'Widget Position', 'wpav' ),
          'id' => 'wpav_widget_2_position',
      'options' => array(
          'normal' => __( 'Left', 'wpav' ),
          'side' => __( 'Right', 'wpav' ),
      ),
      'type' => 'select',
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Title', 'wpav' ),
      'id' => 'wpav_widget_2_title',
      'type' => 'text',
      );

  $panel_fields[] = array(
      'name' => __( 'RSS Feed url', 'wpav' ),
      'id' => 'wpav_widget_2_rss',
      'type' => 'text',
      'desc' => __( 'Put your RSS feed url here if you want to show your own RSS feeds. Otherwise fill your static contents in the below editor.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Content', 'wpav' ),
      'id' => 'wpav_widget_2_content',
      'type' => 'wpeditor',
      );

  $panel_fields[] = array(
      'type' => 'note',
      'desc' => __( 'Widget 3', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Type', 'wpav' ),
      'id' => 'wpav_widget_3_type',
      'options' => array(
          '1' => __( 'RSS Feed', 'wpav' ),
          '2' => __( 'Text Content', 'wpav' ),
          '3' => __( 'Video Content', 'wpav' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Position', 'wpav' ),
      'id' => 'wpav_widget_3_position',
      'options' => array(
          'normal' => __( 'Left', 'wpav' ),
          'side' => __( 'Right', 'wpav' ),
      ),
      'type' => 'select',
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Title', 'wpav' ),
      'id' => 'wpav_widget_3_title',
      'type' => 'text',
      );

  $panel_fields[] = array(
      'name' => __( 'RSS Feed url', 'wpav' ),
      'id' => 'wpav_widget_3_rss',
      'type' => 'text',
      'desc' => __( 'Put your RSS feed url here if you want to show your own RSS feeds. Otherwise fill your static contents in the below editor.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Content', 'wpav' ),
      'id' => 'wpav_widget_3_content',
      'type' => 'wpeditor',
      );

  $panel_fields[] = array(
      'type' => 'note',
      'desc' => __( 'Widget 4', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Type', 'wpav' ),
      'id' => 'wpav_widget_4_type',
      'options' => array(
          '1' => __( 'RSS Feed', 'wpav' ),
          '2' => __( 'Text Content', 'wpav' ),
          '3' => __( 'Video Content', 'wpav' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Position', 'wpav' ),
      'id' => 'wpav_widget_4_position',
      'options' => array(
          'normal' => __( 'Left', 'wpav' ),
          'side' => __( 'Right', 'wpav' ),
      ),
      'type' => 'select',
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Title', 'wpav' ),
      'id' => 'wpav_widget_4_title',
      'type' => 'text',
      );

  $panel_fields[] = array(
      'name' => __( 'RSS Feed url', 'wpav' ),
      'id' => 'wpav_widget_4_rss',
      'type' => 'text',
      'desc' => __( 'Put your RSS feed url here if you want to show your own RSS feeds. Otherwise fill your static contents in the below editor.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Widget Content', 'wpav' ),
      'id' => 'wpav_widget_4_content',
      'type' => 'wpeditor',
      );


  //AdminBar Options
  $panel_fields[] = array(
      'name' => __( 'Adminbar Options', 'aof' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => __( 'External Logo url', 'wpav' ),
      'id' => 'adminbar_external_logo_url',
      'type' => 'text',
      'desc' => __( 'Load image from external source. Maximum size 200x50 pixels.', 'wpav' ),
  );

  $panel_fields[] = array(
      'name' => __( 'Upload Logo', 'wpav' ),
      'id' => 'admin_logo',
      'type' => 'upload',
      'desc' => __( 'Image to be displayed in all pages. Maximum size 200x50 pixels.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Logo link', 'wpav' ),
      'id' => 'adminbar_logo_link',
      'type' => 'text',
      'desc' => __( 'If empty it will default to admin dashboard url.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Resize Logo?', 'wpav' ),
      'id' => 'adminbar_logo_resize',
      'type' => 'checkbox',
      'default' => false,
      'desc' => __( 'Select to resize logo size.', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Set Logo size in %', 'wpav' ),
      'id' => 'adminbar_logo_size_percent',
      'type' => 'number',
      'default' => '75',
      'max' => '100',
      );

  $panel_fields[] = array(
      'name' => __( 'Move logo Top by', 'wpav' ),
      'id' => 'logo_top_margin',
      'type' => 'number',
      'desc' => __( "Can be used in case of logo position haven't matched the menu position.", 'wpav' ),
      'default' => '0',
      'max' => '20',
      );

  $panel_fields[] = array(
      'name' => __( 'Move logo Bottom by', 'wpav' ),
      'id' => 'logo_bottom_margin',
      'type' => 'number',
      'desc' => __( "Can be used in case of logo position haven't matched the menu position.", 'wpav' ),
      'default' => '0',
      'max' => '20',
      );

  $panel_fields[] = array(
      'name' => __( 'Move logo right by', 'wpav' ),
      'id' => 'logo_left_margin',
      'type' => 'number',
      'desc' => __( "Can be used in case of logo position haven't matched the menu position.", 'wpav' ),
      'default' => '0',
      'max' => '150',
      );

  $panel_fields[] = array(
      'name' => __( 'Admin bar color', 'wpav' ),
      'id' => 'admin_bar_color',
      'type' => 'wpcolor',
      'default' => '#fff',
      );

  $panel_fields[] = array(
      'name' => __( 'Menu Link color', 'wpav' ),
      'id' => 'admin_bar_menu_color',
      'type' => 'wpcolor',
      'default' => '#94979B',
      );

  $panel_fields[] = array(
      'name' => __( 'Menu Link hover color', 'wpav' ),
      'id' => 'admin_bar_menu_hover_color',
      'type' => 'wpcolor',
      'default' => '#474747',
      );

  $panel_fields[] = array(
      'name' => __( 'Menu background hover/Sub menu color', 'wpav' ),
      'id' => 'admin_bar_menu_bg_hover_color',
      'type' => 'wpcolor',
      'default' => '#f4f4f4',
      );

  $panel_fields[] = array(
      'name' => __( 'Submenu Link color', 'wpav' ),
      'id' => 'admin_bar_sbmenu_link_color',
      'type' => 'wpcolor',
      'default' => '#666666',
      );

  $panel_fields[] = array(
      'name' => __( 'Submenu Link hover color', 'wpav' ),
      'id' => 'admin_bar_sbmenu_link_hover_color',
      'type' => 'wpcolor',
      'default' => '#333333',
      );

  if(!empty($adminbar_items)) {
    $panel_fields[] = array(
        'name' => __( 'Remove Unwanted Menus', 'wpav' ),
        'id' => 'hide_admin_bar_menus',
        'type' => 'multicheck',
        'desc' => __( 'Select menu items to remove.', 'wpav' ),
        'options' => $adminbar_items,
        );
  }

  //Admin Options
  $panel_fields[] = array(
      'name' => __( 'Admin Page Options', 'aof' ),
      'type' => 'openTab'
  );

  $panel_fields[] = array(
      'name' => __( 'Page background color', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Background color', 'wpav' ),
      'id' => 'bg_color',
      'type' => 'wpcolor',
      'default' => '#e3e7ea',
      );

  $panel_fields[] = array(
      'name' => __( 'Primary button colors', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Button background  color', 'wpav' ),
      'id' => 'pry_button_color',
      'type' => 'wpcolor',
      'default' => '#7ac600',
      );

  if(isset($wpav_options['design_type']) && $wpav_options['design_type'] != 1) {
  $panel_fields[] = array(
      'name' => __( 'Button border color', 'wpav' ),
      'id' => 'pry_button_border_color',
      'type' => 'wpcolor',
      'default' => '#86b520',
      );

  $panel_fields[] = array(
      'name' => __( 'Button shadow color', 'wpav' ),
      'id' => 'pry_button_shadow_color',
      'type' => 'wpcolor',
      'default' => '#98ce23',
      );
      }

  $panel_fields[] = array(
      'name' => __( 'Button text color', 'wpav' ),
      'id' => 'pry_button_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Button hover background color', 'wpav' ),
      'id' => 'pry_button_hover_color',
      'type' => 'wpcolor',
      'default' => '#29ac39',
      );

  if(isset($wpav_options['design_type']) && $wpav_options['design_type'] != 1) {
  $panel_fields[] = array(
      'name' => __( 'Button hover border color', 'wpav' ),
      'id' => 'pry_button_hover_border_color',
      'type' => 'wpcolor',
      'default' => '#259633',
      );

  $panel_fields[] = array(
      'name' => __( 'Button hover shadow color', 'wpav' ),
      'id' => 'pry_button_hover_shadow_color',
      'type' => 'wpcolor',
      'default' => '#3d7a0c',
      );
      }

  $panel_fields[] = array(
      'name' => __( 'Button hover text color', 'wpav' ),
      'id' => 'pry_button_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Secondary button colors', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Button background color', 'wpav' ),
      'id' => 'sec_button_color',
      'type' => 'wpcolor',
      'default' => '#ced6c9',
      );

  if(isset($wpav_options['design_type']) && $wpav_options['design_type'] != 1) {
  $panel_fields[] = array(
      'name' => __( 'Button border color', 'wpav' ),
      'id' => 'sec_button_border_color',
      'type' => 'wpcolor',
      'default' => '#bdc4b8',
      );

  $panel_fields[] = array(
      'name' => __( 'Button shadow color', 'wpav' ),
      'id' => 'sec_button_shadow_color',
      'type' => 'wpcolor',
      'default' => '#dde5d7',
      );
      }

  $panel_fields[] = array(
      'name' => __( 'Button text color', 'wpav' ),
      'id' => 'sec_button_text_color',
      'type' => 'wpcolor',
      'default' => '#7a7a7a',
      );

  $panel_fields[] = array(
      'name' => __( 'Button hover background color', 'wpav' ),
      'id' => 'sec_button_hover_color',
      'type' => 'wpcolor',
      'default' => '#c9c8bf',
      );

  if(isset($wpav_options['design_type']) && $wpav_options['design_type'] != 1) {
  $panel_fields[] = array(
      'name' => __( 'Button hover border color', 'wpav' ),
      'id' => 'sec_button_hover_border_color',
      'type' => 'wpcolor',
      'default' => '#babab0',
      );

  $panel_fields[] = array(
      'name' => __( 'Button hover shadow color', 'wpav' ),
      'id' => 'sec_button_hover_shadow_color',
      'type' => 'wpcolor',
      'default' => '#9ea59b',
      );
      }

  $panel_fields[] = array(
      'name' => __( 'Button hover text color', 'wpav' ),
      'id' => 'sec_button_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Add New button', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Button background color', 'wpav' ),
      'id' => 'addbtn_bg_color',
      'type' => 'wpcolor',
      'default' => '#53D860',
      );

  $panel_fields[] = array(
      'name' => __( 'Button hover background color', 'wpav' ),
      'id' => 'addbtn_hover_bg_color',
      'type' => 'wpcolor',
      'default' => '#5AC565',
      );

  $panel_fields[] = array(
      'name' => __( 'Button text color', 'wpav' ),
      'id' => 'addbtn_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Button hover text color', 'wpav' ),
      'id' => 'addbtn_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Metabox Colors', 'wpav' ),
      'type' => 'title',
  );

  $panel_fields[] = array(
      'name' => __( 'Metabox header box', 'wpav' ),
      'id' => 'metabox_h3_color',
      'type' => 'wpcolor',
      'default' => '#bdbdbd',
      );

  $panel_fields[] = array(
      'name' => __( 'Metabox header box border', 'wpav' ),
      'id' => 'metabox_h3_border_color',
      'type' => 'wpcolor',
      'default' => '#9e9e9e',
      );

  $panel_fields[] = array(
      'name' => __( 'Metabox header Click button color', 'wpav' ),
      'id' => 'metabox_handle_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Metabox header Click button hover color', 'wpav' ),
      'id' => 'metabox_handle_hover_color',
      'type' => 'wpcolor',
      'default' => '#949494',
      );

  $panel_fields[] = array(
      'name' => __( 'Metabox header text color', 'wpav' ),
      'id' => 'metabox_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Message box (Post/Page updates)', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Message box color', 'wpav' ),
      'id' => 'msg_box_color',
      'type' => 'wpcolor',
      'default' => '#02c5cc',
      );

  $panel_fields[] = array(
      'name' => __( 'Message text color', 'wpav' ),
      'id' => 'msgbox_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Message box border color', 'wpav' ),
      'id' => 'msgbox_border_color',
      'type' => 'wpcolor',
      'default' => '#007e87',
      );

  $panel_fields[] = array(
      'name' => __( 'Message link color', 'wpav' ),
      'id' => 'msgbox_link_color',
      'type' => 'wpcolor',
      'default' => '#efefef',
      );

  $panel_fields[] = array(
      'name' => __( 'Message link hover color', 'wpav' ),
      'id' => 'msgbox_link_hover_color',
      'type' => 'wpcolor',
      'default' => '#e5e5e5',
      );

  $panel_fields[] = array(
      'name' => __( 'Custom CSS', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Custom CSS for Admin pages', 'wpav' ),
      'id' => 'admin_page_custom_css',
      'type' => 'textarea',
      );

  //Admin menu Options
  $panel_fields[] = array(
      'name' => __( 'Admin menu Options', 'aof' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => __( 'Admin menu width', 'wpav' ),
      'id' => 'admin_menu_width',
      'type' => 'number',
      'default' => '200',
      'min' => '180',
      'max' => '400',
      );

  // $panel_fields[] = array(
  //     'name' => __( 'Force disable Image/SVG admin menu icons.', 'wpav' ),
  //     'id' => 'disable_img_svg_adminmenu_icons',
  //     'type' => 'checkbox',
  //     'desc' => __( 'Select to remove all Image/SVG admin menu icons.', 'wpav' ),
  //     'default' => false,
  //     );

  $panel_fields[] = array(
      'name' => __( 'Admin Menu Color options', 'wpav' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => __( 'Left menu wrap color', 'wpav' ),
      'id' => 'nav_wrap_color',
      'type' => 'wpcolor',
      'default' => '#1b2831',
      );

  $panel_fields[] = array(
      'name' => __( 'Menu hover color', 'wpav' ),
      'id' => 'hover_menu_color',
      'type' => 'wpcolor',
      'default' => '#3f4457',
      );

  $panel_fields[] = array(
      'name' => __( 'Menu text color', 'wpav' ),
      'id' => 'nav_text_color',
      'type' => 'wpcolor',
      'default' => '#90a1a8',
      );

  $panel_fields[] = array(
      'name' => __( 'Menu hover text color', 'wpav' ),
      'id' => 'menu_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Current active Menu color', 'wpav' ),
      'id' => 'active_menu_color',
      'type' => 'wpcolor',
      'default' => '#6da87a',
      );

  $panel_fields[] = array(
      'name' => __( 'Active Menu text color', 'wpav' ),
      'id' => 'menu_active_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Submenu wrap color', 'wpav' ),
      'id' => 'sub_nav_wrap_color',
      'type' => 'wpcolor',
      'default' => '#22303a',
      );

  $panel_fields[] = array(
      'name' => __( 'Submenu hover color', 'wpav' ),
      'id' => 'sub_nav_hover_color',
      'type' => 'wpcolor',
      'default' => '#22303a',
      );

  $panel_fields[] = array(
      'name' => __( 'Submenu text color', 'wpav' ),
      'id' => 'sub_nav_text_color',
      'type' => 'wpcolor',
      'default' => '#17b7b2',
      );

  $panel_fields[] = array(
      'name' => __( 'Submenu hover text color', 'wpav' ),
      'id' => 'sub_nav_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#17b7b2',
      );

  $panel_fields[] = array(
      'name' => __( 'Active submenu text color', 'wpav' ),
      'id' => 'submenu_active_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => __( 'Updates Count notification background', 'wpav' ),
      'id' => 'menu_updates_count_bg',
      'type' => 'wpcolor',
      'default' => '#212121',
      );

  $panel_fields[] = array(
      'name' => __( 'Updates Count text color', 'wpav' ),
      'id' => 'menu_updates_count_text',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );




  //Footer Options
  $panel_fields[] = array(
      'name' => __( 'Footer Options', 'aof' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => __( 'Footer Text', 'wpav' ),
      'id' => 'admin_footer_txt',
      'type' => 'wpeditor',
      'desc' => __( 'Put any text you want to show on admin footer.', 'wpav' ),
      );


  //Email Options
  $panel_fields[] = array(
      'name' => __( 'Email Options', 'aof' ),
      'type' => 'openTab'
  );

  $panel_fields[] = array(
      'name' => __( 'White Label emails', 'wpav' ),
      'id' => 'email_settings',
      'options' => array(
          '3' => __( 'Disable White Label emails', 'wpav' ),
          '1' => sprintf( __( 'Set Email address as <strong> %1$s </strong> From name as <strong> %2$s', 'wpav' ), $blog_email, $blog_from_name ),
          '2' => __( 'Set different', 'wpav' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
      'name' => __( 'Email From address', 'wpav' ),
      'id' => 'email_from_addr',
      'type' => 'text',
      'desc' => __( 'Enter valid email address', 'wpav' ),
      );

  $panel_fields[] = array(
      'name' => __( 'Email From name', 'wpav' ),
      'id' => 'email_from_name',
      'type' => 'text',
      );

  $output = array('wpav_tabs' => $panel_tabs, 'wpav_fields' => $panel_fields);
  return $output;
}
