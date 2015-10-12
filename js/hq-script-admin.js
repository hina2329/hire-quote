jQuery(document).ready(function () {
    
    // Image upload field for products
    jQuery('#upload_image_button').click(function () {
        formfield = jQuery('#upload_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });
    
    window.send_to_editor = function (html) {
        imgurl = jQuery('img', html).attr('src');
        jQuery('#prod_img').val(imgurl);
        tb_remove();
    };
    
    
});