<?php
/*
 * ALTER
 * @author   Krish Johnson  krishjohnson
 * @url     http://100utils.com
*/

defined('ABSPATH') || die;

if (!class_exists('ALTER_IMP_EXP')) {

    class WPAV_IMP_EXP extends WPAV
    {
        public $aof_options;

        function __construct()
        {
            $this->aof_options = parent::get_wpav_option_data(WPAV_OPTIONS_SLUG);
            add_action('admin_menu', array($this, 'add_impexp_menu'));
            add_action('plugins_loaded',array($this, 'wpav_settings_action'));
        }

        function add_impexp_menu() {
            add_submenu_page( WPAV_MENU_SLUG, __('Import and Export Settings', 'wpav'), __('Import-Export Settings', 'wpav'), 'manage_options', 'wpav_impexp_settings', array($this, 'wpav_impexp_settings_page') );
        }

        function wpav_impexp_settings_page() {
            global $aof_options;
            ?>
            <div class="wrap wpav-wrap">
        <?php
            if(isset($_GET['page']) && $_GET['page'] == 'wpav_impexp_settings' && isset($_GET['status']) && $_GET['status'] == 'updated')
            {
                ?>
                <div class="updated top">
                    <p><strong><?php echo __('Settings Imported!', 'wpav'); ?></strong></p>
                </div>
        <?php
            }
            elseif(isset($_GET['page']) && $_GET['page'] == 'wpav_impexp_settings' && isset($_GET['status']) && $_GET['status'] == 'dataerror')
            {
                ?>
                <div class="updated top">
                    <p><strong><?php echo __('You are importing empty data or wrong data format.', 'wpav'); ?></strong></p>
                </div>
        <?php
            }

            ?>
                <h3><?php echo __('Reset to default', 'wpav'); ?></h3>
                <span><?php echo __('By resetting all settings will be deleted!', 'wpav'); ?></span>
				<div class="ad3" style="text-align:center;">
					
				</div>
                <div style="padding: 15px 0">
                    <form name="wpav_master_reset_form" method="post" onsubmit="return confirm('Do you really want to Reset?');">
                    <input type="hidden" name="reset_to_default" value="wpav_master_reset" />
                    <?php wp_nonce_field('wpav_reset_nonce','wpav_reset_field'); ?>
                    <input class="button button-primary button-hero" type="submit" value="<?php echo __('Reset All Settings', 'wpav'); ?>" />
                    </form>
                </div>

                <h3><?php echo __('Export Settings', 'wpav'); ?></h3>
                <div style="padding: 15px 0">
                <span><?php echo __('Save the below contents to a text file.', 'wpav'); ?></span>
                <textarea class="widefat" rows="10" ><?php echo $this->wpav_get_settings(); ?></textarea>
                </div>

                <h3><?php echo __('Import Settings', 'wpav'); ?></h3>
                <div style="padding:15px 0">
                <form name="wpav_import_settings_form" method="post" action="">
                        <input type="hidden" name="wpav_import_settings" value="1" />
                        <textarea class="widefat" name="wpav_import_settings_data" rows="10" ></textarea><br /><br />
                        <input class="button button-primary button-hero" type="submit" value="<?php echo __('Import Settings', 'wpav'); ?>" />
                <?php wp_nonce_field('wpav_import_settings_nonce','wpav_import_settings_field'); ?>
                </form>
                </div>
				<div class="ad4" style="text-align:center;">
					
				</div>
            </div>

<?php
        }

        function wpav_settings_action() {
            if(isset($_POST['wpav_import_settings_field']) ) {
                if(!wp_verify_nonce( sanitize_text_field($_POST['wpav_import_settings_field']), 'wpav_import_settings_nonce' ) )
                    exit();
                $import_data = sanitize_text_field(trim($_POST['wpav_import_settings_data']));
                if(empty($import_data) || !is_serialized($import_data)) {
                    wp_safe_redirect( admin_url( 'admin.php?page=wpav_impexp_settings&status=dataerror' ) );
                    exit();
                }
                else {
                    $data = unserialize($import_data); //to avoid double serialization
                    parent::updateOption(WPAV_OPTIONS_SLUG, $data);
                    wp_safe_redirect( admin_url( 'admin.php?page=wpav_impexp_settings&status=updated' ) );
                    exit();
                }
            }

            if(isset($_POST['reset_to_default']) && $_POST['reset_to_default'] == "wpav_master_reset") {
                if(!wp_verify_nonce( sanitize_text_field($_POST['wpav_reset_field']), 'wpav_reset_nonce' ) )
                        exit();

                global $aof_options;
                $aof_options->aofLoaddefault(true);
                wp_safe_redirect( admin_url( 'admin.php?page='.WPAV_MENU_SLUG ) );
                exit();
            }
        }

        function wpav_get_settings() {
           $saved_data = parent::get_wpav_option_data(WPAV_OPTIONS_SLUG);
           if(!empty($saved_data)) {
               if(!is_serialized($saved_data)) {
                   return maybe_serialize($saved_data);
               }
               else {
                   return $saved_data;
               }
           }
        }

    }

}

new WPAV_IMP_EXP();
