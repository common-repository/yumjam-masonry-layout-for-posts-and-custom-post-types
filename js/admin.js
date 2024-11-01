/* 
 Created on : 04-Apr-2016, 11:37:37
 Author     : Matt
 */
var options = [];

jQuery(document).ready(function ($) {

    //hide all but main options on load
    showTab();

    $.getJSON(ajaxurl, {action: "yj_defaults"}, function (resp) {
        if (resp.success) {
            options = resp.data;
        }
    });

    //change visible option on tab click
    $('#yumjam-masonry-options .nav-tab').click(function (e) {
        e.preventDefault();
        showTab(this.id);
    });

    $('#clear-options').click(function (e) {
       e.preventDefault();
       $( ".masonry input" ).each(function( index ) {
          $(this).val("");
        });
       $( ".masonry select" ).each(function( index ) {
          $(this).val("0");
        });

    });

    // Add Color Picker to all inputs that have 'color-field' class setting-slider
    $('.colour-picker').iris();
    $(document).click(function (e) {
        if (!$(e.target).is(".colour-picker, .iris-picker, .iris-picker-inner")) {
            $('.colour-picker').iris('hide');
            return;
        }
    });
    $('.colour-picker').click(function (event) {
        $('.colour-picker').iris('hide');
        $(this).iris('show');
        return false;
    });

    //Ui-Slider value select
    $(".setting-slider").each(function () {
        // $this is a reference to .setting-slider in current iteration of each
        var $this = $(this);
        // find any .slider-range element WITHIN scope of $this
        $(".ui-slider", $this).slider({
            min: $($this).data('min'),
            max: $($this).data('max'),
            value: $($this).data('value'),
            slide: function (event, ui) {
                // find any element with class .amount WITHIN scope of $this
                var id = 'input#' + this.id.substring(0, this.id.length - 6);
                $(id).val(ui.value);
            }
        });
    });

    if ($("input[name='brick_layout']:checked").val() != "custom") {
        $("#default_column_width_slide").closest("tr").hide();
    }

    $("input[name='brick_layout']").change(function () {
        if ($("input[name='brick_layout']:checked").val() !== "custom") {
            $("#default_column_width_slide").closest("tr").hide();
        } else
        {
            $("#default_column_width_slide").closest("tr").show();
        }
    });

    $("button#update_sc").on("click", function () {
        var pts;
        var sc = "[yumjam-masonry";

        $.each(options, function (key, val) {
            var input_val = $('#'+key).val();
            var input_type = $('#'+key).attr('type');
            
            //chosen mutli select
            switch (input_type) {
                case 'text':
                    sc += (input_val && val != input_val)?" " + key + "=\"" + input_val + "\"":"";
                    break;
                case 'radio':
                    input_val = $('#'+key+':checked').val(); 
                    sc += (input_val && val != input_val)?" " + key + "=\"" + input_val + "\"":"";
                    break;
                case 'checkbox':
                    input_val = $('#'+key+':checked').val()?"on":"off";
                    sc += (input_val && val != input_val)? " " + key + "=\"" + input_val + "\"": "";
                    break;
                default:
                    if (input_val) {
                        input_val = $('#'+key).chosen().val().join();
                        
                        if (!val) val = [];
                        sc += (val.join() != input_val)?" " + key + "=\"" + input_val + "\"":"";
                    }
                    break;                    
            }
            input_val = null;
        });

        $("#shortcode_built").val(sc + "]");
        updateDemo();
    });
    
    $('button#reset').click(function(){
        if (confirm('Are you sure you want to reset? You will lose all current changes')) {
            $.each(options, function (key, val) {
            var input_type = $('#'+key).attr('type');
            switch (input_type) {
                case 'radio':
                    $('#'+key+'[value="'+val+'"]').prop('checked', true); 
                    break;
                case 'checkbox':
                    if (val == 'on') {
                        $('#'+key).prop('checked', true); 
                    } else {
                        $('#'+key).prop('checked', false); 
                    }
                    break;
                default:
                    $('#'+key).val(val);
                    break;                    
            }
        });
            $('button#update_sc').click();
            updateDemo();

        } else {
            return;
        }
        
    });
    
    $('button#demo').click(function () {
        updateDemo('main');
    });
    
    $('button#demo_alt').click(function () {
        updateDemo('alt');
    });
    
    $("button#import_sc").on("click", function () {
        
        $.each(options, function (key, val) {
            var input_type = $('#'+key).attr('type');
            switch (input_type) {
                case 'radio':
                    $('#'+key+'[value="'+val+'"]').prop('checked', true); 
                    break;
                case 'checkbox':
                    if (val == 'on') {
                        $('#'+key).prop('checked', true); 
                    } else {
                        $('#'+key).prop('checked', false); 
                    }
                    break;
                default:
                    $('#'+key).val(val);
                    break;                    
            }
        });
        
        var sc = $("#shortcode_built").val();
        var trim = sc.substring("[yumjam-masonry ".length, sc.length - 1).split('" ');
        
        $.each(trim, function(i, v){
            
            sc = v.split("=");
            var el = $('#'+sc[0]);
            var input_val = sc[1].replace(/['"]+/g, '');
            var input_type = el[0].type;

            //chosen mutli select
            switch (input_type) {
                case 'text':
                    el.val(input_val);
                    break;
                case 'radio':
                    $('#'+sc[0]+'[value="'+input_val+'"]').prop('checked', true);
                    break;
                case 'checkbox':
                    if (input_val == 'off') {
                        $('#'+sc[0]).prop('checked', false);
                    } else {
                        $('#'+sc[0]).prop('checked', true);
                    }
                    break;
                case 'select':
                case 'select-multiple':
                    $('#'+sc[0]).val(input_val.split(',')).trigger("chosen:updated");
                    break;
                default:
                    console.log('cant import input type "'+input_type+'" with value of "'+input_val+'"');
                    break;                    
            }
            input_val = null;
        });
        updateDemo();
         
    });
    
    //Update Preview
    $("input#content_show_author").click(function(){
        jQuery('.demo-brick .headerblock h3').toggle();
    });        
    
    $("input#content_show_date").click(function(){
        jQuery('.demo-brick .headerblock h4').toggle();
    });

    $(".brick_layout").hover(function() {
            
            var thisid = $(this).attr('id');
            r_thisid = thisid.replace("radio-Style-","");
            //console.log("hover in")
            var exampleimg = "<img src='/wp-content/plugins/yumjam-masonry/assets/images/grid-template-" + r_thisid +  ".png' alt=''>";
            if ($(this).attr('id') !== "radio-Custom Layout") {
                    $(".help-brick_layout").html(exampleimg);
                    $(".help-brick_layout").stop().fadeIn();
                    $(".demo-brick .headerblock").addClass("masonry-active");
            }
        }, function() {
            //console.log("out")
            $(".help-brick_layout").stop().fadeOut();
        }
    );
    $("#yumjam-masonry-options input:checkbox, #yumjam-masonry-options input:radio").click(function(){
        updateDemo();
    });
    $("#yumjam-masonry-options input:checkbox, #yumjam-masonry-options input:radio").click(function(){
        updateDemo();
    });
    var lastValue = '';
    $("#yumjam-masonry-options input:text, #yumjam-masonry-options .iris-picker").on('change keyup paste mouseup input', function() {
        if ($(this).val() != lastValue) {
            lastValue = $(this).val();
            updateDemo();
        }
    });
    

    $('#post_types').on('change', function(event, params) {
        if (params.deselected) {
           $('select#post_cats optgroup[data-post_type='+params.deselected+']').prop('disabled', true); 
        } 
        if (params.selected) {
           $('select#post_cats optgroup[data-post_type='+params.selected+']').prop('disabled', false); 
        }
        $('select#post_cats').trigger("chosen:updated");
    });
    
    $('input#custom_enabled').on('click', function(){
        if ( $(this).is(':checked')) {
            $('div#yumjam-masonry-post-options').show();
        } else {
            $('div#yumjam-masonry-post-options').hide();
        }
    });
    
    $('select#post_cats optgroup').prop('disabled', true);
    $('select#post_cats optgroup[data-post_type=custom]').prop('disabled', false);
    $('select#post_cats').trigger("chosen:updated");
});

function showTab(tabid) {
    //console.log(tabid)
    if (tabid=="main_options" || tabid=="postload_options") {
        jQuery(".demo-brick").hide();
    }
    else
    {
        jQuery(".demo-brick").show();
    }
    if (tabid) {
        jQuery('#yumjam-masonry-options .nav-tab').removeClass('nav-tab-active');
        jQuery('#yumjam-masonry-options .form-table tr').hide();
        jQuery('a#' + tabid).addClass('nav-tab-active');
        jQuery('.tab-' + tabid).closest('tr').show();
        localStorage.setItem("masonry-settings-tab", tabid);
        if (tabid == 'main_options') {
            jQuery("#post_types, #post_cats").chosen();

        }
        return;
    }
    if (localStorage.getItem("masonry-settings-tab")) {
        showTab(localStorage.getItem("masonry-settings-tab"));
        return;
    }

    showTab('main_options');
}

function inArray(needle, haystack) {
    var length = haystack.length;
    for (var i = 0; i < length; i++) {
        if (haystack[i] == needle)
            return i;
    }
    return false;
}

function updateDemo(alt) {
    var head_bg, head_txtcol, cont_bg ;
    
    //Brick Style    
    jQuery('.demo-brick')
        .css('border-color', jQuery('input#page_background_color').val())
        .css('border-width', (jQuery('input#block_spacing').val() / 2));
    
    //Brick Header
    if (alt == 'alt') {
       head_bg = jQuery('input#alternate_header_background_color').val();
       head_txtcol = jQuery('input#alternate_header_font_color').val();
    } else {
       head_bg = jQuery('input#header_background_color').val();       
       head_txtcol = jQuery('input#header_font_color').val();
    }
    
    jQuery('.demo-brick .headerblock').css('background-color', jQuery('input#header_background_color').val())
        .css('background-color', head_bg)
        .css('padding', jQuery('input#header_padding').val());
     
    jQuery('.demo-brick .headerblock h2').css('font-size', jQuery('input#header_font_size').val())
        .css('color', head_txtcol);

    if (document.getElementById('header_show').checked) {
        jQuery('.demo-brick .headerblock').css('display', 'block');
    } else {
        jQuery('.demo-brick .headerblock').css('display', 'none');
    }

    if (document.getElementById('header_font_uppercase').checked) {
        jQuery('.demo-brick .headerblock h2').css('text-transform', 'uppercase');
    } else {
        jQuery('.demo-brick .headerblock h2').css('text-transform', 'none');
    }

    jQuery('.demo-brick .headerblock h3, .demo-brick .headerblock h4').css('margin-top', jQuery('input#header_spacing').val());

    jQuery('.demo-brick .headerblock h3').css('font-size', jQuery('input#header_author_font_size').val())
        .css('color', jQuery('input#header_author_font_color').val());

    jQuery('.demo-brick .headerblock h4').css('font-size', jQuery('input#header_date_font_size').val())
        .css('color', jQuery('input#header_date_font_color').val());

    //Brick content
    var txt = "The Mini is a small economy car produced by the English based British Motor Corporation (BMC) and its successors from 1959 until 2000. The original is considered an icon of 1960s British popular culture.[7][8][9][10] Its space-saving transverse engine front-wheel drive layout - allowing 80 percent of the area of the car's floorpan to be used for passengers and luggage influenced a generation of car makers.[11] In 1999 the Mini was voted the second most influential car of the 20th century";
    jQuery('.demo-brick p').text(txt.split(" ").splice(0,jQuery('input#content_show_excerpt_words').val()).join(" "));
    
    if (document.getElementById('content_use_featured').checked) {
       jQuery('.demo-brick .inner').css('background-image', 'url(/wp-content/plugins/yumjam-masonry/assets/images/mini.jpg)');
    } else {
       jQuery('.demo-brick .inner').css('background-image', '');
    }
    
    if (alt == 'alt') {
       cont_bg = jQuery('input#alternate_background_color').val();
    } else {
       cont_bg = jQuery('input#background_color').val();       
    }
    
    jQuery('.demo-brick .inner').css('min-height', jQuery('input#content_minimum_height').val())
        .css('background-color', cont_bg)
        .css('opacity', jQuery('input#background_opacity').val());
    
    jQuery('.demo-brick p').css('font-size', jQuery('input#content_font_size').val())
        .css('color', jQuery('input#content_font_color').val())
        .css('padding', jQuery('input#content_padding').val());

    if (document.getElementById('content_show_excerpt').checked) {
        jQuery('.demo-brick p').show();
    } else {
        jQuery('.demo-brick p').hide();       
    }
    
    //Brick Footer
    if (document.getElementById('content_show_readmore').checked) {
        jQuery('.demo-brick a.readmore').show();
    } else {
        jQuery('.demo-brick a.readmore').hide();       
    }
    
    if (document.getElementById('content_show_readmore_bold').checked) {
        jQuery('.demo-brick a.readmore').css('font-weight', 'bold');
    } else {
        jQuery('.demo-brick a.readmore').css('font-weight', 'normal');      
    }
    
    var val = jQuery('input#content_show_readmore_alignment:checked').val();
    if (val == 'left') {
        jQuery('.demo-brick a.readmore').css('left', '0').css('right', 'initial');
    } else {
        jQuery('.demo-brick a.readmore').css('left', 'initial').css('right', '0');
    }

    if (document.getElementById('content_over_image').checked) {
        jQuery('.inner.above .substring').css('display', 'block');
        jQuery('.inner.below .substring').css('display', 'block');
    } else {
        jQuery('.inner.above .substring').css('display', 'none');
        jQuery('.inner.below .substring').css('display', 'none');
    }

    if (jQuery("input[name='header_position']:checked").val() == 'top') {
        jQuery('.above').css('display', 'none');
        jQuery('.below').css('display', 'block');
        jQuery('.headerblock .substring').css('display', 'none');
    } else {
        jQuery('.above').css('display', 'block');
        jQuery('.below').css('display', 'none');
        jQuery('.above .readmore').css('display', 'none');
        jQuery('.above .substring').css('display', 'none');
    }

    jQuery('.demo-brick a.readmore').css('font-size', jQuery('input#content_show_readmore_font_size').val())
        .css('color', jQuery('input#content_show_readmore_font_color').val())
        .css('padding', jQuery('input#content_show_readmore_padding').val())
        .html('<i class="fa ' + jQuery('input#content_show_readmore_icon').val() +'" aria-hidden="true"></i> ' + jQuery('input#content_show_readmore_text').val())
        .css('background', jQuery('input#content_show_readmore_background').val());
}