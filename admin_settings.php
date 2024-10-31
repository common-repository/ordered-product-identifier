<?php
if ( ! defined( 'ABSPATH' ) )
{
	exit;
}
$val = get_option(CED_OPI_PREFIX.'_compat');
$wpinvdl = get_option(CED_OPI_PREFIX.'_wpinvdl');
$opienbl = get_option(CED_OPI_PREFIX.'_enbl');
$opiprefix = get_option(CED_OPI_PREFIX.'_prefix');
$opi_radio_opt = get_option(CED_OPI_PREFIX.'_radio_opt');
$inp_txt_val = '';
if(isset($opiprefix) && !empty ($opiprefix)){ 
	$inp_txt_val = $opiprefix;
}else{
	$inp_txt_val = 'OPI';
}
if(isset($_GET["ced_opi_close"]) && $_GET["ced_opi_close"])
	{
		unset($_GET["ced_opi_close"]);
		if(!session_id())
			session_start();
		$_SESSION["ced_opi_hide_email"]=true;
		wp_redirect(admin_url('admin.php').'?page=ordered_product_identifier');
		exit();
	}
?>
<div class="ced_opi_frm_wrp">

		<?php
		if(!session_id())
			session_start();
		if(!isset($_SESSION["ced_opi_hide_email"])):
			$actual_link = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$urlvars = parse_url($actual_link);
			$url_params = $urlvars["query"];
		?>
		<div class="ced_opi_img_email_image">
			<div class="ced_opi_email_main_content">
				<div class="ced_opi_cross_image_container">
				<a class="button-primary ced_opi_cross_image" href="?<?php echo $url_params?>&ced_opi_close=true"></a>
				</div>
				<input type="text" value="" class="ced_opi_img_email_field" placeholder="<?php _e("enter your e-mail address","order-product-identifier")?>"/> 
				<a id="ced_opi_img_send_email" href=""><?php _e("Know More","order-product-identifier")?></a>
				<p></p>
				<div class="hide"  id="ced_opi_loader">	
					<img id="ced-opi-loading-image" src="<?php echo plugins_url().'/ordered-product-identifier/assets/images/ajax-loader.gif'?>" >
				</div>
				<div class="ced_opi_banner">
				<a target="_blank" href="https://cedcommerce.com/offers#woocommerce-offers"><img src="<?php echo plugins_url().'/ordered-product-identifier/assets/images/ebay.jpg'?>"></a>
				</div>
			</div>
		</div>
		<?php endif;?>


		<div class="ced_opi_frm">
			<form method="post" class="frm" action="options.php">
				<?php settings_fields( 'ced-opi-settings-group'); ?>
			   				 	<?php do_settings_sections('ced-opi-settings-group' ); ?>
				<h3><?php _e( 'COMPATIBILITY WITH INVOICE PLUGINS', 'order-product-identifier' );?></h3>
				<p>
					<input type="checkbox" name="ced_opi_compat" value="yes" <?php if($val=="yes"){?> checked <?php } ?>><?php _e('Do you want Compatiblity with WooCommerce PDF Invoices & Packing Slips', 'order-product-identifier' );?>
				</p>
				<p>
					<input type="checkbox" name="ced_opi_wpinvdl" value="yes" <?php if($wpinvdl=="yes"){?> checked <?php }?>><?php _e('Do you want compatibility with Woocommerce Print Invoices and Delivery Notes', 'order-product-identifier' );?>
				</p>
				<p>
					<input type="checkbox" name="ced_opi_enbl" id="ced_opi_enbl"  value="yes" <?php if($opienbl=="yes"){?> checked <?php }?>><?php _e('Enable to generate unique identification no.', 'order-product-identifier' );?>
				</p>
				<div class="unique-ID">
					<input type="radio" name="ced_opi_radio_opt" id="ced_opi_manually"  value="ced_opi_manually" <?php checked('ced_opi_manually', $opi_radio_opt); ?>><?php _e('Manually', 'order-product-identifier' );?><br>
					<input type="radio" name="ced_opi_radio_opt" id="ced_opi_auto"  value="ced_opi_auto" <?php checked('ced_opi_auto', $opi_radio_opt); ?>><?php _e('Automatically', 'order-product-identifier' );?>
					<h2 class="ced_opi_prefix"><?php _e('For automatic generation of Unique-ID', 'order-product-identifier') ?></h2>
					<input type="text" class="ced_opi_prefix va" name="ced_opi_prefix" value="<?php echo $inp_txt_val?>" placeholder="OPI" required>
					<i class="ced_opi_prefix"><?php _e('Enter Prefix ( Required )', 'order-product-identifier' );?></i>
				</div>
				<p>
					<?php submit_button(); ?>
				</p>
			</form>
		</div>
	
	<div class="ced_opi_frm">
		<h3 class="head"><?php _e('FUNCTION FOR GETTING IDENTIFICATION ID', 'order-product-identifier');?></h3>
		<h4><?php _e('You strictly need order id and product id to get unique identification to be printed over invoice through this function','order-product-identifier');?></h4>
		<p class="function"><?php _e('ced_opi_pdf($order_id,$product_id)', 'order-product-identifier');?></p>
	</div>
</div>