<?php
/*
  Plugin Name: WPHangouts
  Plugin URI: http://wordpress.org/extend/plugins/wphangouts/
  Description: Embed a Google Hangout or HOA button in any page or post.
  Version: 1.0
  Author: Lee Rickler
  Author URI: http://pointandstare.com

  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

add_action("admin_menu", "WPHangouts_menu"); 
 
 function WPHangouts_menu()
{
	add_menu_page( 'WPHangouts Settings', 'WPHangouts Settings', 'manage_options', 'wphangoutssettings', 'pands_settings_page', '');
	add_action( 'admin_init', 'register_pands_settings' );
}
 
 function register_pands_settings() 
{
	register_setting( 'pands-settings-panel', 'appid' );
	register_setting( 'pands-settings-panel', 'buttonsize' );
	register_setting( 'pands-settings-panel', 'buttontype' );	
}

function replace_admin_menu_icons_css() { ?>
    <style>#adminmenu #toplevel_page_wphangoutssettings div.wp-menu-image:before { content: "\f235"; }}</style>
    <?php
}
add_action( 'admin_head', 'replace_admin_menu_icons_css' );

function pands_settings_page() {
?>
<div class="wrap">
<h2 >WPHangouts Settings</h2>
<p>The Hangout button lets you launch a Google+ Hangout or Hangout On Air directly from your site.<br />
You can customise the Hangout button to meet the needs of your website by modifying the button size via the dropdown selector below.
<br /><br />
Not sure what a Google Hangout is? <a href="http://www.google.com/+/learnmore/hangouts/" target="_blank">Have a read here</a>.<br />
Use of the Hangout button is subject to the <a href="https://developers.google.com/+/web/buttons-policy" target="_blank">Buttons Policy</a>.</p>
<form method="post" action="options.php" >
    <?php settings_fields( 'pands-settings-panel' ); ?>
    <?php do_settings_sections( 'pands-settings-panel' );   ?>
	<?php if(isset($_REQUEST['settings-updated']) === true){ ?>
			 <div class="updated settings-error" id="setting-error-settings_updated"> 
		        <p><strong>Settings saved.</strong></p>
			</div>
	<?php } 
	$btn_size = get_option('buttonsize');
	$btn_type = get_option('buttontype'); 
	?>
 <table class="form-table">
		<tr valign="top">
        <th scope="row">App ID:<br /><h5>This will be your original Google Plus ID (the numbers, not your vanity URL)</h5></th>
        <td><input type="text" name="appid" value="<?php echo get_option('appid'); ?>" /></td>
        </tr>
				   <tr valign="top">
        <th scope="row">Choose your button size:</th>
        <td style="width:5%"><select name="buttonsize" >
				<option value="72"<?php if($btn_size=='72'){echo 'selected';   } ?>  >72</option>
			<option value="136"<?php if($btn_size=='136'){echo 'selected';   } ?>  >136</option>	
			  <option value="175"<?php if($btn_size=='175'){echo 'selected';   } ?>  >175</option>
			</select></td>
			<td><?php echo do_shortcode('[wphangouts]'); ?></td>
        </tr>
		 <tr valign="top">
        <th scope="row">Button Type:</th>
        <td><select name="buttontype" >
			<option value="Hangout"<?php if($btn_type == 'Hangout'){echo 'selected';   } ?>  >Hangout</option>	
			<option value="HOA"<?php if($btn_type == 'HOA'){echo 'selected';   } ?>  >Hangout On Air</option>	
			</select></td>
        </tr>
		    </table>
    <?php submit_button(); ?>
</form>

<h3>When you are happy with the settings, simply copy/ paste this shortcode into any post or page - [wphangouts]</h3>

</div>


<?php }

function hangout_function()
{
?>
<script src="//apis.google.com/js/platform.js"></script>
<div id="placeholder-div1"></div>
<?php 
$app_id = get_option('appid');
$btn_size = get_option('buttonsize');
$btn_type = get_option('buttontype'); 
if($btn_type == 'Hangout')
{
?>
<script>
  gapi.hangout.render('placeholder-div1', {
    'render': 'createhangout',
    'initial_apps': [{'app_id' : '<?php echo $app_id; ?>'}],
	 'widget_size': <?php echo $btn_size; ?>
  });
</script>

<?php
}
else
{
?>
<script>
  gapi.hangout.render('placeholder-div1', {
    'render': 'createhangout',
    'hangout_type': 'onair',
    'initial_apps': [{'app_id' : '<?php echo $app_id; ?>' }],
    'widget_size': <?php echo $btn_size; ?>
  });
</script>
<?php
}
}
add_shortcode( 'wphangouts', 'hangout_function' );
?>