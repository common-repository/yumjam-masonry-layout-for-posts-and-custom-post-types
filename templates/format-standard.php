<?php
/**
 * Standard Post Format
 */
//build header text
if ($this->options['header_position'] == "top" ) { $header_position = "1"; }
if ($this->options['header_position'] == "bottom" ) { $header_position = "2"; }
if(!get_post_meta(get_the_ID(), 'custom_header_position', true) == "0") {
    $header_position = get_post_meta(get_the_ID(), 'custom_header_position', true);
}

$pm = get_post_meta(get_the_ID());

$custom_header_show = get_post_meta(get_the_ID(), 'custom_header_show', true);

$custom_header_background = get_post_meta(get_the_ID(), 'custom_header_background', true);
$custom_header_color = get_post_meta(get_the_ID(), 'custom_header_color', true);
$custom_header_padding = get_post_meta(get_the_ID(), 'custom_header_padding', true);
$custom_header_font_size = get_post_meta(get_the_ID(), 'custom_header_font_size', true);
$custom_header_spacing = get_post_meta(get_the_ID(), 'custom_header_spacing', true);
$custom_header_author_show = get_post_meta(get_the_ID(), 'custom_header_author_show', true);
$custom_header_author_size = get_post_meta(get_the_ID(), 'custom_header_author_size', true);
$custom_header_author_color = get_post_meta(get_the_ID(), 'custom_header_author_color', true);
$custom_header_date_show = get_post_meta(get_the_ID(), 'custom_header_date_show', true);
$custom_header_date_size = get_post_meta(get_the_ID(), 'custom_header_date_size', true);
$custom_header_date_color = get_post_meta(get_the_ID(), 'custom_header_date_color', true);

$custom_content_featured = get_post_meta(get_the_ID(), 'custom_content_featured', true);
$custom_content_show = get_post_meta(get_the_ID(), 'custom_content_show', true);
$custom_content_overlay = get_post_meta(get_the_ID(), 'custom_content_overlay', true);
$custom_content_min_height = get_post_meta(get_the_ID(), 'custom_content_min_height', true);
$custom_content_size = get_post_meta(get_the_ID(), 'custom_content_size', true);
$custom_content_color = get_post_meta(get_the_ID(), 'custom_content_color', true);
$custom_content_padding = get_post_meta(get_the_ID(), 'custom_content_padding', true);
$custom_content_background = get_post_meta(get_the_ID(), 'custom_content_background', true);
$custom_content_background_opacity = get_post_meta(get_the_ID(), 'custom_content_background_opacity', true);
$custom_content_link = get_post_meta(get_the_ID(), 'custom_content_link', true);

$custom_footer_show = get_post_meta(get_the_ID(), 'custom_footer_show', true);
$custom_footer_bold = get_post_meta(get_the_ID(), 'custcustom_footer_boldom_header_position', true);
$custom_footer_text = get_post_meta(get_the_ID(), 'custom_footer_text', true);
$custom_footer_size = get_post_meta(get_the_ID(), 'custom_footer_size', true);
$custom_footer_color = get_post_meta(get_the_ID(), 'custom_footer_color', true);
$custom_footer_background_color = get_post_meta(get_the_ID(), 'custom_footer_background_color', true);
$custom_footer_padding = get_post_meta(get_the_ID(), 'custom_footer_padding', true);
$custom_footer_alignment = get_post_meta(get_the_ID(), 'custom_footer_alignment', true);

$headerblockcss = "";
if ($custom_header_background !== '') $headerblockcss = $headerblockcss . ' background-color:'.$custom_header_background.';';
if ($custom_header_show == '1') $headerblockcss = $headerblockcss . ' display:block;';
if ($custom_header_show == '2') $headerblockcss = $headerblockcss . ' display:none;';
if ($custom_header_padding !== '') $headerblockcss = $headerblockcss . ' padding:'.$custom_header_padding.';';

$h2style = "";
if ($custom_header_color !== '') $h2style = $h2style . ' color:'.$custom_header_color.';';
if ($custom_header_font_size !== '') $h2style = $h2style . ' font-size:'.$custom_header_font_size.';';

$h3style = "";
if ($custom_header_author_show == '2') $h3style = $h3style . ' display:none;';
if ($custom_header_author_size !== '') $h3style = $h3style . ' padding:'.$custom_header_author_size.';';
if ($custom_header_author_color !== '') $h3style = $h3style . ' padding:'.$custom_header_author_color.';';

$h4style = "";
if ($custom_header_date_show == '2') $h4style = $h4style . ' display:none;';
if ($custom_header_date_size !== '') $h4style = $h4style . ' padding:'.$custom_header_date_size.';';
if ($custom_header_date_color !== '') $h4style = $h4style . ' padding:'.$custom_header_date_color.';';

if ($custom_content_link!==""){
    $postlink = $custom_content_link;
}
else
{
    $postlink = get_permalink();   
}

$trimtitle = get_the_title();
$shorttitle = wp_trim_words( $trimtitle, $num_words = intval($this->options['content_show_title_words']), $more = '… ' );

if($this->options['content_link_title'] == "on") {
    $headertext = "<a href='" . $postlink . "'><h2 style='" . $h2style . "'>" . $shorttitle ."</h2>";
}
else
{
    $headertext = "<h2 style='" . $h2style . "'>" . $shorttitle ."</h2>";    
}

if ($this->options['content_show_author'] == "on" || $custom_header_author_show == '1') {
    $headertext .= "<h3 style='" . $h3style . "'>" . get_the_author() . "</h3>";
}

if ($this->options['content_show_date'] == "on" || $custom_header_date_show == '1') {
    $headertext .= "<h4 style='" . $h4style . "'>" . get_the_date() . "</h4>";
}
if($this->options['content_link_title'] == "on") {
        $headertext .= "</a>";
}
$cssstyle = "";
if (!empty($image[0])) {
    if ($this->options['content_use_featured'] == "on" || $custom_content_featured == '1') {
        $cssstyle .= "background-image: url('" . $image[0] . "'); background-size:cover; background-position:center center;";
    }
    if ($custom_content_featured == '2') { $cssstyle = ""; }
}
$innercss = "";
if ($custom_content_min_height !== '') $innercss = $innercss . ' min-height:'.$custom_content_min_height.';';
if ($custom_content_background !== '') $innercss = $innercss . ' background-color:'.$custom_content_background.';';

$contentcss = "";
if ($custom_content_size !== '') $contentcss = $contentcss . ' font-size:'.$custom_content_size.';';
if ($custom_content_color !== '') $contentcss = $contentcss . ' color:'.$custom_content_color.';';
if ($custom_content_padding !== '') $contentcss = $contentcss . ' padding:'.$custom_content_padding.';';


$footercss ="";
if ($custom_footer_show == '1') $footercss = $footercss . ' display:block;';
if ($custom_footer_show == '2') $footercss = $footercss . ' display:none;';
if ($custom_footer_bold == '1') $footercss = $footercss . ' font-weight:bold;';
if ($custom_footer_bold == '2') $footercss = $footercss . ' font-weight:normal;';

if ($custom_footer_size !== '') $footercss = $footercss . ' font-size:'.$custom_footer_size.';';
if ($custom_footer_color !== '') $footercss = $footercss . ' color:'.$custom_footer_color.';';
if ($custom_footer_background_color !== '') $footercss = $footercss . ' background-color:'.$custom_footer_background_color.';';
if ($custom_footer_padding !== '') $footercss = $footercss . ' padding:'.$custom_footer_padding.';';
if ($custom_footer_alignment == '1') $footercss = $footercss . ' left: 0; right: inherit;';
if ($custom_footer_alignment == '2') $footercss = $footercss . ' left: inherit; right: 0;';

$readmoretext = "";
$readmoretext = $this->options['content_show_readmore_text'];

if ($custom_footer_text !== '') $readmoretext = $custom_footer_text;

$shortexcerpt = "";

if (($this->options['content_show_excerpt'] == "on" && $custom_content_show !== '2') || $custom_content_show == '1') {

    if (has_excerpt()) {
        $trimexcerpt = get_the_excerpt();
        $shortexcerpt = wp_trim_words( $trimexcerpt, $num_words = intval($this->options['content_show_excerpt_words']), $more = '… ' ); //force_balance_tags( html_entity_decode( wp_trim_words( htmlentities( wpautop(get_the_content()) ), intval($this->options['content_show_excerpt_words']), $more = '... ' ) ) );//
                
    } else {

        $shortexcerpt = wp_trim_words(get_the_content(), $num_words = intval($this->options['content_show_excerpt_words']), $more = '… ');        //force_balance_tags( html_entity_decode( wp_trim_words( htmlentities( wpautop(get_the_content()) ), intval($this->options['content_show_excerpt_words']), $more = '... ' ) ) );//
    }
}
?>
<div class='gridcontainer'>
<?php 
    if ($header_position == "1") {
        
?>  
    <div class="headerblock" style="<?php echo $headerblockcss;?>">
        <?php echo($headertext); ?>                
    </div>
<?php 
        
    } 
?>
    <div class="inner " data-imagewidth="<?php echo $image[1] ?>" data-imageheight="<?php echo $image[2] ?>" style="<?php echo $innercss?>">
        
        <?php 
            if($this->options['content_link_content'] == "on") {
                echo '<a href="' . $postlink . '">';
            }
        ?>
        <div class="inner-image<?php if (!empty($pm['content_image_is_link']) && $pm['content_image_is_link'][0] == "on") { echo ' link-image'; } ?>" style="<?php echo($cssstyle); ?>">
        <?php 
        
        if (($this->options['content_over_image'] == "on" && $custom_content_overlay !== '2') || $custom_content_overlay == '1') { ?>
            <p class='excerpt' style='<?php echo $contentcss?>'>
                <?php echo $shortexcerpt; ?>
            </p>
        <?php
        }
        
        if ($header_position == "1") {

            
            if (($this->options['content_show_readmore'] == "on" && $custom_footer_show !== '2') || $custom_footer_show == '1' && $this->options['content_link_content'] !== "on") {
                echo '<span class="readmore" style="' . $footercss . '"><i class="fa '.$this->options['content_show_readmore_icon'].'" aria-hidden="true"></i>' . $readmoretext . '</span>';
            }
            else if (($this->options['content_show_readmore'] == "on" && $custom_footer_show !== '2') || $custom_footer_show == '1') {                
                echo '<a href="' . $postlink . '"><span class="readmore" style="' . $footercss . '"><i class="fa '.$this->options['content_show_readmore_icon'].'" aria-hidden="true"></i>' . $readmoretext . '</span></a>';
            }
        }
        ?>
        </div>
        <?php 
        if($this->options['content_link_content'] == "on") {
            echo '</a>';
        }
            edit_post_link();
        
        ?>
    </div>
<?php 
    if ($header_position == "2") {
      
?>
    <div class="headerblock" style="<?php echo $headerblockcss;?>">
        <?php echo($headertext); 
        if (($this->options['content_over_image'] !== "on" && $custom_content_overlay !== '1') || $custom_content_overlay == '2') { ?>
            <p class='excerpt' style='<?php echo $contentcss?>'>
                <?php echo $shortexcerpt ?>
            </p>
        <?php
            
        }    
        if (($this->options['content_show_readmore'] == "on" && $custom_footer_show !== '2') || $custom_footer_show == '1') {                
                echo '<a href="' . $postlink . '"><span class="readmore" style="' . $footercss . '"><i class="fa '.$this->options['content_show_readmore_icon'].'" aria-hidden="true"></i>' . $readmoretext . '</span></a>';
            }
        ?>       
    </div>
<?php 
        
    } 
?>    
</div>