<div id='post-<?php the_ID(); ?>' <?php post_class($classes); ?> <?php if ($cols['override']) { echo "data-override=", $cols['count']," "; } ?>>
    <?php
    //show number of columns spanned
    $this->brick_debug($cols['count'], get_post_type());
    
    if (has_post_format()) {
        $format = get_post_format();

        include 'format-'.$format.'.php';

    } else {
        include 'format-standard.php';
    }
    

    ?>
</div>