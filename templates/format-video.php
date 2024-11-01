<?php
/**
 * Video Post Format
 */
global $post;

$url = '';

if (strpos($post->post_content, "[video") > -1) {
    //has embedded video content, get url
    $url_start = strpos($post->post_content, 'mp4="');
    if ( empty($url_start) ) {
        $url_start = strpos($post->post_content, 'webm="') + 6;
        $video = 'webm';
    } else {
        $url_start += 5;
        $video = 'mp4';
    }
    
    $url_len = strpos($post->post_content, '"', $url_start) - $url_start;
    $url = substr($post->post_content, $url_start, $url_len);
    
    /*
    $width_start = strpos($post->post_content, 'width="') + 7;
    $width_len = strpos($post->post_content, '"', $width_start) - $width_start;
    $width = substr($post->post_content, $width_start, $width_len);
    
    $height_start = strpos($post->post_content, 'height="') + 8;
    $height_len = strpos($post->post_content, '"', $height_start) - $height_start;
    $height = substr($post->post_content, $height_start, $height_len);
     */
}
?>

<div class='gridcontainer fullscreen-video'>
    <video loop muted autoplay poster="" class="fullscreen-bg__video">
        <source src="<?php echo $url ?>" type="video/<?php echo $video ?>">
    </video>    
    <?php edit_post_link();?>
</div>

