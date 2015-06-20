<?php
/*
Plugin Name: WooCommerce Frontend Shop Manager (shared on wplocker.com)
Plugin URI: http://www.mihajlovicnenad.com/woocommerce-frontend-shop-manager
Description:  WooCommerce Frontend Shop Manager! - mihajlovicnenad.com
Author: Mihajlovic Nenad
Version: 1.0.1
Author URI: http://www.mihajlovicnenad.com
*/

add_action( 'plugins_loaded', array( 'WC_Frontnend_Shop_Manager', 'init' ));

class WC_Frontnend_Shop_Manager {

	public static $path;
	public static $url_path;

	public static function init() {
		$class = __CLASS__;
		new $class;
	}

	function __construct() {

		if ( !class_exists('Woocommerce') || !current_user_can('edit_products') ) {
			return;
		}

		self::$path = plugin_dir_path( __FILE__ );
		self::$url_path = plugins_url( __FILE__ );

		add_action( 'init', array(&$this, 'wfsm_textdomain') );
		add_action( 'wp_enqueue_scripts', array(&$this, 'wfsm_scripts') );

		add_action( 'woocommerce_before_shop_loop_item', array(&$this, 'wfsm_content') );
		add_action( 'woocommerce_before_single_product_summary', array(&$this, 'wfsm_content'), 5 );
		add_action( 'wp_ajax_wfsm_respond', array(&$this, 'wfsm_respond') );
		add_action( 'wp_ajax_wfsm_save', array(&$this, 'wfsm_save') );
		add_action( 'wp_ajax_wfsm_create_attribute', array(&$this, 'wfsm_create_attribute') );

	}

	public static function wfsm_get_path() {
		return plugin_dir_path( __FILE__ );
	}

	function wfsm_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wfsm' );
		$dir = trailingslashit( WP_LANG_DIR );

		load_textdomain( 'wfsm', $dir . 'plugins/wfsm-' . $locale . '.mo' );
		load_plugin_textdomain( 'wfsm', false, $dir . 'plugins' );

	}

	function wfsm_scripts() {
		wp_register_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
		wp_enqueue_style( 'jquery-ui' );
		wp_enqueue_style( 'wfsm-selectize', plugins_url( 'assets/css/selectize.default.css', __FILE__) );
		wp_enqueue_style( 'wfsm-style', plugins_url( 'assets/css/styles.css', __FILE__) );

		wp_register_script( 'wfsm-selectize', plugins_url( 'assets/js/selectize.min.js', __FILE__), array( 'jquery' ), '1.0.0', true );
		wp_register_script( 'wfsm-scripts', plugins_url( 'assets/js/scripts.js', __FILE__), array( 'jquery' ), '1.0.0', true );
		wp_register_script( 'wfsm-init', plugins_url( 'assets/js/scripts-init.js', __FILE__), array( 'jquery' ), '1.0.0', false );
		wp_enqueue_media();
		wp_enqueue_script( array( 'wfsm-init', 'jquery-ui-datepicker', 'wfsm-selectize', 'wfsm-scripts' ) );
		$curr_args = array(
			'ajax' => admin_url( 'admin-ajax.php' )
		);

		wp_localize_script( 'wfsm-scripts', 'wfsm', $curr_args );
	}


	function wfsm_content() {

		global $post, $woocommerce_loop;

		$curr_id = $post->ID;

		if ( !current_user_can( 'edit_product', array( 'ID' => $curr_id ) ) ) {
			return;
		}

		$add_loop = !empty($woocommerce_loop) ?$woocommerce_loop['loop'] . '|' . $woocommerce_loop['columns'] : 'single';

	?>
		<div class="wfsm-buttons" data-id="<?php echo $curr_id; ?>" data-loop="<?php echo $add_loop; ?>">
			<a href="#" class="wfsm-button wfsm-activate" title="<?php _e( 'Quick edit product', 'wfsm' ); ?>"><i class="wfsmico-activate"></i></a>
			<a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $curr_id ) . '&action=edit' ) ); ?>" class="wfsm-button wfsm-edit" title="<?php _e( 'Edit product in the backend', 'wfsm' ); ?>"><i class="wfsmico-edit"></i></a>
			<a href="#" class="wfsm-button wfsm-save" title="<?php _e( 'Save changes', 'wfsm' ); ?>"><i class="wfsmico-save"></i></a>
			<a href="#" class="wfsm-button wfsm-discard" title="<?php _e( 'Discard changes', 'wfsm' ); ?>"><i class="wfsmico-discard"></i></a>
			<span class="wfsm-editing">
				<img width="64" height="64" src="<?php echo plugins_url( 'assets/images/editing.png', __FILE__ ); ?>" />
				<small>
					<?php _e( 'Currently Editing', 'wfsm' ) ; ?><br/>
					<?php _e( 'Tap to Save', 'wfsm' ) ; ?>
				</small>
			</span>
		</div>
	<?php

	}

	function wfsm_respond() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX && isset($_POST) && isset($_POST['wfsm_id']) && get_post_status($_POST['wfsm_id']) ) === false ) {
			die();
			exit;
		}

		$product = wc_get_product( $_POST['wfsm_id'] );

		if ( $product->is_type( 'simple' ) ) {
			$product_type_class = ' wfsm-simple-product';
			$product_information = __( 'Simple Product', 'wfsm' ) . ' #ID ' . $_POST['wfsm_id'];
		}
		else if ( $product->is_type( 'variable' ) ) {
			$product_type_class = ' wfsm-variable-product';
			$product_information = __( 'Variable Product', 'wfsm' ) . ' #ID ' . $_POST['wfsm_id'];
		}
		else if ( $product->is_type( 'external' ) ) {
			$product_type_class = ' wfsm-external-product';
			$product_information = __( 'External Product', 'wfsm' ) . ' #ID ' . $_POST['wfsm_id'];
		}
		else if ( $product->is_type( 'grouped' ) ) {
			$product_type_class = ' wfsm-grouped-product';
			$product_information = __( 'Grouped Product', 'wfsm' ) . ' #ID ' . $_POST['wfsm_id'];
		}
		else {
			$product_type_class = ' wfsm-' . $product->product_type() . '-product';
			$product_information = __( 'Product', 'wfsm' ) . ' #ID ' . $_POST['wfsm_id'];
		}

		ob_start();
	?>
		<div class="wfsm-quick-editor">
			<div class="wfsm-screen<?php echo $product_type_class; ?>">
				<div class="wfsm-controls">
					<span class="wfsm-about">
						<img width="49" height="28" src="<?php echo plugins_url( 'assets/images/about.png', __FILE__ ); ?>" />
						<em><?php echo 'WooCommerce Frontend Shop Manager<br/>' . __( 'by', 'wfsm' ) . ' <a href="http://mihajlovicnenad.com">mihajlovicnenad.com</a><br/>' . __( 'Full Version', 'wfsm' ) . ' 1.0.0<br/>' . ' <a href="http://codecanyon.net/user/dzeriho/portfolio?ref=dzeriho">' . __('Get more premium plugins at this link', 'wfsm' ) . '</a>'; ?></em>
						<small><?php echo __( 'Editing', 'wfsm' ) . ': ' . $product_information; ?></small>
					</span>
					<span class="wfsm-expand"><i class="wfsmico-expand"></i></span>
					<span class="wfsm-contract"><i class="wfsmico-contract"></i></span>
					<span class="wfsm-side-edit"><i class="wfsmico-edit"></i></span>
					<span class="wfsm-side-save"><i class="wfsmico-save"></i></span>
					<span class="wfsm-side-discard"><i class="wfsmico-discard"></i></span>
					<div class="wfsm-clear"></div>
				</div>
				<span class="wfsm-headline"><?php _e( 'Product Data', 'wfsm' ); ?></span>
				<div class="wfsm-group-general">
					<label for="wfsm-featured-image" class="wfsm-featured-image">
						<a href="#" class="wfsm-featured-image-trigger">
						<?php
							if ( has_post_thumbnail( $_POST['wfsm_id'] ) ) {
								$curr_image = wp_get_attachment_image_src( $curr_image_id = get_post_thumbnail_id( $_POST['wfsm_id'] ), 'thumbnail' );
							?>
								<img width="64" height="64" src="<?php echo $curr_image[0]; ?>" />
							<?php
							}
							else {
								$curr_image_id = 0;
							?>
								<img width="64" height="64" src="<?php echo plugins_url( 'assets/images/placeholder.gif', __FILE__ ); ?>" />
						<?php
							}
						?>
						</a>
						<input id="wfsm-featured-image" name="wfsm-featured-image" class="wfsm-collect-data" type="hidden" value="<?php echo $curr_image_id; ?>" />
					</label>
					<div class="wfsm-featured-image-controls">
						<a href="#" class="wfsm-editor-button wfsm-change-image"><?php _e( 'Change Image', 'wfsm' ); ?></a>
						<a href="#" class="wfsm-editor-button wfsm-remove-image"><?php _e( 'Discard Image', 'wfsm' ); ?></a>
					</div>
					<div class="wfsm-clear"></div>
					<label for="wfsm-product-name">
						<span><?php _e( 'Product Name', 'wfsm' ); ?></span>
						<input id="wfsm-product-name" name="wfsm-product-name" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo get_the_title($_POST['wfsm_id']); ?>"/>
					</label>
				<?php
					if ( $product->is_type( 'external' ) ) {
						$product_http = ( $product_http_meta = get_post_meta( $_POST['wfsm_id'], '_product_url', true ) ) ? $product_http_meta : '';
				?>
						<label for="wfsm-product-http">
							<span><?php _e( 'Product External URL', 'wfsm' ); ?></span>
							<input id="wfsm-product-http" name="wfsm-product-http" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_http; ?>"/>
						</label>
				<?php
					}
					if ( wc_product_sku_enabled() && !$product->is_type( 'grouped' ) ) {
				?>
					<label for="wfsm-sku">
						<span><?php _e( 'SKU', 'wfsm' ); ?></span>
						<input id="wfsm-sku" name="wfsm-sku" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product->get_sku(); ?>" />
					</label>
				<?php
					}
					if ( !$product->is_type( 'variable' ) && !$product->is_type( 'grouped' ) ) {
				?>
					<label for="wfsm-regular-price" class="wfsm-label-half wfsm-label-first">
						<span><?php _e( 'Regular Price', 'wfsm' ); ?></span>
						<input id="wfsm-regular-price" name="wfsm-regular-price" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product->get_regular_price(); ?>"/>
					</label>
					<label for="wfsm-sale-price" class="wfsm-label-half">
						<span><?php _e( 'Sale Price', 'wfsm' ); ?></span>
						<input id="wfsm-sale-price" name="wfsm-sale-price" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product->get_sale_price(); ?>"/>
					</label>
					<div class="wfsm-clear"></div>
				<?php
					}
					$option_manage_stock = get_option( 'woocommerce_manage_stock' );
					if ( 'yes' == $option_manage_stock && !$product->is_type( 'external' )  && !$product->is_type( 'grouped' ) ) {
						if ( !$product->is_type( 'variable' ) ) {
							$in_stock_status = ( $status = get_post_meta( $_POST['wfsm_id'], '_stock_status', true ) ) ? $status : 'instock';
						?>
								<label for="wfsm-product-status" class="wfsm-label-checkbox wfsm-<?php echo $in_stock_status; ?>">
									<span class="wfsm-show-instock"><?php _e( 'Product is In Stock', 'wfsm' ); ?></span>
									<span class="wfsm-show-outofstock"><?php _e( 'Product is currently Out of Stock', 'wfsm' ); ?></span>
									<input id="wfsm-product-status" name="wfsm-product-status" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $in_stock_status; ?>"/>
								</label>
							<?php
						}
						$wfsm_class = ( ( get_post_meta( $_POST['wfsm_id'], '_manage_stock', true ) ) == 'yes' ? ' wfsm-visible' : ' wfsm-hidden' );
					?>
						<div class="wfsm-manage-stock-quantity<?php echo $wfsm_class; ?>">
							<label for="wfsm-stock-quantity" class="wfsm-label-quantity">
								<span><?php _e( 'Stock Quantity', 'wfsm' ); ?></span>
							<?php
								$stock_count = get_post_meta( $_POST['wfsm_id'], '_stock', true );
							?>
								<input id="wfsm-stock-quantity" name="wfsm-stock-quantity" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $stock_count; ?>" />
							</label>
							<label for="wfsm-backorders" class="wfsm-selectize">
							<?php
								$wfsm_selected = ( ( $backorders = get_post_meta( $_POST['wfsm_id'], '_backorders', true ) ) ? $backorders : '' );
							?>
								<span><?php _e( 'Allow Backorders', 'wfsm' ); ?></span>
								<select id="wfsm-backorders" name="wfsm-backorders" class="wfsm-collect-data">
								<?php
									$wfsm_select_options = array(
										'no'     => __( 'Do not allow', 'wfsm' ),
										'notify' => __( 'Allow, but notify customer', 'wfsm' ),
										'yes'    => __( 'Allow', 'wfsm' )
									);
									foreach ( $wfsm_select_options as $wk => $wv ) {
								?>
										<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
								<?php
									}
								?>
								</select>
							</label>
						</div>
					<?php
						$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
					?>
						<a href="#" class="wfsm-editor-button wfsm-manage-stock-quantity<?php echo $wfsm_button_class; ?>"><?php _e( 'Manage Stock', 'wfsm' ); ?></a>
				<?php
					}
					if ( !$product->is_type( 'variable' ) && !$product->is_type( 'grouped' ) ) {

						$sale_price_dates_from = ( $date_from = get_post_meta( $_POST['wfsm_id'], '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date_from ) : '';
						$sale_price_dates_to = ( $date_to = get_post_meta( $_POST['wfsm_id'], '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date_to ) : '';
						$wfsm_class = ( $sale_price_dates_from !== '' || $sale_price_dates_to !== '' ? ' wfsm-visible' : ' wfsm-hidden' );
					?>
						<div class="wfsm-schedule-sale<?php echo $wfsm_class; ?>">
							<label for="wfsm-schedule-sale-start" class="wfsm-label-half wfsm-label-first">
								<span><?php _e( 'Start Sale', 'wfsm' ); ?></span>
								<input id="wfsm-schedule-sale-start" name="wfsm-schedule-sale-start" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_from ); ?>" placeholder="<?php _e( 'From&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
							</label>
							<label for="wfsm-schedule-sale-end" class="wfsm-label-half">
								<span><?php _e( 'End Sale', 'wfsm' ); ?></span>
								<input id="wfsm-schedule-sale-end" name="wfsm-schedule-sale-end" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_to ); ?>" placeholder="<?php _e( 'To&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
							</label>
							<div class="wfsm-clear"></div>
							<script type="text/javascript">
								(function($){
									"use strict";
									var curr_date = new Date();
									var curr_dates = $('#wfsm-schedule-sale-start, #wfsm-schedule-sale-end').datepicker( {
										dateFormat: 'yy/mm/dd',
										defaultDate: "+1w",
										minDate: curr_date,
										onSelect: function(curr_selected) {
											var option = this.id == "wfsm-schedule-sale-start" ? "minDate" : "maxDate",
											instance = $(this).data("datepicker"),
											date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, curr_selected, instance.settings);
											curr_dates.not(this).datepicker("option", option, date);
										}
									} );
								})(jQuery);
							</script>
						</div>
					<?php
						$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
					?>
						<a href="#" class="wfsm-editor-button wfsm-schedule-sale<?php echo $wfsm_button_class; ?>"><?php _e( 'Schedule Sale', 'wfsm' ); ?></a>
				<?php
						$post_parents = array();
						$post_parents[''] = __( 'Choose a grouped product&hellip;', 'woocommerce' );

						if ( $grouped_term = get_term_by( 'slug', 'grouped', 'product_type' ) ) {

							$posts_in = array_unique( (array) get_objects_in_term( $grouped_term->term_id, 'product_type' ) );

							if ( sizeof( $posts_in ) > 0 ) {

								$args = array(
									'post_type'        => 'product',
									'post_status'      => 'any',
									'numberposts'      => -1,
									'orderby'          => 'title',
									'order'            => 'asc',
									'post_parent'      => 0,
									'suppress_filters' => 0,
									'include'          => $posts_in,
								);

								$grouped_products = get_posts( $args );

								if ( $grouped_products ) {

									foreach ( $grouped_products as $sel_product ) {

										if ( $sel_product->ID == $post->ID ) {
											continue;
										}

										$post_parents[ $sel_product->ID ] = $sel_product->post_title;
									}
								}
							}

						}
						if ( !empty($post_parents) ) {
						?>
							<label for="wfsm-grouping" class="wfsm-selectize">
							<?php
								$wfsm_selected = ( ( $grouping = wp_get_post_parent_id( $_POST['wfsm_id'] ) ) !== '' ? $grouping : '' );
							?>
								<span><?php _e( 'Grouping', 'wfsm' ); ?></span>
								<select id="wfsm-grouping" name="wfsm-grouping" class="wfsm-collect-data">
								<?php
									foreach ( $post_parents as $wk => $wv ) {
								?>
										<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
								<?php
									}
								?>
								</select>
							</label>
						<?php
							}
					}
					?>
						<div class="wfsm-clear"></div>
				</div>
				<span class="wfsm-headline wfsm-headline-gallery"><?php _e( 'Product Gallery', 'wfsm' ); ?></span>
				<div class="wfsm-group-gallery">
				<?php
					$product_gallery = ( $gallery = get_post_meta( $_POST['wfsm_id'], '_product_image_gallery', true ) ) ? $gallery : '';
				?>
					<div class="wfsm-product-gallery-images">
					<?php
						$curr_gallery = ( strpos( $product_gallery , ',' ) !== false ? explode( ',', $product_gallery ) : array( $product_gallery ) );
						foreach( $curr_gallery as $img_id ) {
							if ( $img_id == '' ) continue;
							$curr_image = wp_get_attachment_image_src( $img_id, 'thumbnail' );
						?>
							<span class="wfsm-product-gallery-image" data-id="<?php echo $img_id; ?>">
								<img width="64" height="64" src="<?php echo $curr_image[0]; ?>" />
								<a href="#" class="wfsm-remove-gallery-image"><i class="wfsmico-discard"></i></a>
							</span>
					<?php
						}
					?>
						<div class="wfsm-clear"></div>
					</div>
					<label for="wfsm-product-gallery" class="wfsm-product-gallery">
						<a href="#" class="wfsm-editor-button wfsm-add-gallery-image"><?php _e( 'Add Image', 'wfsm' ); ?></a>
						<select id="wfsm-product-gallery" name="wfsm-product-gallery" class="wfsm-reset-this wfsm-collect-data" multiple="multiple">
					<?php
							foreach( $curr_gallery as $img_id ) {
						?>
								<option value="<?php echo $img_id; ?>" selected="selected"><?php echo $img_id; ?></option>
						<?php
							}
					?>
						</select>
					</label>
				</div>
				<span class="wfsm-headline wfsm-headline-taxonomies"><?php _e( 'Product Taxnonomies and Terms', 'wfsm' ); ?></span>
				<div class="wfsm-group-taxonomies">
					<label for="wfsm-select-product_cat" class="wfsm-selectize">
						<span><?php _e( 'Product Categories', 'wfsm' ); ?></span>
						<select id="wfsm-select-product_cat" name="wfsm-select-product_cat" class="wfsm-collect-data" multiple="multiple">
						<?php
							$product_cats = wp_get_post_terms($_POST['wfsm_id'], 'product_cat', array( 'fields' => 'slugs' ) );
							foreach( get_terms('product_cat','parent=0&hide_empty=0') as $term ) {
								$wfsm_selected = in_array( $term->slug , $product_cats ) ? 'added' : 'notadded' ;
							?>
								<option <?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
							<?php
							}
						?>
						</select>
					</label>
					<label for="wfsm-select-product_tag" class="wfsm-selectize">
						<span><?php _e( 'Product tags', 'wfsm' ); ?></span>
						<select id="wfsm-select-product_tag" name="wfsm-select-product_tag" class="wfsm-collect-data" multiple="multiple">
						<?php
							$product_tags = wp_get_post_terms($_POST['wfsm_id'], 'product_tag', array( 'fields' => 'slugs' ) );
							foreach( get_terms('product_tag','parent=0&hide_empty=0') as $term ) {
								$wfsm_selected = in_array( $term->slug , $product_tags ) ? 'added' : 'notadded' ;
							?>
								<option <?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
							<?php
							}
						?>
						</select>
					</label>
				<?php
					if ( !$product->is_type( 'variable' ) ) {
						$attribute_taxonomies = wc_get_attribute_taxonomies();
						$product_attributes = $product->get_attributes();
					?>
						<label for="wfsm-select-attributes" class="wfsm-selectize">
							<span><?php _e( 'Product Attributes', 'wfsm' ); ?></span>
							<select id="wfsm-select-attributes" name="wfsm-select-attributes" class="wfsm-collect-data" multiple="multiple">
							<?php
								foreach ($attribute_taxonomies as $tax) {
									$wfsm_selected = array_key_exists( 'pa_' . $tax->attribute_name , $product_attributes ) ? 'added' : 'notadded' ;
							?>
									<option value="<?php echo 'pa_' . $tax->attribute_name;?>"<?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?>><?php echo ucfirst( $tax->attribute_label ); ?></option>
							<?php
								}
							?>
							</select>
						</label>
					<?php
						$curr_atts = array();
						if ( !empty( $attribute_taxonomies ) && !is_wp_error( $attribute_taxonomies ) ){
							foreach ($attribute_taxonomies as $tax) {
								if ( !array_key_exists( 'pa_' . $tax->attribute_name, $product_attributes ) ) {
									continue;
								}
								$curr_name = sanitize_title($tax->attribute_name);
								$curr_paname = 'pa_' . $tax->attribute_name;
						?>
							<div class="wfsm-attribute-<?php echo $curr_paname; ?>">
								<label for="wfsm-select-<?php echo $curr_paname; ?>" class="wfsm-selectize">
									<span><?php echo __( 'Product', 'wfsm' ) . ' ' . ucfirst( $tax->attribute_label); ?></span>
									<select id="wfsm-select-<?php echo $curr_paname; ?>" name="wfsm-select-<?php echo $curr_paname; ?>" class="wfsm-collect-data" multiple="multiple">
									<?php
										$product_atts = wp_get_post_terms($_POST['wfsm_id'], $curr_paname, array( 'fields' => 'slugs' ) );
										foreach( get_terms($curr_paname,'parent=0&hide_empty=0') as $term ) {
											$wfsm_selected = in_array( $term->slug , $product_atts ) ? 'added' : 'notadded' ;
										?>
											<option <?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
										<?php
										}
									?>
									</select>
								</label>
							<?php
								$curr_value = ( ( isset( $product_attributes[$curr_paname]) && $product_attributes[$curr_paname]['is_visible'] == 1 ) ? 'isvisible' : 'notvisible' );
							?>
								<label for="wfsm-visible-<?php echo $curr_paname; ?>" class="wfsm-label-checkbox wfsm-<?php echo $curr_value; ?>">
									<span class="wfsm-show-isvisible"><?php _e( 'Attribute is visible on product page', 'wfsm' ); ?></span>
									<span class="wfsm-show-notvisible"><?php _e( 'Attribute is not visible on product page', 'wfsm' ); ?></span>
									<input id="wfsm-visible-<?php echo $curr_paname; ?>" name="wfsm-visible-<?php echo $curr_paname; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $curr_value; ?>"/>
								</label>
							</div>
						<?php
							}
						}
					}
				?>
				</div>
				<?php
				if ( $product->is_type( 'variable' ) ) {
					$available_variations = $product->get_available_variations();
				?>
					<div class="wfsm-variations">
				<?php
					foreach ( $available_variations as $var ) {
						$curr_product[$var['variation_id']] = new WC_Product_Variation( $var['variation_id'] );
						$curr_variable_attributes = $curr_product[$var['variation_id']]->get_variation_attributes();
					?>
						<span class="wfsm-headline"><?php echo __( 'Variation #ID', 'wfsm' ) . ' ' . $var['variation_id']; ?></span>
						<div class="wfsm-variation" data-id="<?php echo $var['variation_id']; ?>">
							<div class="wfsm-variation-attributes">
							<?php
								foreach ( $curr_variable_attributes as $ak => $av ) {
									echo '<span class="wfsm-variation-attribute">' . wc_attribute_label( substr( $ak, 10 ) ) . ': <span class="wfsm-variation-term">' . ( $av == '' ? __( 'any', 'wfsm' ) : $av ) . '</span></span>';
								}
							?>
							</div>
							<label for="wfsm-featured-image-<?php echo $var['variation_id']; ?>" class="wfsm-featured-image">
								<a href="#" class="wfsm-featured-image-trigger">
								<?php
									if ( has_post_thumbnail( $var['variation_id'] ) ) {
										$curr_image = wp_get_attachment_image_src( $curr_image_id = get_post_thumbnail_id( $var['variation_id'] ), 'thumbnail' );
									?>
										<img width="64" height="64" src="<?php echo $curr_image[0]; ?>" />
									<?php
									}
									else {
										$curr_image_id = 0;
									?>
										<img width="64" height="64" src="<?php echo plugins_url( 'assets/images/placeholder.gif', __FILE__ ); ?>" />
								<?php
									}
								?>
								</a>
								<input id="wfsm-featured-image-<?php echo $var['variation_id']; ?>" name="wfsm-featured-image-<?php echo $var['variation_id']; ?>" class="wfsm-collect-data" type="hidden" value="<?php echo $curr_image_id; ?>" />
							</label>
							<div class="wfsm-featured-image-controls">
								<a href="#" class="wfsm-editor-button wfsm-change-image"><?php _e( 'Change Image', 'wfsm' ); ?></a>
								<a href="#" class="wfsm-editor-button wfsm-remove-image"><?php _e( 'Discard Image', 'wfsm' ); ?></a>
							</div>
							<div class="wfsm-clear"></div>
						<?php
							if ( wc_product_sku_enabled() ) {
						?>
							<label for="wfsm-sku-<?php echo $var['variation_id']; ?>">
								<span><?php _e( 'SKU', 'wfsm' ); ?></span>
								<input id="wfsm-sku-<?php echo $var['variation_id']; ?>" name="wfsm-sku-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $curr_product[$var['variation_id']]->get_sku(); ?>" />
							</label>
						<?php
							}
						?>
							<label for="wfsm-regular-price-<?php echo $var['variation_id']; ?>" class="wfsm-label-half wfsm-label-first">
								<span><?php _e( 'Regular Price', 'wfsm' ); ?></span>
								<input id="wfsm-regular-price-<?php echo $var['variation_id']; ?>" name="wfsm-regular-price-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $curr_product[$var['variation_id']]->get_regular_price(); ?>"/>
							</label>
							<label for="wfsm-sale-price-<?php echo $var['variation_id']; ?>" class="wfsm-label-half">
								<span><?php _e( 'Sale Price', 'wfsm' ); ?></span>
								<input id="wfsm-sale-price-<?php echo $var['variation_id']; ?>" name="wfsm-sale-price-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $curr_product[$var['variation_id']]->get_sale_price(); ?>"/>
							</label>
							<div class="wfsm-clear"></div>
						<?php
							$in_stock_status = ( $status = get_post_meta( $var['variation_id'], '_stock_status', true ) ) ? $status : 'instock';
						?>
							<label for="wfsm-product-status-<?php echo $var['variation_id']; ?>" class="wfsm-label-checkbox wfsm-<?php echo $in_stock_status; ?>">
								<span class="wfsm-show-instock"><?php _e( 'Product is In Stock', 'wfsm' ); ?></span>
								<span class="wfsm-show-outofstock"><?php _e( 'Product is currently Out of Stock', 'wfsm' ); ?></span>
								<input id="wfsm-product-status-<?php echo $var['variation_id']; ?>" name="wfsm-product-status-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $in_stock_status; ?>"/>
							</label>
						<?php
							if ( 'yes' == $option_manage_stock ) {
								$wfsm_class = ( ( get_post_meta( $var['variation_id'], '_manage_stock', true ) ) == 'yes' ? ' wfsm-visible' : ' wfsm-hidden' );
							?>
								<div class="wfsm-manage-stock-quantity-<?php echo $var['variation_id']; ?><?php echo $wfsm_class; ?>">
									<label for="wfsm-stock-quantity-<?php echo $var['variation_id']; ?>" class="wfsm-label-quantity">
										<span><?php _e( 'Stock Quantity', 'wfsm' ); ?></span>
									<?php
										$stock_count = get_post_meta( $var['variation_id'], '_stock', true );
									?>
										<input id="wfsm-stock-quantity-<?php echo $var['variation_id']; ?>" name="wfsm-stock-quantity-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $stock_count; ?>" />
									</label>
									<label for="wfsm-backorders-<?php echo $var['variation_id']; ?>" class="wfsm-selectize">
									<?php
										$wfsm_selected = ( ( $backorders = get_post_meta( $var['variation_id'], '_backorders', true ) ) ? $backorders : '' );
									?>
										<span><?php _e( 'Allow Backorders', 'wfsm' ); ?></span>
										<select id="wfsm-backorders-<?php echo $var['variation_id']; ?>" name="wfsm-backorders-<?php echo $var['variation_id']; ?>" class="wfsm-collect-data">
										<?php
											$wfsm_select_options = array(
												'no'     => __( 'Do not allow', 'wfsm' ),
												'notify' => __( 'Allow, but notify customer', 'wfsm' ),
												'yes'    => __( 'Allow', 'wfsm' )
											);
											foreach ( $wfsm_select_options as $wk => $wv ) {
										?>
												<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
										<?php
											}
										?>
										</select>
									</label>
								</div>
							<?php
								$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
							?>
								<a href="#" class="wfsm-editor-button wfsm-manage-stock-quantity-<?php echo $var['variation_id']; ?><?php echo $wfsm_button_class; ?>"><?php _e( 'Manage Stock', 'wfsm' ); ?></a>
							<?php
							}
						?>
						<?php
							$sale_price_dates_from = ( $date = get_post_meta( $var['variation_id'], '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
							$sale_price_dates_to = ( $date = get_post_meta( $var['variation_id'], '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
							$wfsm_class = ( $sale_price_dates_from !== '' || $sale_price_dates_to !== '' ? ' wfsm-visible' : ' wfsm-hidden' );
						?>
							<div class="wfsm-schedule-sale<?php echo $wfsm_class; ?>">
								<label for="wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>" class="wfsm-label-half wfsm-label-first">
									<span><?php _e( 'Start Sale', 'wfsm' ); ?></span>
									<input id="wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>" name="wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_from ); ?>" placeholder="<?php _e( 'From&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
								</label>
								<label for="wfsm-schedule-sale-end-<?php echo $var['variation_id']; ?>" class="wfsm-label-half">
									<span><?php _e( 'End Sale', 'wfsm' ); ?></span>
									<input id="wfsm-schedule-sale-end-<?php echo $var['variation_id']; ?>" name="wfsm-schedule-sale-end-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_to ); ?>" placeholder="<?php _e( 'To&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
								</label>
								<div class="wfsm-clear"></div>
								<script type="text/javascript">
									(function($){
										"use strict";
										var curr_date = new Date();
										var curr_dates = $('#wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>, #wfsm-schedule-sale-end-<?php echo $var['variation_id']; ?>').datepicker( {
											dateFormat: 'yy/mm/dd',
											defaultDate: "+1w",
											minDate: curr_date,
											onSelect: function(curr_selected) {
												var option = this.id == "wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>" ? "minDate" : "maxDate",
												instance = $(this).data("datepicker"),
												date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, curr_selected, instance.settings);
												curr_dates.not(this).datepicker("option", option, date);
											}
										} );
									})(jQuery);
								</script>
							</div>
						<?php
							$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
						?>
							<a href="#" class="wfsm-editor-button wfsm-schedule-sale<?php echo $wfsm_button_class; ?>"><?php _e( 'Schedule Sale', 'wfsm' ); ?></a>
						</div>
					<?php

					}
				?>
					</div>
				<?php
				}
			?>
				<div class="wfsm-clear"></div>
				<script type="text/javascript">
					(function($){
						"use strict";

						$(document).on('click', '.wfsm-quick-editor label.wfsm-featured-image > a.wfsm-featured-image-trigger, .wfsm-change-image', function () {

							if ( $(this).hasClass('wfsm-change-image') ) {
								var el = $(this).parent().prev().find('.wfsm-featured-image-trigger');
							}
							else {
								var el = $(this);
							}

							var curr = el.parent();

							if ( $.isEmptyObject(window.wfsm_frame) == false ) {

								window.wfsm_frame.off('select');

								window.wfsm_frame.on( 'select', function() {

									var attachment = window.wfsm_frame.state().get('selection').first();
									window.wfsm_frame.close();

									curr.find('input:hidden').val(attachment.id);
									if ( attachment.attributes.type == 'image' ) {
										el.html('<img width="64" height="64" src="'+attachment.attributes.sizes.thumbnail.url+'" />');
									}

								});

								window.wfsm_frame.open();

								return false;
							}


							window.wfsm_frame = wp.media({
								title: '<?php _e('Set Featured Image','wfsm'); ?>',
								button: {
									text: el.data("update"),
									close: false
								},
								multiple: false,
								default_tab: 'upload',
								tabs: 'upload, library',
								returned_image_size: 'thumbnail'
							});

							window.wfsm_frame.off('select');

							window.wfsm_frame.on( 'select', function() {

								var attachment = window.wfsm_frame.state().get('selection').first();
								window.wfsm_frame.close();

								curr.find('input:hidden').val(attachment.id);
								if ( attachment.attributes.type == 'image' ) {
									el.html('<img width="64" height="64" src="'+attachment.attributes.sizes.thumbnail.url+'" />');
								}

							});

							window.wfsm_frame.open();

							return false;

						});

						$(document).on('click', '.wfsm-quick-editor label.wfsm-product-gallery > .wfsm-add-gallery-image', function () {

							var curr_input = $(this).next();
							var curr = $(this).parent().prev();

							if ( $.isEmptyObject(window.wfsm_frame_gallery) == false ) {

								window.wfsm_frame_gallery.off("select");

								window.wfsm_frame_gallery.on( 'select', function() {

									var attachment = window.wfsm_frame_gallery.state().get('selection');
									window.wfsm_frame_gallery.close();

									attachment.each( function(curr_att) {
										curr_input.prepend('<option value="'+curr_att.id+'" selected="selected">'+curr_att.id+'</option>');
										if ( curr_att.attributes.type == 'image' ) {
											curr.prepend('<span class="wfsm-product-gallery-image" data-id="'+curr_att.id+'"><img width="64" height="64" src="'+curr_att.attributes.sizes.thumbnail.url+'" /><a href="#" class="wfsm-remove-gallery-image"><i class="wfsmico-discard"></i></a></span>');
										}
									});

								});

								window.wfsm_frame_gallery.open();

								return false;
							}


							window.wfsm_frame_gallery = wp.media({
								title: '<?php _e('Select Product Images','wfsm'); ?>',
								button: {
									text: '<?php _e( 'Add Images', 'wfsm' ); ?>',
									close: false
								},
								multiple: true,
								default_tab: 'upload',
								tabs: 'upload, library',
								returned_image_size: 'thumbnail'
							});

							window.wfsm_frame_gallery.off("select");

							window.wfsm_frame_gallery.on( 'select', function() {

								var attachment = window.wfsm_frame_gallery.state().get('selection');
								window.wfsm_frame_gallery.close();

								attachment.each( function(curr_att) {
									curr_input.prepend('<option value="'+curr_att.id+'" selected="selected">'+curr_att.id+'</option>');
									if ( curr_att.attributes.type == 'image' ) {
										curr.prepend('<span class="wfsm-product-gallery-image" data-id="'+curr_att.id+'"><img width="64" height="64" src="'+curr_att.attributes.sizes.thumbnail.url+'" /><a href="#" class="wfsm-remove-gallery-image"><i class="wfsmico-discard"></i></a></span>');
									}
								});

							});

							window.wfsm_frame_gallery.open();

							return false;

						});

						$(document).on('click', '.wfsm-remove-gallery-image', function () {

							var el = $(this).parent();
							var curr = el.parent().next();
							var el_id = el.attr('data-id');

							el.remove();
							curr.find('select option[value="'+el_id+'"]').remove();

							return false;

						});

						$(document).on('click', '.wfsm-remove-image', function () {

							var el = $(this).parent().prev().find('.wfsm-featured-image-trigger');
							var curr = el.parent();

							el.html('<img width="64" height="64" src="<?php echo plugins_url( 'assets/images/placeholder.gif', __FILE__ ); ?>">');
							curr.find('input').val('0');

							return false;

						});

						$('.wfsm-group-taxonomies .wfsm-selectize select').each( function() {
							var curr = $(this);

							curr.selectize({
								plugins: ['remove_button'],
								delimiter: ',',
								persist: false,
								onItemAdd: function(input) {
									if ( curr.closest('label').attr('for') == 'wfsm-select-attributes' ) {

										var el = $('.wfsm-buttons.wfsm-active');

										var curr_data = {
											action: 'wfsm_create_attribute',
											wfsm_id: el.attr('data-id'),
											wfsm_add: input
										}

										$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', curr_data, function(response) {
											if (response) {

												curr.closest('.wfsm-screen').find('.wfsm-group-taxonomies').append(response);
												curr.closest('.wfsm-screen').find('.wfsm-group-taxonomies').find('div:last select:first').selectize({
													plugins: ['remove_button'],
													delimiter: ',',
													persist: false,
													create: function(input) {
														return {
															value: input,
															text: input
														}
													}
												});

											}
											else {
												alert('Error!');
											}
										});

									}
								},
								onItemRemove: function(input) {
									if ( curr.closest('label').attr('for') == 'wfsm-select-attributes' ) {
										$('.wfsm-attribute-'+input).remove();
									}
								},
								create: function(input) {
									return {
										value: input,
										text: input
									}
								}
							});
						});
					})(jQuery);
				</script>
			</div>
		</div>
	<?php
		$out = ob_get_clean();

		die($out);
		exit;

	}

	function product_exist( $sku ) {
		global $wpdb;
		$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value= %s LIMIT 1", $sku ) );
		return $product_id;
	}

	function wfsm_save() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX && isset($_POST) && isset($_POST['wfsm_id']) && get_post_status($_POST['wfsm_id']) ) === false ) {
			die();
			exit;
		}

		if ( !current_user_can( 'edit_product', array( 'ID' => $_POST['wfsm_id']) ) ) {
			return;
		}

		$curr_data = array();
		$curr_data = json_decode( stripslashes( $_POST['wfsm_save'] ), true );

		$curr_post = array(
			'ID' => $_POST['wfsm_id'],
			'post_title' => $curr_data['wfsm-product-name'],
		);

		if ( isset($curr_data['wfsm-grouping']) && $curr_data['wfsm-grouping'] !== '' ) {
			$curr_post['post_parent'] = absint( $curr_data['wfsm-grouping'] );
		}
		else if ( isset($curr_data['wfsm-grouping']) && $curr_data['wfsm-grouping'] == '' ){
			$curr_post['post_parent'] = '';
		}

		wp_update_post( $curr_post );

		if ( isset($curr_data['wfsm-product-gallery']) && $curr_data['wfsm-product-gallery'] !== null && is_array( $curr_data['wfsm-product-gallery'] ) ) {
			if ( count($curr_data['wfsm-product-gallery']) > 1 ) {
				$curr_gallery_images = implode( $curr_data['wfsm-product-gallery'], ',' );
			}
			else {
				$curr_gallery_images = $curr_data['wfsm-product-gallery'][0];
			}
			update_post_meta( $_POST['wfsm_id'], '_product_image_gallery', $curr_gallery_images );
		}
		else if ( isset($curr_data['wfsm-product-gallery']) && $curr_data['wfsm-product-gallery'] == null ) {
			update_post_meta( $_POST['wfsm_id'], '_product_image_gallery', '' );
		}

		if ( isset($curr_data['wfsm-product-http']) && $curr_data['wfsm-product-http'] !== '' ) {
			update_post_meta( $_POST['wfsm_id'], '_product_url', esc_url( $curr_data['wfsm-product-http'] ) );
		}

		if ( isset($curr_data['wfsm-sku']) && !$this->product_exist( $curr_data['wfsm-sku'] ) ) {
			update_post_meta( $_POST['wfsm_id'], '_sku', $curr_data['wfsm-sku'] );
		}

		if ( isset($curr_data['wfsm-featured-image']) ) {
			update_post_meta( $_POST['wfsm_id'], '_thumbnail_id', $curr_data['wfsm-featured-image'] );
		}

		if ( isset($curr_data['wfsm-regular-price']) && $curr_data['wfsm-regular-price'] !== '' ) {
			update_post_meta( $_POST['wfsm_id'], '_regular_price', intval( $curr_data['wfsm-regular-price'] ) );
			if ( intval( $curr_data['wfsm-sale-price'] ) == '' ) {
				update_post_meta( $_POST['wfsm_id'], '_price', intval( $curr_data['wfsm-regular-price'] ) );
			}
		}
		else if ( isset($curr_data['wfsm-regular-price']) && $curr_data['wfsm-regular-price'] == '' ) {
			update_post_meta( $_POST['wfsm_id'], '_regular_price', '' );
		}

		if ( isset($curr_data['wfsm-sale-price']) && $curr_data['wfsm-sale-price'] !== '' ) {
			update_post_meta( $_POST['wfsm_id'], '_sale_price', intval( $curr_data['wfsm-sale-price'] ) );

			if ( $curr_data['wfsm-schedule-sale-start'] == '' && $curr_data['wfsm-schedule-sale-end'] == '' ) {
				update_post_meta( $_POST['wfsm_id'], '_price', intval( $curr_data['wfsm-sale-price'] ) );
			}
			else {
				update_post_meta( $_POST['wfsm_id'], '_price', intval( $curr_data['wfsm-regular-price'] ) );
			}
		}
		else if ( isset($curr_data['wfsm-sale-price']) && $curr_data['wfsm-sale-price'] == '' ) {
			update_post_meta( $_POST['wfsm_id'], '_sale_price', '' );
		}


		if ( isset($curr_data['wfsm-schedule-sale-start']) && $curr_data['wfsm-schedule-sale-start'] !== '' && isset($curr_data['wfsm-schedule-sale-end']) && $curr_data['wfsm-schedule-sale-end'] !== '' ) {

			$curr_date = explode( '-', $curr_data['wfsm-schedule-sale-start'] );
			$curr_newdate = strtotime( $curr_date[2] . '-' . $curr_date[1] . '-' . $curr_date[0] );
			update_post_meta( $_POST['wfsm_id'], '_sale_price_dates_from', $curr_newdate );

			$curr_date = explode( '-', $curr_data['wfsm-schedule-sale-end'] );
			$curr_newdate = strtotime( $curr_date[2] . '-' . $curr_date[1] . '-' . $curr_date[0] );
			update_post_meta( $_POST['wfsm_id'], '_sale_price_dates_to', $curr_newdate );

		}
		else if ( ( isset($curr_data['wfsm-schedule-sale-start']) && $curr_data['wfsm-schedule-sale-start'] == '' ) || ( isset($curr_data['wfsm-schedule-sale-end']) && $curr_data['wfsm-schedule-sale-end'] !== '' ) ) {
			update_post_meta( $_POST['wfsm_id'], '_sale_price_dates_from', '' );
			update_post_meta( $_POST['wfsm_id'], '_sale_price_dates_to', '' );
		}

		if ( isset($curr_data['wfsm-product-status']) ) {
			update_post_meta( $_POST['wfsm_id'], '_stock_status', $curr_data['wfsm-product-status'] );
		}
		if ( isset($curr_data['wfsm-manage-stock-quantity']) ) {
			update_post_meta( $_POST['wfsm_id'], '_manage_stock', $curr_data['wfsm-manage-stock-quantity'] );
			if ( $curr_data['wfsm-manage-stock-quantity'] == 'yes' ) {
				if ( $curr_data['wfsm-stock-quantity'] !== '' ) {
					update_post_meta( $_POST['wfsm_id'], '_stock', intval( $curr_data['wfsm-stock-quantity'] ) );
				}
				update_post_meta( $_POST['wfsm_id'], '_backorders', $curr_data['wfsm-backorders'] );
			}
		}

		if ( isset($curr_data['wfsm-select-product_cat']) && $curr_data['wfsm-select-product_cat'] !== null && is_array( $curr_data['wfsm-select-product_cat'] ) ) {

			$add_terms = array();

			foreach ( $curr_data['wfsm-select-product_cat'] as $curr_tax ) {
				$curr_slug = sanitize_title( $curr_tax );
				if ( !get_term_by( 'slug', $curr_slug, 'product_cat' ) ) {
					wp_insert_term( $curr_tax, 'product_cat', array( 'slug' => $curr_tax ) );
				}
				$add_terms[] = $curr_slug;
			}
			wp_set_object_terms( $_POST['wfsm_id'], $add_terms, 'product_cat' );

		}
		else {
			wp_set_object_terms( $_POST['wfsm_id'], array(), 'product_cat' );
		}

		if ( isset($curr_data['wfsm-select-product_tag']) && $curr_data['wfsm-select-product_tag'] !== null && is_array( $curr_data['wfsm-select-product_tag'] ) ) {

			$add_terms = array();

			foreach ( $curr_data['wfsm-select-product_tag'] as $curr_tax ) {
				$curr_slug = sanitize_title( $curr_tax );
				if ( !get_term_by( 'slug', $curr_slug, 'product_tag' ) ) {
					wp_insert_term( $curr_tax, 'product_tag', array( 'slug' => $curr_tax ) );
				}
				$add_terms[] = $curr_slug;
			}
			wp_set_object_terms( $_POST['wfsm_id'], $add_terms, 'product_tag' );

		}
		else {
			wp_set_object_terms( $_POST['wfsm_id'], array(), 'product_tag' );
		}

		if ( isset($curr_data['wfsm-select-attributes']) && $curr_data['wfsm-select-attributes'] !== null && is_array( $curr_data['wfsm-select-attributes'] ) ) {

			global $wpdb;

			$add_terms = array();

			foreach ( $curr_data['wfsm-select-attributes'] as $curr_tax ) {
				$curr_slug = wc_sanitize_taxonomy_name( stripslashes( $curr_tax ) );
				if ( substr($curr_slug, 0, 3) !== 'pa_' && !taxonomy_exists( 'pa_' . $curr_slug ) ) {

					$curr_attribute = array(
						'attribute_label'   => ucfirst($curr_tax),
						'attribute_name'    => $curr_slug,
						'attribute_type'    => 'select',
						'attribute_orderby' => 'menu_order',
						'attribute_public'  => 0
					);

					$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $curr_attribute );

					$add_terms['pa_' . $curr_slug] = array(
						'name' => 'pa_' . $curr_slug,
						'value' => '',
						'is_visible' => ( $curr_data['wfsm-visible-pa_' . $curr_slug] == 'isvisible' ? '1' : '0' ),
						'is_variation' => '0',
						'is_taxonomy' => '1'
					);

					$curr_tax_args = array(
						'label' => ucfirst($curr_tax),
						'rewrite' => array( 'slug' => $curr_slug ),
						'hierarchical' => true
					);
					register_taxonomy( 'pa_' . $curr_slug, 'product', $curr_tax_args );

					$refresh = 'added';

				}
				else {
					$add_terms[$curr_slug] = array(
						'name' => $curr_slug,
						'value' => '',
						'is_visible' => ( $curr_data['wfsm-visible-' . $curr_slug] == 'isvisible' ? '1' : '0' ),
						'is_variation' => '0',
						'is_taxonomy' => '1'
					);
				}

			}

			if ( isset($refresh) ) {
				delete_transient( 'wc_attribute_taxonomies' );
			}

			update_post_meta( $_POST['wfsm_id'], '_product_attributes', $add_terms );

			$product = wc_get_product( $_POST['wfsm_id'] );

			$attribute_taxonomies = wc_get_attribute_taxonomies();
			$product_attributes = $product->get_attributes();

			if ( !empty( $attribute_taxonomies ) && !is_wp_error( $attribute_taxonomies ) ) {

				foreach ($attribute_taxonomies as $tax) {
					if ( !array_key_exists( 'pa_' . $tax->attribute_name, $product_attributes ) ) {
						continue;
					}
					$curr_name = sanitize_title($tax->attribute_name);
					$curr_paname = 'pa_' . $tax->attribute_name;

					if ( $curr_data['wfsm-select-' . $curr_paname] !== null && is_array( $curr_data['wfsm-select-' . $curr_paname] ) ) {
						$add_terms = array();

						foreach ( $curr_data['wfsm-select-' . $curr_paname] as $curr_tax ) {
							$curr_slug = sanitize_title( $curr_tax );
							if ( !get_term_by( 'slug', $curr_slug, $curr_paname ) ) {
								wp_insert_term( $curr_tax, $curr_paname, array( 'slug' => $curr_slug ) );
							}
							$add_terms[] = $curr_slug;
						}
						wp_set_object_terms( $_POST['wfsm_id'], $add_terms, $curr_paname );

					}
					else {
						wp_set_object_terms( $_POST['wfsm_id'], array(), $curr_paname );
					}

				}
			}

		}
		else if ( isset($curr_data['wfsm-select-attributes']) && $curr_data['wfsm-select-attributes'] === null ) {
			update_post_meta( $_POST['wfsm_id'], '_product_attributes', array() );
		}


		if ( isset($curr_data['wfsm-variations-ids']) && is_array($curr_data['wfsm-variations-ids']) ) {
			foreach ( $curr_data['wfsm-variations-ids'] as $curr_variation ) {

				if ( isset($curr_data['wfsm-sku-' . $curr_variation]) && !$this->product_exist( $curr_data['wfsm-sku-' . $curr_variation] ) ) {
					update_post_meta( $curr_variation, '_sku', $curr_data['wfsm-sku-' . $curr_variation] );
				}

				if ( isset($curr_data['wfsm-featured-image-' . $curr_variation]) ) {
					update_post_meta( $curr_variation, '_thumbnail_id', $curr_data['wfsm-featured-image-' . $curr_variation] );
				}

				if ( isset($curr_data['wfsm-regular-price-' . $curr_variation]) && $curr_data['wfsm-regular-price-' . $curr_variation] !== '' ) {
					update_post_meta( $curr_variation, '_regular_price', intval( $curr_data['wfsm-regular-price-' . $curr_variation] ) );
					if ( intval( $curr_data['wfsm-sale-price-' . $curr_variation] ) == '' ) {
						update_post_meta( $curr_variation, '_price', intval( $curr_data['wfsm-regular-price-' . $curr_variation] ) );
					}
				}
				else if ( isset($curr_data['wfsm-regular-price-' . $curr_variation]) && $curr_data['wfsm-regular-price-' . $curr_variation] == '' ) {
					update_post_meta( $curr_variation, '_regular_price', '' );
				}

				if ( isset($curr_data['wfsm-sale-price-' . $curr_variation]) && $curr_data['wfsm-sale-price-' . $curr_variation] !== '' ) {
					update_post_meta( $curr_variation, '_sale_price', intval( $curr_data['wfsm-sale-price-' . $curr_variation] ) );

					if ( $curr_data['wfsm-schedule-sale-start-' . $curr_variation] == '' && $curr_data['wfsm-schedule-sale-end-' . $curr_variation] == '' ) {
						update_post_meta( $curr_variation, '_price', intval( $curr_data['wfsm-sale-price-' . $curr_variation] ) );
					}
					else {
						update_post_meta( $curr_variation, '_price', intval( $curr_data['wfsm-regular-price-' . $curr_variation] ) );
					}
				}
				else if ( isset($curr_data['wfsm-sale-price-' . $curr_variation]) && $curr_data['wfsm-sale-price-' . $curr_variation] == '' ) {
					update_post_meta( $curr_variation, '_sale_price', '' );
				}


				if ( isset($curr_data['wfsm-schedule-sale-start-' . $curr_variation]) && $curr_data['wfsm-schedule-sale-start-' . $curr_variation] !== '' && isset($curr_data['wfsm-schedule-sale-end-' . $curr_variation]) && $curr_data['wfsm-schedule-sale-end-' . $curr_variation] !== '' ) {

					$curr_date = explode( '-', $curr_data['wfsm-schedule-sale-start-' . $curr_variation] );
					$curr_newdate = strtotime( $curr_date[2] . '-' . $curr_date[1] . '-' . $curr_date[0] );
					update_post_meta( $curr_variation, '_sale_price_dates_from', $curr_newdate );

					$curr_date = explode( '-', $curr_data['wfsm-schedule-sale-end-' . $curr_variation] );
					$curr_newdate = strtotime( $curr_date[2] . '-' . $curr_date[1] . '-' . $curr_date[0] );
					update_post_meta( $curr_variation, '_sale_price_dates_to', $curr_newdate );

				}
				else if ( ( isset($curr_data['wfsm-schedule-sale-start-' . $curr_variation]) && $curr_data['wfsm-schedule-sale-start-' . $curr_variation] == '' ) || ( isset($curr_data['wfsm-schedule-sale-end-' . $curr_variation]) && $curr_data['wfsm-schedule-sale-end-' . $curr_variation] !== '' ) ) {
					update_post_meta( $curr_variation, '_sale_price_dates_from', '' );
					update_post_meta( $curr_variation, '_sale_price_dates_to', '' );
				}

				if ( isset($curr_data['wfsm-product-status-' . $curr_variation]) ) {
					update_post_meta( $curr_variation, '_stock_status', $curr_data['wfsm-product-status-' . $curr_variation] );
				}
				if ( isset($curr_data['wfsm-manage-stock-quantity-' . $curr_variation]) ) {
					update_post_meta( $curr_variation, '_manage_stock', $curr_data['wfsm-manage-stock-quantity-' . $curr_variation] );
					if ( $curr_data['wfsm-manage-stock-quantity-' . $curr_variation] == 'yes' ) {
						if ( $curr_data['wfsm-stock-quantity-' . $curr_variation] !== '' ) {
							update_post_meta( $curr_variation, '_stock', intval( $curr_data['wfsm-stock-quantity-' . $curr_variation] ) );
						}
						update_post_meta( $curr_variation, '_backorders', $curr_data['wfsm-backorders-' . $curr_variation] );
					}
				}


			}
		}

		if ( $_POST['wfsm_loop'] !== 'single' ) {
			$wfsm_settings = explode( '|', $_POST['wfsm_loop'] );

			global $woocommerce_loop;

			$woocommerce_loop = array(
				'loop' => $wfsm_settings[0]-1,
				'columns' => $wfsm_settings[1]
			);
		}

		$curr_products = new WP_Query( array( 'post_type' => 'product', 'post__in' => array( $_POST['wfsm_id'] ) ) );

		ob_start();

		if ( $curr_products->have_posts() ) {

			while ( $curr_products->have_posts() ) : $curr_products->the_post();

				if ( $_POST['wfsm_loop'] !== 'single' ) {
					wc_get_template_part( 'content', 'product' );
				}
				else {
					$out = 'single';
				}

			endwhile;

		}

		$out = ob_get_clean();

		die($out);
		exit;
	}

	function wfsm_create_attribute() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX && isset($_POST) && isset($_POST['wfsm_id']) && get_post_status($_POST['wfsm_id']) ) === false ) {
			die();
			exit;
		}

		if ( !current_user_can( 'edit_product', array( 'ID' => $_POST['wfsm_id']) ) ) {
			return;
		}

		$product = wc_get_product( $_POST['wfsm_id'] );

			ob_start();

			$curr_slug = wc_sanitize_taxonomy_name( stripslashes( $_POST['wfsm_add'] ) );
			if ( substr($curr_slug, 0, 3) !== 'pa_' && !taxonomy_exists( 'pa_' . $curr_slug ) ) {

						$curr_name = sanitize_title($curr_slug);
						$curr_paname = 'pa_' . $curr_slug;
			}
			else {
						$tax = get_taxonomy($curr_slug);
						$curr_name = $tax->label;
						$curr_paname = $curr_slug;
			}
		?>
			<div class="wfsm-attribute-<?php echo $curr_paname; ?>">
				<label for="wfsm-select-<?php echo $curr_paname; ?>" class="wfsm-selectize">
					<span><?php echo __( 'Product', 'wfsm' ) . ' ' . ucfirst( $curr_name ); ?></span>
					<select id="wfsm-select-<?php echo $curr_paname; ?>" name="wfsm-select-<?php echo $curr_paname; ?>" class="wfsm-collect-data" multiple="multiple">
					<?php
						if ( isset($tax) ) {
							$product_atts = wp_get_post_terms($_POST['wfsm_id'], $curr_paname, array( 'fields' => 'slugs' ) );
							foreach( get_terms($curr_paname,'parent=0&hide_empty=0') as $term ) {
								$wfsm_selected = in_array( $term->slug , $product_atts ) ? 'added' : 'notadded' ;
							?>
								<option <?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
							<?php
							}
						}

					?>
					</select>
				</label>
			<?php
				$product_attributes = $product->get_attributes();
				$curr_value = ( ( isset( $product_attributes[$curr_paname]) && $product_attributes[$curr_paname]['is_visible'] == 1 ) ? 'isvisible' : 'notvisible' );
			?>
				<label for="wfsm-visible-<?php echo $curr_paname; ?>" class="wfsm-label-checkbox wfsm-<?php echo $curr_value; ?>">
					<span class="wfsm-show-isvisible"><?php _e( 'Attribute is visible on product page', 'wfsm' ); ?></span>
					<span class="wfsm-show-notvisible"><?php _e( 'Attribute is not visible on product page', 'wfsm' ); ?></span>
					<input id="wfsm-visible-<?php echo $curr_paname; ?>" name="wfsm-visible-<?php echo $curr_paname; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $curr_value; ?>"/>
				</label>
			</div>
		<?php

			$out = ob_get_clean();

			die($out);
			exit;

	}

}

?>