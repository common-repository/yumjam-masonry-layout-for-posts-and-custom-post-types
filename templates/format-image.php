<?php
/**
 * Image Post Format
 */


$custom_content_featured = get_post_meta(get_the_ID(), 'custom_content_featured', true);
$custom_content_min_height = get_post_meta(get_the_ID(), 'custom_content_min_height', true);
$custom_content_background = get_post_meta(get_the_ID(), 'custom_content_background', true);

if (!empty($image[0])) {
    if ($this->options['content_use_featured'] == "on" || $custom_content_featured == '1') {
        $cssstyle .= "background-image: url('" . $image[0] . "'); background-size:cover; background-position:center center;";
    }
    if ($custom_content_featured == '2') { $cssstyle = ""; }
}
$cssstyle = "";
if (!empty($image[0])) {
    if ($this->options['content_use_featured'] == "on" || $custom_content_featured == '1') {
        $cssstyle .= "background-image: url('" . $image[0] . "'); background-size:cover; background-position:center center;";
    }
    if ($custom_content_featured == '2') { $cssstyle = ""; }
}
if ($custom_content_background !== '') $cssstyle = $cssstyle . ' background-color:'.$custom_content_background.';';
$innercss = "";
if ($custom_content_min_height !== '') $innercss = $innercss . ' min-height:'.$custom_content_min_height.';';
$innercss = $innercss . ' background-color:transparent !important;';
?>
<div class='gridcontainer image' style="<?php echo($cssstyle); ?> " data-imagewidth="<?php echo $image[1] ?>" data-imageheight="<?php echo $image[2] ?>">
	<div class="inner" data-imagewidth="<?php echo $image[1] ?>" data-imageheight="<?php echo $image[2] ?>" style="height:100%; <?php echo $innercss?>">
		<div class="inner-image"  style="position:relative; <?php echo($cssstyle); ?>">
			<?php edit_post_link();?>
		</div>
	</div>
</div>

