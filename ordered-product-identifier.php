<?php
/**
 * Plugin Name: Ordered Product Identifier
 * Plugin URI: http://cedcommerce.com
 * Description: It provide ordered product identification id for products which are ordered and displayed on order detail page when order is completed and also provides feature to print this identification id over invoice.
 * Author: CedCommerce
 * Author URI: http://cedcommerce.com
 * Version: 1.0.6
 * Requires at least: 3.8
 * Tested up to: 5.2.0
 * Text Domain: order-product-identifier
 * Domain Path: /languages
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) 
{
	exit; 
}

$activated = true;
if (function_exists('is_multisite') && is_multisite())
{
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	if (!is_plugin_active('woocommerce/woocommerce.php'))
	{
		$activated = false;
	}
}
else
{
	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
	{
		$activated = false;
	}
}

/**
 * Check if WooCommerce is active
 **/

if ($activated) 
{
	define('CED_OPI_PREFIX', 'ced_opi');
	define('CED_OPI_DIR_URL', plugin_dir_url( __FILE__ )); 
	define('CED_OPI_DIR', plugin_dir_path( __FILE__ ));
	add_action('plugins_loaded','ced_opi_load_text_domain');
	
	/**
	 * This function is used to load language'.
	 * @name ced_opi_load_text_domain()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	*/
	
	function ced_opi_load_text_domain()
	{
		$domain = "order-product-identifier";
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, CED_OPI_DIR .'language/'.$domain.'-' . $locale . '.mo' );
		$var=load_plugin_textdomain( 'order-product-identifier', false, plugin_basename( dirname(__FILE__) ) . '/language' );
	}
	
	
	if( ! class_exists( 'Ced_order_product_identifier' ) )
	{
		class Ced_order_product_identifier
		{
			/**
			 * all required functions are hooked here
			 * @name __construct
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/ 
			 */
			public function __construct()
			{
				$plugin = plugin_basename(__FILE__);
				add_filter('plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2);
				add_action('admin_init', array( $this, 'ced_opi_save_settings' ) );
				add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this,'ced_opi_add_settings_link'),10,1);
				add_action('woocommerce_admin_order_item_headers', array($this,'ced_opi_header'));
				add_action('woocommerce_admin_order_item_values', array($this,'ced_opi_content'), 10, 3);
				add_action('woocommerce_process_shop_order_meta', array($this,'ced_opi_id_save'), 10, 2);
				add_action('woocommerce_order_item_meta_start', array($this,'ced_opi_frontend_action'),10,3);
				add_action('admin_menu',array($this,'ced_opi_menu_settings'));
				add_action('wpo_wcpdf_before_item_meta', array($this,'ced_opi_woopdfinvcpkgsl_compatible'),10,3);
				add_action('wcdn_order_item_before',array($this,'ced_opi_wooprinvcdn_compat'),10,2);
				add_action('admin_enqueue_scripts', array( $this,'add_our_script' ));
				add_action('wp_ajax_ced_opi_send_mail',array($this,'ced_opi_send_mail'));
				register_deactivation_hook(__FILE__,array( $this, 'ced_opi_plugin_deactivation') );
			}
			
			/**
			 * Enqueues style of plugin
			 * @name add_our_script
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			
			function add_our_script()
			{
				wp_enqueue_style('ordered-product-style',plugin_dir_url( __FILE__ ).'assets/css/ordered-product-style.css');
				wp_enqueue_script('opi-js', plugin_dir_url( __FILE__ ).'assets/js/opi.js', array('jquery'));
				wp_localize_script('opi-js','ajax_url',admin_url('admin-ajax.php'));
			}


			function ced_opi_send_mail()
			{
				if(isset($_POST["flag"]) && $_POST["flag"]==true && !empty($_POST["emailid"]))
				{
					$to = "support@cedcommerce.com";
					$subject = "Wordpress Org Know More";
					$message = 'This user of our woocommerce extension "Ordered Product Identifier" wants to know more about marketplace extensions.<br>';
					$message .= 'Email of user : '.$_POST["emailid"];
					$headers = array('Content-Type: text/html; charset=UTF-8');
					$flag = wp_mail( $to, $subject, $message);	
					if($flag == 1)
					{
						echo json_encode(array('status'=>true,'msg'=>__('Soon you will receive the more details of this extension on the given mail.',"order-product-identifier")));
					}
					else
					{
						echo json_encode(array('status'=>false,'msg'=>__('Sorry,an error occured.Please try again.',"order-product-identifier")));
					}
				}
				else
				{
					echo json_encode(array('status'=>false,'msg'=>__('Sorry,an error occured.Please try again.',"order-product-identifier")));
				}
				wp_die();
			}
			
			/**
			 * generates link of docs 
			 * @name plugin_row_meta
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 * 
			 */
			public static function plugin_row_meta( $links, $file ) 
			{
				if ( $file == "ced-ordered-product-identifier/ordered-product-identifier.php" ) 
				{
					$row_meta = array('docs'    => '<a href="' . esc_url( apply_filters( 'ced_opi_docs_url', 'http://demo.cedcommerce.com/ced-ordered-product-identifier/doc/index.html' ) ) . '" title="' . esc_attr( __( 'View Ordered Product Identifier Documentation', 'order-product-identifier' ) ) . '">' . __( 'Docs', 'order-product-identifier' ) . '</a>',);
					return array_merge( $links, $row_meta );
				}
				return (array) $links;
			}
			
			/**
			 * This function is used to add setting menu.
			 * @name ced_opi_menu_settings
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			
			function ced_opi_menu_settings()
			{
				add_menu_page('Ordered Product Identifier', 'Ordered Product Identifier', 'manage_options', 'ordered_product_identifier', array($this,'ced_opi_settings') );
			}
			
			/**
			 * provides interface to deal with invoice compatibility
			 * @name ced_opi_settings
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			
			function ced_opi_settings()
			{
				require_once plugin_dir_path( __FILE__ ).'admin_settings.php';
			}
			
			/**
			 * saves settings field data to options
			 * @name ced_opi_save_settings
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			
			function ced_opi_save_settings()
			{
				register_setting('ced-opi-settings-group',sanitize_text_field(CED_OPI_PREFIX.'_compat'));
				register_setting('ced-opi-settings-group',sanitize_text_field(CED_OPI_PREFIX.'_wpcompat'));
				register_setting('ced-opi-settings-group',sanitize_text_field(CED_OPI_PREFIX.'_wpinvdl'));
				register_setting('ced-opi-settings-group',sanitize_text_field(CED_OPI_PREFIX.'_enbl'));
				register_setting('ced-opi-settings-group',sanitize_text_field(CED_OPI_PREFIX.'_prefix'));
				register_setting('ced-opi-settings-group',sanitize_text_field(CED_OPI_PREFIX.'_radio_opt'));
			}
			
			/**
			 * add setting link to the plugin
			 * @name ced_opi_add_settings_link
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 * @param string $links
			 * @return string
			 */
			
			function ced_opi_add_settings_link($links)
			{
				$settings_link = '<a href="'.get_admin_url().'admin.php?page=ordered_product_identifier">Settings</a>';
				array_unshift( $links, $settings_link );
				return $links;
			}
			
			/**
			 * Delete option for notification.
			 * @name ced_opi_plugin_deactivation()
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			
			function ced_opi_plugin_deactivation()
			{
				delete_option('ced_feed');
				delete_option('ced_feed_opi');
			}
						
			/**
			 * creating heading for new field ordered product identification id
			 * @name ced_opi_header
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			
			function ced_opi_header()
			{
				$enbl_opi = get_option(CED_OPI_PREFIX.'_enbl');
				if($enbl_opi=="yes"){
					$heading = 'Ordered Product Unique Id';
					$heading = apply_filters('order_product_identifier_admin_heading',$heading);
					echo '<th>'.__($heading,'order-product-identifier').'</th>';
				}
			}
			
			/**
			 * creating input text field for entering ordered product identification id
			 * @name ced_opi_content
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			
			function ced_opi_content($_product, $item, $item_id)
			{
				global $post;
				$unique = bin2hex(openssl_random_pseudo_bytes(4));
				$opiprefix = get_option(CED_OPI_PREFIX.'_prefix');
				if (isset ($opiprefix) && $opiprefix !=='' ){
					$unique_string_to_save = $opiprefix.'-'.$unique;
				}else{
					$unique_string_to_save = $unique;
				}
				
				$enbl_opi = get_option(CED_OPI_PREFIX.'_enbl');
				if($enbl_opi=="yes"){
					$opimanually = get_option(CED_OPI_PREFIX.'_radio_opt');
					$unique_id = get_post_meta($post->ID,$_product->id,true);
					if( isset($opimanually) && $opimanually == 'ced_opi_manually'){
						if($_product->id)
						{
							echo '<td><input type ="text" name = "'.$_product->id.'_product_identifier" value="'.$unique_id.'"></td>';
						}
					}else{
						
						if($_product->id)
						{
							if(isset($unique_id) && !empty($unique_id)){
								
								echo '<td><input type ="text" name = "'.$_product->id.'_product_identifier" value="'.$unique_id.'" readonly></td>';
						
							}else{
								
								echo '<td><input type ="text" name = "'.$_product->id.'_product_identifier" value="'.$unique_string_to_save.'" readonly></td>';
							}
						}
					}
				}
			}
	
			/**
			 * saving ordered product identification number on save click
			 * @name ced_opi_id_save
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/ 
			 */
			
			function ced_opi_id_save ( $post_id, $post ) 
			{
				
				$enbl = get_option(CED_OPI_PREFIX.'_enbl');
				if(isset($enbl)){
					$opienbl = get_option(CED_OPI_PREFIX.'_enbl');
					$order = wc_get_order($post_id);
					$products_in_order = $order->get_items();
					foreach ($products_in_order as $k => $v)
					{
						delete_post_meta($post_id, $v['product_id']);
						update_post_meta($post_id, $v['product_id'],sanitize_text_field($_POST[$v['product_id']."_product_identifier"]));
					}
				}
			}
			
			/**
			 * showing ordered product identification id on order detail page
			 * @name ced_opi_frontend_action
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/ 
			 */
			
			function ced_opi_frontend_action($item_id, $item, $order)
			{
				if ( $order->has_status( array( 'completed') ))
				{
						$enbl_opi = get_option(CED_OPI_PREFIX.'_enbl');
						if($enbl_opi=="yes"){
						$unique_id = get_post_meta($order->id,$item['product_id'],true);
						if($unique_id)
						{
							$heading = 'Identification ID';
							$heading = apply_filters('order_product_identifier_front_heading',$heading);
							echo '<p><strong>'.__($heading,'order-product-identifier').':</strong>'.$unique_id.'</p>';
							apply_filters('ced_opi_pdf_compat',$heading,$unique_id);
						}
					}
				}
			}
			
			/**
			 * show identification id on woocommerce pdf invoice and pkgslip invoice
			 * @name ced_opi_woopdfinvcpkgsl_compatible
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/ 
			 */
			function ced_opi_woopdfinvcpkgsl_compatible($template_type, $item, $order)
			{
				$chk=get_option(CED_OPI_PREFIX.'_compat');
				if($chk=="yes")
				{
					$identification_id = get_post_meta($order->id,$item['product_id'],true);
					if($identification_id)
					{
						echo '<br>';
						echo "Identification ID:"." ".$identification_id;	 
					}
				}
			}
			
			
			/**
			 * provides identification number over woocommerce print invoice and delivery notes
			 * @name ced_opi_wooprinvcdn_compat
			 * @author CedCommerce <plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/ 
			*/
			
			function ced_opi_wooprinvcdn_compat($product,$order)
			{
				$wpinvcdl=get_option(CED_OPI_PREFIX.'_wpinvdl');
				if($wpinvcdl=="yes")
				{
					$Identificationid=get_post_meta($order->id,$product->id,true);
					if($Identificationid)
					{
						echo "Identification ID:"." ".$Identificationid.'<br/>';
					}
				}
			}
	
		}
		new Ced_order_product_identifier();
		
		/**
		 * provides functionality to be added to invoice plugins to get Identification id
		 * @name ced_opi_pdf
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/ 
		 */
		
		function ced_opi_pdf($order_id,$product_id)
		{
			$id=get_post_meta($order_id,$product_id,true);
			if($id)
			{
				echo $id;
			}
		}
	}
	else
	{
		/**
		 * generates error if woocommerce is not activated
		 * @name ced_opi_plugin_error_notice
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/ 
		 */
		function ced_opi_plugin_error_notice() 
		{
			?>
			<div class="error notice is-dismissible">
			<p><?php _e( 'Woocommerce is not activated.please install woocommerce in order to use the plugin !!!', 'order-product-identifier' ); ?></p>
			</div>
			<?php
		}
	
		add_action( 'admin_init', 'ced_opi_plugin_deactivate' );
		
		/**
		 * deactivates plugin if error present
		 * @name ced_opi_plugin_deactivate
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/ 
		 */
		function ced_opi_plugin_deactivate() 
		{
			deactivate_plugins( plugin_basename( __FILE__ ) );
			add_action( 'admin_notices', 'ced_opi_plugin_error_notice' );
		}
	}
}
else
{
	/**
	 * generates error if woocommerce is not activated
	 * @name ced_opi_plugin_error_notice
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	
	function ced_opi_plugin_error_notice()
	{
		?>
		<div class="error notice is-dismissible">
			<p><?php _e( 'Woocommerce is not activated.please install woocommerce to use the Ordered Product Identifier plugin !!!', 'order-product-identifier' ); ?></p>
		</div>
		<?php
	}
		
	add_action( 'admin_init', 'ced_opi_plugin_deactivate' );
		
	/**
	 * deactivates plugin if error present
	 * @name ced_opi_plugin_deactivate
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	
	function ced_opi_plugin_deactivate() 
	{
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'ced_opi_plugin_error_notice' );
	}
	
}
?>