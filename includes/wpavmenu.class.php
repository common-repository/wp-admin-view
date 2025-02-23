<?php
/*
 * WPAV
 * @author   Krish Johnson  krishjohnson
 * @url     http://100utils.com
*/

defined('ABSPATH') || die;

if(!class_exists('CUSTOMIZEADMINMENU')) {
    class CUSTOMIZEADMINMENU extends WPAV {
        private $magic_priority = PHP_INT_MAX;
        private $custom_menu = array();
        private $custom_submenu = array();
        private $debug = 0;

        function __construct()
        {
            $this->aof_options = parent::get_wpav_option_data(WPAV_OPTIONS_SLUG);
            add_action('admin_init', array($this, 'initialize_default_menu'), 9);
            add_action('admin_head', array($this, 'wpav_load_fa_icons'), 998);
            add_action('admin_menu', array($this, 'add_admin_management_menu'));
            add_action('admin_enqueue_scripts', array($this, 'load_menu_assets'));
            add_action('plugins_loaded', array($this, 'save_menu_data'));
            add_filter('parent_file', array($this, 'replace_wp_menu'));
        }

        function initialize_default_menu(){
            global $menu, $submenu;
            $this->wp_df_menu = $menu;
            $this->wp_df_submenu = $submenu;
        }

        public function add_admin_management_menu()
        {
            add_submenu_page( WPAV_MENU_SLUG , __('Manage Admin Menu', 'wpav'), __('Manage Admin menu', 'wpav'), 'manage_options', 'admin_menu_management', array($this, 'wpav_admin_menu_management') );
        }

        public function load_menu_assets($nowpage)
        {
          if($nowpage == 'wp-admin-view_page_admin_menu_management') {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script( 'wpav-sorting', WPAV_DIR_URI . 'assets/js/sortjs.js', array( 'jquery' ), '', true );
            wp_enqueue_style('iconPicker-styles', WPAV_DIR_URI . 'assets/icon-picker/css/icon-picker.css', '', WPAV_VERSION);
            wp_enqueue_script( 'iconPicker', WPAV_DIR_URI . 'assets/icon-picker/js/icon-picker.js', array( 'jquery' ), '', true );
          }
        }

        function save_menu_data() {
            if(isset($_POST['alter_menu_order'])) {
                $custom_menu_data = array();$saved_data = array();
                $custom_menu_data = $_POST;
                $saved_data = parent::get_wpav_option_data(WPAV_OPTIONS_SLUG);
                if($saved_data)
                    $data = array_merge($saved_data, $custom_menu_data);
                else
                    $data = $custom_menu_data;
                parent::updateOption(WPAV_OPTIONS_SLUG, $data);
                wp_safe_redirect( admin_url( 'admin.php?page=admin_menu_management' ) );
                exit();
            }
        }

        function order_menu_page($menu, $submenu)
        {
            $tmenu = $menu;
            $tsubmenu = $submenu;
            if (isset($this->aof_options['custom_admin_menu']['top_menu']) && !empty($this->aof_options['custom_admin_menu']['top_menu'])) {
                $current_user_role = parent::wpav_get_user_role();
                $current_user_id = get_current_user_id();
                $wpav_menu_access = $this->aof_options['show_all_menu_to_admin'];
                $wpav_privilege_users = (!empty($this->aof_options['privilege_users'])) ? $this->aof_options['privilege_users'] : array();
                $toporder = isset($this->aof_options['custom_admin_menu']['top_menu']) ? $this->aof_options['custom_admin_menu']['top_menu'] : "";
                $multiorder = isset($this->aof_options['custom_admin_menu']['sub_menu']) ? $this->aof_options['custom_admin_menu']['sub_menu'] : "";
                $topmenutitle = isset($this->aof_options['custom_admin_menu']['top_menu_title']) ? $this->aof_options['custom_admin_menu']['top_menu_title'] : "";
                $topmenuicon = isset($this->aof_options['custom_admin_menu']['menu_icon']) ? $this->aof_options['custom_admin_menu']['menu_icon'] : "";
                $submenutitle = isset($this->aof_options['custom_admin_menu']['sub_menu_title']) ? $this->aof_options['custom_admin_menu']['sub_menu_title'] : "";
                $topmenuhide = isset($this->aof_options['custom_admin_menu']['top_menu_hide']) ? $this->aof_options['custom_admin_menu']['top_menu_hide'] : array();
                $submenuhide = isset($this->aof_options['custom_admin_menu']['sub_menu_hide']) ? $this->aof_options['custom_admin_menu']['sub_menu_hide'] : array();

                // top menu custom order sort
                $menuorder = $this->cleanArray($toporder);
                array_push($menuorder, 'profile.php');

                $debug = $this->debug;

                usort($menu, function ($a, $b) use ($menuorder, $debug){
                    $pos_a = array_search(esc_html(html_entity_decode($a['2'])), $menuorder);
                    $pos_b = array_search(esc_html(html_entity_decode($b['2'])), $menuorder);

                    if($pos_a === false || $pos_b === false) {
                        //$out = self::getSubmenuParentSlug('edit-comments.php', $tmenu, $tsubmenu);
                        if ($debug) {
                            var_dump($a['2'].'----->'.$b['2'].'---->menu');
                            var_dump(esc_html(html_entity_decode($a['2'])). '----->'.esc_html(html_entity_decode($b['2'])));
                            var_dump($pos_a.'---->'.$pos_b);
                        }
                    }
                    return $pos_a - $pos_b;
                });


                // submenu loop
                foreach($menu as $key => &$value) {
                    // set custom title for top menu
                    if (isset($topmenutitle[$value[2]]) && !empty($topmenutitle[$value[2]])){
                        $value[0] = $topmenutitle[$value[2]];
                    }
                    // set custom icon for top menu
                    if (isset($topmenuicon[$value[2]]) && !empty($topmenuicon[$value[2]])){
                        $value[4] = str_replace('menu-icon-', 'wpav-menu-icon-', $value[4]);
                        $value[4] = str_replace('toplevel_page', 'wpav-icon-selected wpav-toplevel_page', $value[4]);
                        $iconType = explode("|", $topmenuicon[$value[2]]);
                        if($iconType[1] != "dashicons-blank") {
                            if($iconType[0] == "dashicons") {
                                $value[6] = trim($iconType[1]);
                            }
                            else {
                                $value[6] = "dashicons-" . $iconType[1];
                            }
                        }
                    }


                    // hide top menus as per roles
                    if (isset($topmenuhide[$value[2]]) && !empty($topmenuhide[$value[2]])) {

                      if(is_super_admin($current_user_id)) {
                        if(isset($wpav_menu_access) && $wpav_menu_access == 2 && !empty($wpav_privilege_users) && !in_array($current_user_id, $wpav_privilege_users)
                        && array_key_exists($current_user_role, $topmenuhide[$value[2]])) {
                          $this->hide_menu[$key] = $menu[$key];
                          unset($menu[$key]);
                        }
                      }
                      elseif (array_key_exists($current_user_role, $topmenuhide[$value[2]])) {
                          $this->hide_menu[$key] = $menu[$key];
                          unset($menu[$key]);
                      }


                    }

                    //fix for remove vc menu
                    if(isset($value[2]) && $value[2] == "vc-welcome") { //if top menu slug is vcwelcome, definitely it's not an administrator user
                      $if_vc_general_hidden = isset($this->aof_options['custom_admin_menu']['top_menu_hide']['vc-general']) ?
                          $this->aof_options['custom_admin_menu']['top_menu_hide']['vc-general'] : array();
                          if(!empty($if_vc_general_hidden) && array_key_exists($current_user_role, $if_vc_general_hidden)) {
                            unset($menu[$key]);
                          }
                    }

                    //fix for remove profile.php
                    if(isset($value[2]) && $value[2] == "profile.php") { //if top menu slug is vcwelcome, definitely it's not an administrator user
                      $if_profile_hidden = isset($this->aof_options['custom_admin_menu']['sub_menu_hide']['users.php']['profile.php']) ?
                          $this->aof_options['custom_admin_menu']['sub_menu_hide']['users.php']['profile.php'] : array();
                          if(!empty($if_profile_hidden) && array_key_exists($current_user_role, $if_profile_hidden)) {
                            unset($menu[$key]);
                          }
                    }



                    // sub menu custom order sort
                    if (isset($submenu[$value[2]]) && !empty($submenu[$value[2]]) ) {
                      if (isset($multiorder[$value[2]])) {
                           $sortorder = $this->cleanArray($multiorder[$value[2]]);
                           // submenu custom order sort
                           usort($submenu[$value[2]], function ($a, $b) use ($sortorder, $debug){
                               $pos_a = array_search(esc_html(html_entity_decode($a['2'])), $sortorder);
                               $pos_b = array_search(esc_html(html_entity_decode($b['2'])), $sortorder);

                               if($pos_a === false || $pos_b === false) {
                                    if ($debug) {
                                        var_dump($sortorder);
                                        var_dump($a['2'].'----->'.$b['2'].'---->submenu');
                                        var_dump(esc_html(html_entity_decode($a['2'])). '----->'.esc_html(html_entity_decode($b['2'])));
                                        var_dump($pos_a.'----->'.$pos_b);
                                    }
                               }
                               return $pos_a - $pos_b;
                           });
                           $sortorder = array();
                        }


                        foreach($submenu[$value[2]] as $sub_key => &$sub_value) {
                            if (isset($submenutitle[$value[2]][$sub_value['2']]) && !empty($submenutitle[$value[2]][$sub_value['2']])){
                                $sub_value[0] = $submenutitle[$value[2]][$sub_value['2']];
                            }

                             //hiding sub menus
                            if(is_super_admin($current_user_id)) {
                              if(isset($wpav_menu_access) && $wpav_menu_access == 2 && !empty($wpav_privilege_users) && !in_array($current_user_id, $wpav_privilege_users) &&
                               isset($submenuhide[$value[2]][(html_entity_decode($sub_value[2]))]) && !empty($submenuhide[$value[2]][(html_entity_decode($sub_value[2]))])) {
                                if(array_key_exists($current_user_role, $submenuhide[$value[2]][(html_entity_decode($sub_value[2]))])) {
                                  $this->hide_submenu[$value[2]][$sub_key] = $submenu[$value[2]][$sub_key];
                                  unset($submenu[$value[2]][$sub_key]);
                                }
                              }
                           }
                            elseif (isset($submenuhide[$value[2]][(html_entity_decode($sub_value[2]))]) && !empty($submenuhide[$value[2]][(html_entity_decode($sub_value[2]))])) {
                                if (array_key_exists($current_user_role, $submenuhide[$value[2]][(html_entity_decode($sub_value[2]))])) {
                                    $this->hide_submenu[$value[2]][$sub_key] = $submenu[$value[2]][$sub_key];
                                    unset($submenu[$value[2]][$sub_key]);
                                }
                            }
                        }
                    }
                }
            }

            return array($menu, $submenu);
        }

        public function replace_wp_menu($parent_file = '')
        {
           //if(!empty($this->aof_options['disable_menu_customize']) && $this->aof_options['disable_menu_customize'] == 1)
            //  return;

            global $menu, $submenu,$submenu_file;
            if ($this->aof_options) {
               //list($menu, $submenu) = $this->addMenuItem($menu, $submenu);
               list($menu, $submenu) = $this->order_menu_page($menu, $submenu);
            }

            return $parent_file;
        }

        public function addMenuItem($menu, $submenu)
        {
            global $_registered_pages, $_wp_submenu_nopriv, $_wp_menu_nopriv, $submenu_file,$pagenow,$admin_page_hooks,$_parent_pages,$_wp_real_parent_file, $wp_filter;
            $rm_topmenu = array();

            if ($this->aof_options) {
                $toporder = isset($this->aof_options['custom_admin_menu']['top_menu']) ? $this->aof_options['custom_admin_menu']['top_menu'] : "";
                $multiorder = isset($this->aof_options['custom_admin_menu']['sub_menu']) ? $this->aof_options['custom_admin_menu']['sub_menu'] : "";
                $topmenutitle = isset($this->aof_options['custom_admin_menu']['top_menu_title'])? $this->aof_options['custom_admin_menu']['top_menu_title']: "";
                $topmenuicon = isset($this->aof_options['custom_admin_menu']['menu_icon']) ? $this->aof_options['custom_admin_menu']['menu_icon'] : "";
                $submenutitle = isset($this->aof_options['custom_admin_menu']['sub_menu_title']) ? $this->aof_options['custom_admin_menu']['sub_menu_title'] : "";
                $current_user_role = parent::wpav_get_user_role();
                if (isset($toporder) && !empty($toporder)){
                    foreach($toporder as $key => $item) {
                        $current = self::istopmenu($item, $menu);
                        $subcurrent = (empty($current) ) ? self::getSubmenuParentSlug($item, $menu, $submenu) : "";

                        if ( current_user_can( $current[1] ) || current_user_can($subcurrent[1])){
                            unset($subcurrent);
                            if (empty($current)) {
                                $subcurrent = self::getSubmenuParentSlug($item, $menu, $submenu);
                                if(isset($subcurrent) && !empty($subcurrent)) {
                                    unset($submenu[$subcurrent[count($subcurrent)-1]][$subcurrent[count($subcurrent)-2]]);
                                    $menuicon = isset($topmenuicon[$item]) ? self::menuicon($topmenuicon[$item]) : self::menuicon('');
                                    $prev_hookname = get_plugin_page_hookname($subcurrent[2], $subcurrent[count($subcurrent)-1] );
                                    $admin_page_hooks[$item] = sanitize_title( $subcurrent[0] );
                                    $hookname = get_plugin_page_hookname($item, '');

                                    if (isset($wp_filter[$prev_hookname])) {
                                        $function = self::dump_hook($prev_hookname, $wp_filter[$prev_hookname]);
                                        $this->add_hook_function($hookname, $function, $item);
                                    }

                                    $custom_top_menu[] = array($subcurrent[0], $subcurrent[1], $item, $subcurrent[3], 'menu-top'. ' '.$hookname, $hookname, $menuicon);
                                    array_splice($menu, $key, 0, $custom_top_menu);
                                    $_registered_pages[$hookname] = true;
                                    $_parent_pages[$item] = false;
                                    unset($custom_top_menu);
                                }
                                if(!$current && !$subcurrent) {
                                    $menuicon = self::menuicon($topmenuicon[$item]);
                                    $menu_title = $topmenutitle[$item];
                                    $hookname = get_plugin_page_hookname($item, '');
                                    $custom_top_menu[] = array($menu_title, 'read', $item, $menu_title, 'menu-top'. ' '.$hookname, $hookname, $menuicon);
                                    array_splice($menu, $key, 0, $custom_top_menu);
                                    $_registered_pages[$hookname] = true;
                                    $_parent_pages[$item] = false;
                                    unset($custom_top_menu);
                                }
                                unset($subcurrent);
                            }
                            unset($current);

                            if (isset($multiorder[$item]) && !empty($multiorder[$item])) {
                                foreach($multiorder[$item] as $skey => $sitem) {
                                    $subcurrent = self::issubmenu($sitem, $item, $submenu);
                                    $titles = (empty($subcurrent)) ? self::istopmenu($sitem, $menu) : "";

                                    if ( current_user_can( $subcurrent[1] ) || current_user_can($titles[1])){
                                        unset($titles);
                                        if (empty($subcurrent)) {
                                            $_parent_pages[$sitem] = $item;
                                            $titles = self::istopmenu($sitem, $menu);
                                            if ($item != $sitem){


                                            if ($titles) {
                                                //unset($menu[$titles[count($titles)-1]]);
                                                if (!in_array($titles[2], $rm_topmenu)){
                                                    $rm_topmenu[$titles[count($titles)-1]] = $titles[2];
                                                }
                                                $custom_sub_menu[] = array($titles[0], $titles[1], $sitem);
                                                if(isset($submenu[$item]) && !empty($submenu[$item])) {
                                                    array_splice($submenu[$item], $skey, 0, $custom_sub_menu);
                                                } else {
                                                    $submenu[$item] =  $custom_sub_menu;
                                                }
                                                unset($custom_sub_menu);

                                                $hookname = get_plugin_page_hookname($sitem, $item);
                                                $_registered_pages[$hookname] = true;
                                                $prev_hookname = get_plugin_page_hookname( $sitem, $titles[2]);

                                                if (isset($wp_filter[$prev_hookname])) {
                                                    $function = self::dump_hook($prev_hookname,$wp_filter[$prev_hookname]);
                                                    $this->add_hook_function($hookname, $function, $sitem);
                                                }
                                            } else {
                                                $capa_titles = self::istopmenu($item, $menu);
                                                $anothersub = self::getSubmenuParentSlug($sitem, $menu, $submenu);

                                                if(isset($anothersub) && !empty($anothersub)) {
                                                    unset($submenu[$anothersub[count($anothersub)-1]][$anothersub[count($anothersub)-2]]);
                                                    $custom_sub_menu[] = array($anothersub[0], $capa_titles[1], $sitem);
                                                    if(isset($submenu[$item]) && !empty($submenu[$item])) {
                                                        array_splice($submenu[$item], $skey, 0, $custom_sub_menu);
                                                    } else {
                                                        $submenu[$item] =  $custom_sub_menu;
                                                    }

                                                    unset($custom_sub_menu);
                                                    $hookname = get_plugin_page_hookname( $sitem, $item );
                                                    $_registered_pages[$hookname] = true;
                                                    $prev_hookname = get_plugin_page_hookname( $sitem, $anothersub[count($anothersub)-1]);
                                                    if (isset($wp_filter[$prev_hookname])) {
                                                        $function = self::dump_hook($prev_hookname,$wp_filter[$prev_hookname]);
                                                        $this->add_hook_function($hookname, $function, $sitem);
                                                    }
                                                }
                                            }
                                        }
                                        }
                                        $ismenu = self::istopmenu($sitem, $menu);
                                        $issubmenu = self::getSubmenuParentSlug($sitem, $menu, $submenu);
                                        if (!$ismenu && !$issubmenu) {
                                            $menuicon = isset($topmenuicon[$item]) ? self::menuicon($topmenuicon[$item]) : self::menuicon('');
                                            $menu_title = $topmenutitle[$item];
                                            $hookname = get_plugin_page_hookname( $sitem, $item );
                                            $custom_sub_menu[] = array($menu_title, 'read', $sitem);
                                            if(isset($submenu[$item]) && !empty($submenu[$item])) {
                                                array_splice($submenu[$item], $skey, 0, $custom_sub_menu);
                                            } else {
                                                $submenu[$item] =  $custom_sub_menu;
                                            }
                                            $_registered_pages[$hookname] = true;
                                            $_parent_pages[$sitem] = $item;
                                            unset($custom_sub_menu);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }

            if(!empty($rm_topmenu)) {
                $menu = $this->removemenu($rm_topmenu, $menu);
            }

            return array($menu, $submenu);
        }

        function add_hook_function($hookname, $function, $item)
        {
            $isobject = false;
            if(isset($function) && !empty($function)){
                if(isset($function[1]) && !empty($function[1])){
                    $function_name = $function[1];
                    $isobject = true;
                } else {
                    $function_name = $function[0];
                }
            }
            if (strpos($item, '.php') === false) {
                if ($isobject)
                    add_action($hookname, array( $this, $function_name));
                else
                    add_action($hookname, $function_name);
            }
        }

        public function menuIcon($item)
        {
            $icon = 'dashicons-marker';
            if (isset($item) && !empty($item)){
                $iconType = explode("|", $item);
                if($iconType[1] != "dashicons-blank") {
                    if($iconType[0] == "dashicons") {
                        $icon = trim($iconType[1]);
                    }
                    else {
                        $icon = "dashicons-" . $iconType[1];
                    }
                }
            }
            return $icon;
        }

        function wpav_fa_iconStyles(){
            if(class_exists('WPAVFAICONS')) {
                $wpav_icon_data = (isset($this->aof_options['custom_admin_menu']['menu_icon']) && !empty($this->aof_options['custom_admin_menu']['menu_icon'])) ? $this->aof_options['custom_admin_menu']['menu_icon'] : array();
                $faicons = new WPAVFAICONS();
                $faicons_data = $faicons->wpav_fa_icons();
                $icon_styles = "";
                if(!empty($wpav_icon_data)){
                  foreach($wpav_icon_data as $wpav_icon){
                      if(isset($wpav_icon) && !empty($wpav_icon)) {
                          $get_icon_type = explode("|", $wpav_icon);
                          if($get_icon_type[0] == "fa") {
                              $icon_styles .= '#adminmenu li.menu-top .dashicons-' . $get_icon_type[1] . ':before {';
                              $icon_styles .= 'font-family: "FontAwesome" !important; content: "' . $faicons_data[$get_icon_type[1]] . '" !important';
                              $icon_styles .= '} ';
                          }
                      }

                  } //end of foreach
                }
                return $icon_styles;
            }
        }

        function wpav_load_fa_icons() {
          if($this->wpav_fa_iconStyles()) {
            echo '<style type="text/css">';
            echo parent::wpav_compress_css($this->wpav_fa_iconStyles());
            echo '</style>';
          }
        }

        public function wpav_admin_menu_management()
        {
           global $menu, $submenu, $_parent_pages, $_registered_pages, $admin_page_hooks,$function,$aof_options;
            $topmenutitle = isset($this->aof_options['custom_admin_menu']['top_menu_title']) ? $this->aof_options['custom_admin_menu']['top_menu_title'] : array();
            $topmenuicon = isset($this->aof_options['custom_admin_menu']['menu_icon']) ? $this->aof_options['custom_admin_menu']['menu_icon'] : array();
            $submenutitle = isset($this->aof_options['custom_admin_menu']['sub_menu_title']) ? $this->aof_options['custom_admin_menu']['sub_menu_title'] : array();
            if(isset($this->hide_menu) && !empty($this->hide_menu)){
                foreach($this->hide_menu as $key => $row) {
                    array_splice($menu, $key, 0, array($row));
                }
                unset($key, $row);
            }

            if(isset($this->hide_submenu) && !empty($this->hide_submenu)){
                foreach($this->hide_submenu as $key => $row){
                    if(isset($submenu[$key]) && !empty($submenu[$key])) {
                        foreach($row as $subkey => $subrow){
                            array_splice($submenu[$key], $subkey, 0, array($subrow));
                        }
                        unset($subkey, $subrow);
                    }
                }
                unset($key, $row);
            }
        ?>
            <div class="wrap wpav-wrap">
                <h2><?php _e('Manage Admin Menu', 'wpav'); ?></h2>
				<div class="ad5" style="text-align:center;">
					
				</div>
                <div id="message" class="updated below-h2"><p>
                <?php _e('By default, all menu items will be shown to administrator users. ', 'wpav');
                echo '<a href="' . admin_url() . 'admin.php?page='. WPAV_MENU_SLUG .'"><strong>';
                echo __('Click here ', 'wpav');
                echo '</strong></a>';
                echo __('to customize who can access to all menu items.', 'wpav');
                ?>
                </p></div>

                <div class="manage_admin_menu_sorter">
                    <?php
                        $actual_menulabel = $actual_submenulabel = array();
                        if(isset($this->wp_df_menu) && !empty($this->wp_df_menu)){
                            foreach($this->wp_df_menu as $pmenu){
                                if(isset($pmenu[2]) && !empty($pmenu[2])){
                                    $pslug = $pmenu[2];
                                    $actual_menulabel[$pslug] = $pmenu[0];
                                    if (isset($this->wp_df_submenu[$pmenu[2]]) && !empty($this->wp_df_submenu[$pmenu[2]])){
                                        foreach($this->wp_df_submenu[$pmenu[2]] as $psubmenu){
                                            $actual_submenulabel[$psubmenu[2]] = $psubmenu[0];
                                        }
                                    }
                                }
                            }
                        }
                    ?>
                    <form name="alter_manage_admin_menu" method="post">
                    <ol class="sortable topmenu sortUls" id="top_menu">
                        <?php $inm = 0; $mm_cu = 0; $tsl = 0; ?>
                        <?php if(isset($menu) && !empty($menu)): ?>
                            <?php foreach($menu as $menu_key => $value): $inm++; ?>
                            <?php $menu_value = ((!empty($value[0]))) ? $value[0] : "Separator";?>
                            <?php $menu_icon_data = (isset($topmenuicon[$value[2]]) && !empty($topmenuicon[$value[2]])) ? $topmenuicon[$value[2]] : "";
                              $menu_icon_class = explode('|', $menu_icon_data);
                            ?>
                            <?php $custom_menu_title = (isset($topmenutitle[$value[2]]) &&!empty($topmenutitle[$value[2]])) ? $topmenutitle[$value[2]] : "";?>

                        <li id="<?php echo "top-li-".$tsl;?>">
                            <input type="hidden" name="custom_admin_menu[top_menu][]" id="<?php echo "input-top-li-".$tsl;?>" value="<?php echo $value[2];?>"/>
                            <div class="alter-sort-list alter-top-menu-<?php echo $menu_key; ?>">
                                <span class="menu_title">
                                    <?php

                                        if(isset($actual_menulabel[$value[2]]) && !empty($actual_menulabel[$value[2]])){
                                            $this->Menu_Title($actual_menulabel[$value[2]]);
                                        } else {
                                            $subcurrent = self::getSubmenuParentSlug($value[2], $this->wp_df_menu, $this->wp_df_submenu);
                                            if(isset($subcurrent) && !empty($subcurrent)){
                                                $this->Menu_Title($subcurrent[0]);
                                                unset($subcurrent);
                                            } else {
                                                $this->Menu_Title($menu_value);
                                            }
                                            //$this->Menu_Title($menu_value);
                                        }

                                    ?>
                                </span>
                                <?php $this->Issubpage($value[0]); ?>

                                <div class="alter-menu-contents" id="s">
                                    <div class="menu_title">
                                        <label for="menu_title"><em><?php _e('Rename Title', 'wpav'); ?></em></label>
                                        <input type="text" id="<?php echo "customtitle-top-li-".$tsl;?>" name="custom_admin_menu[top_menu_title][<?php echo $value[2];?>]" value="<?php echo $custom_menu_title;?>" />
                                    </div>
                                    <div class="menu_icon">
                                        <label for="icon_picker"><em><?php _e('Choose Icon', 'wpav'); ?></em></label>
                                        <div id="" data-target="#menu-icon-for-<?php echo $mm_cu; ?>" class="icon-picker <?php echo $menu_icon_class[0] . " " . $menu_icon_class[1]; ?>"></div>
                                        <input type="hidden" id="menu-icon-for-<?php echo $mm_cu++; ?>" name="custom_admin_menu[menu_icon][<?php echo $value[2];?>]" value="<?php echo trim($menu_icon_data); ?>" />
                                    </div>
                                    <?php echo self::hide_for_menu("top_menu", $value[2], '', $inm); ?>

                                    <ol class="menu_child_<?php echo $menu_key; ?> submenu subsortUls" id="sub_menu">
                                        <?php if (isset($submenu[$value[2]]) && !empty($submenu[$value[2]])): ?>
                                        <?php $ssl = 0;?>
                                        <?php foreach($submenu[$value[2]] as $submenu_key => $submenu_value): $inm++; ?>
                                        <?php $disblieitem = (esc_html(html_entity_decode($value[2])) == esc_html(html_entity_decode($submenu_value[2]))) ? "ui-state-disabled" : "ui-state-disabled";?>
                                        <?php $custom_submenu_title = (isset($submenutitle[$value[2]][$submenu_value[2]]) &&!empty($submenutitle[$value[2]][$submenu_value[2]])) ? $submenutitle[$value[2]][$submenu_value[2]] : "";?>
                                            <li id="<?php echo "sub-li-".$tsl."-".$ssl;?>" class="<?php echo $disblieitem; ?>">
                                                <input type="hidden" name="custom_admin_menu[sub_menu][<?php echo $value[2];?>][]" id="<?php echo "input-sub-li-".$tsl."-".$ssl;?>" value="<?php echo $submenu_value[2];?>"/>
                                                <div class="alter-sort-list submenu_contents">
                                                    <span class="menu_title">
                                                        <?php
                                                            if (isset($actual_submenulabel[$submenu_value[2]]) && !empty($actual_submenulabel[$submenu_value[2]])){
                                                                $this->Menu_Title($actual_submenulabel[$submenu_value[2]]);
                                                            } else {
                                                                $ismenu = self::istopmenu($submenu_value[2], $this->wp_df_menu);
                                                                if(isset($ismenu) && !empty($ismenu)){
                                                                    $this->Menu_Title($ismenu[0]);
                                                                    unset($ismenu);
                                                                } else {
                                                                    $this->Menu_Title($submenu_value[0]);
                                                                }

                                                                //$this->Menu_Title($submenu_value[0]);
                                                            }
                                                        ?>
                                                    </span>
                                                    <a href="#" class="alter-edit-expand"><i class="fa fa-chevron-down" aria-hidden="true"></i> <span>Edit</span></a>
                                                    <div class="alter-menu-contents">
                                                        <div class="menu_title">
                                                            <label for="menu_title"><em><?php _e('Rename Title', 'wpav'); ?></em></label>
                                                            <input type="text" id="<?php echo "customtitle-sub-li-".$tsl."-".$ssl;?>" name="custom_admin_menu[sub_menu_title][<?php echo $value[2];?>][<?php echo $submenu_value[2];?>]" value="<?php echo $custom_submenu_title; ?>" />
                                                        </div>
                                                        <a href="#" class="alter-edit-expand"><i class="fa fa-chevron-down" aria-hidden="true"></i> <span>Edit</span></a>
                                                        <?php echo self::hide_for_menu("sub_menu", $value[2], $submenu_value[2],$inm); ?>
                                                    </div>
                                                </div>
                                            </li>
                                            <?php $ssl++; unset($custom_menu_title);?>
                                            <?php endforeach;?>
                                        <?php endif; ?>
                                    </ol>
                                </div>
                            </div>
                        </li>
                            <?php $tsl++;?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ol>
                    <input type="hidden" name="alter_menu_order" value="" />
                    <input type="submit"  class="button button-primary button-hero" value="<?php esc_html_e('Save Changes', 'wpav'); ?>" />
                    </form>
                </div>
				<div class="ad6" style="text-align:center;">

				</div>
            </div>
        <?php

        }

        public function removemenu($itemArray, $menu)
        {
            if (is_array($itemArray)) {
                foreach($itemArray as $key => &$value) {
                    $find = self::istopmenu($value, $menu);
                    $pos = ($find[count($find)-1]);
                    if ($pos) {
                       if (isset($menu[$pos]) && !empty($menu[$pos])) {
                            if ($menu[$pos][2] == $value) {
                                    unset($menu[$pos]);
                                    unset($value);
                            }
                        }
                    }
                }
                return $menu;
            }
            return false;
        }

        public function Issubpage($title)
        {
            if (!empty($title) && isset($title))
                echo '<a href="#" class="alter-edit-expand"><i class="fa fa-chevron-down" aria-hidden="true"></i> <span>Edit</span></a>';
        }

        public function Menu_title($title)
        {
            echo '<i class="fa fa-arrows-alt" aria-hidden="true"></i>';

            if (__('Separator', 'wpav') == $title) {
              echo '<span class="menu-seperator"></span>';
            }
            else {
              echo parent::clean_title($title);
            }
        }

        public function wpav_menu_data() {
           if (isset($this->aof_options['custom_admin_menu']) && !empty($this->aof_options['custom_admin_menu'])) {
               return $this->aof_options['custom_admin_menu'];
           }
           else
               return null;;
        }

        public function cleanArray($array)
        {
            if ($array && is_array($array)){
                foreach($array as &$row) {
                    $row = esc_html(html_entity_decode($row));
                }
                return $array;
            }
            return false;
        }

        public function istopmenu($item, $menu)
        {
            if ($menu) {
                foreach($menu as $key => $value) {
                    if (in_array($item, $value)){
                        array_push($value, $key);
                        return $value;
                    }
                }
            }
            return false;
        }

        public function issubmenu($searchitem, $topkey, $submenu)
        {
            if ($submenu) {
                if (isset($submenu[$topkey]) && !empty($submenu[$topkey])) {
                    foreach($submenu[$topkey] as $key => $value) {
                        if(esc_html(html_entity_decode($searchitem)) == esc_html(html_entity_decode($value[2]))) {
                            array_push($value, $key);
                            return $value;
                        }
                    }
                }
            }
            return false;
        }

        public function getSubmenuParentSlug($item, $menu, $submenu)
        {
            $output = array();
            if (isset($menu) && !empty($menu)) {
                foreach($menu as $key => $value) {
                    if(isset($submenu[$value[2]]) && !empty($submenu[$value[2]])) {
                        $output = self::issubmenu($item, $value[2], $submenu);
                        if ($output) {
                            array_push($output, $value[2]);
                            return $output;
                        }
                    }
                }
            }
            return false;
        }

        function dump_hook( $tag, $hook ) {
            $function = array();

            foreach( $hook as $priority => $functions ) {
                foreach( $functions as $row_function ) {
                    if( $row_function['function'] != 'list_hook_details' ) {
                        if( is_string( $row_function['function'] ) )
                            $function = array_merge(array($row_function['function'])) ;
                        elseif( is_string( $row_function['function'][0] ) )
                             $function = array_merge(array($row_function['function'][0],$row_function['function'][1]));
                        elseif( is_object( $row_function['function'][0] ) )
                            $function = array_merge(array(get_class( $row_function['function'][0] ),$row_function['function'][1]));
                        else
                            $function = array();
                    }
                }
            }

            return $function;
        }

        public function hide_for_menu($level, $admin_menu_slug, $admin_submenu_slug='', $menu_count) {
            $level_name = (empty($level)) ? "top_menu" : $level;
            $admin_submenu_slug = (!empty($admin_submenu_slug)) ? $admin_submenu_slug : $admin_menu_slug;
            $wpav_menu_data = $this->wpav_menu_data();
            $admin_submenu_slug = html_entity_decode($admin_submenu_slug);
            $output = '<div class="hide-for-roles">' .
                '<label class="hide-for-roles" for="hide-for-roles"><em>' . __('Hide menu for', 'wpav') . '</em></label>';
                $get_all_roles = parent::wpav_get_wproles();
                if(!empty($get_all_roles) && is_array($get_all_roles)) {
                    $role_nm = 0;
                    $role_max_nm = count($get_all_roles);
                    $output .= "<table id='box-input-{$menu_count}' class='hide-for-roles-inputs'><tbody>";
                    $output .= "<tr><td><a class='select_all' rel='box-input-{$menu_count}' href='#select_all'>Select all</a>
                    <a class='select_none' rel='box-input-{$menu_count}' href='#select_none'>Select none</a></td></tr>";
                    $output .= "<tr>";
                    foreach ($get_all_roles as $wprole_name => $wprole_label) {
                        if($level_name == "top_menu") {
                            $ids = 'custom_admin_menu['.$level_name.'_hide][' . $admin_menu_slug .  '][' . $wprole_name .  ']';
                            $chk_value_array = (isset($wpav_menu_data['top_menu_hide'][$admin_menu_slug])) ? $wpav_menu_data['top_menu_hide'][$admin_menu_slug] : "";
                        }
                        elseif($level_name == "sub_menu") {
                            $ids = 'custom_admin_menu['.$level_name.'_hide][' . $admin_menu_slug .  '][' . $admin_submenu_slug .  '][' . $wprole_name .  ']';
                            $chk_value_array = (isset($wpav_menu_data['sub_menu_hide'][$admin_menu_slug][$admin_submenu_slug])) ? $wpav_menu_data['sub_menu_hide'][$admin_menu_slug][$admin_submenu_slug] : "";
                        }
                        $chk_value = (!empty($chk_value_array) && array_key_exists($wprole_name, $chk_value_array)) ? "checked=checked" : "";
                        if($role_nm !=0 && $role_nm % 4 == 0) {
                            $output .= "</tr><tr>";
                        }

                        $output .= '<td>';
                        $output .= '<input class="alter-inputs" type="checkbox" name="'.$ids.'" value="1"' . $chk_value . ' />
                        <span>' . $wprole_label . '</span>';
                        $output .= '</td>';

                        if($role_nm == $role_max_nm) {
                            $output .= '</tr>';
                        }
                        $role_nm++;
                    }
                    $output .= '</tbody></table>';
                }
                //print_r($get_all_roles);

            $output .= '</div>';

            return $output;
        }
    }
}new CUSTOMIZEADMINMENU();
