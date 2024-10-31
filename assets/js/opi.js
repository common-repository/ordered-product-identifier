/**
 * 
 */
jQuery(document).ready(function ($) {
   jQuery('#ced_opi_enbl').click(function () {
	    // var $this = $(this);
	     if ( jQuery('#ced_opi_enbl').is(':checked')) {
	         jQuery('.unique-ID').show();
	     } else {
	    	 jQuery('.unique-ID').hide();
	     }
	 });
   
   if ( jQuery('#ced_opi_manually').is(':checked')) {
       jQuery('.ced_opi_prefix').hide();
   } else {
  	 jQuery('.ced_opi_prefix').show();
   }
   jQuery('#ced_opi_auto').click(function () {
	    // var $this = $(this);
	     if ( jQuery('#ced_opi_auto').is(':checked')) {
	         jQuery('.ced_opi_prefix').show();
	     } else {
	    	 jQuery('.ced_opi_prefix').hide();
	     }
	 });
   jQuery('#ced_opi_manually').click(function () {
	    // var $this = $(this);
	     if ( jQuery('#ced_opi_manually').is(':checked')) {
	         jQuery('.ced_opi_prefix').hide();
	     } else {
	    	 jQuery('.ced_opi_prefix').show();
	     }
	 });
   jQuery('#ced_opi_img_send_email').click(function(e) {
		e.preventDefault();
		jQuery(".ced_opi_img_email_image p").removeClass("ced_opi_email_image_success");
		jQuery(".ced_opi_img_email_image p").removeClass("ced_opi_email_image_error");

		jQuery(".ced_opi_img_email_image p").html("");
		var email = jQuery('.ced_opi_img_email_field').val();
		jQuery("#ced_opi_loader").removeClass("hide");
		jQuery("#ced_opi_loader").addClass("dislay");
		$.ajax({
	        type:'POST',
	        url :ajax_url,
	        data:{action:'ced_opi_send_mail',flag:true,emailid:email},
	        success:function(data)
	        {
				var new_data = JSON.parse(data);
	        	jQuery("#ced_opi_loader").removeClass("dislay");
				jQuery("#ced_opi_loader").addClass("hide");
				if(new_data['status']==true)
		        {
					jQuery(".ced_opi_img_email_image p").addClass("ced_opi_email_image_success");
					jQuery(".ced_opi_img_email_image p").html(new_data['msg']);
		        }
		        else
		        {
					jQuery(".ced_opi_img_email_image p").addClass("ced_opi_email_image_error");
					jQuery(".ced_opi_img_email_image p").html(new_data['msg']);
		        }
	        }
    	});
	});
});

