<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$current_user = wp_get_current_user();

?>
<div id="yumjam-masonry-options" class="wrap">
    <h2>YumJam Flexible Masonry Shortcode Builder</h2>
    <textarea rows="5" cols="90" id="shortcode_built">[yumjam-masonry]</textarea>

    <h2 class="nav-tab-wrapper">
        <a href="#" id="main_options" class="nav-tab nav-tab-active">Main Layout</a>
        <a href="#" id="postload_options" class="nav-tab nav-tab-active">Post Loading</a>
        <a href="#" id="brick_header" class="nav-tab">Brick Header</a>
        <a href="#" id="brick_content" class="nav-tab">Brick Content</a>
        <a href="#" id="brick_footer" class="nav-tab">Brick Footer</a>
    </h2> 

    <p class="demo">
        <button type="button" name="update_sc" id="update_sc" class="button button-primary">Build Shortcode</button>
        <button type="button" name="import_sc" id="import_sc" class="button button-primary">Import</button>
        <button type="button" name="demo" id="demo" class="button button-primary">Preview</button>
        <button type="button" name="demo_alt" id="demo_alt" class="button button-primary">Alt. Preview</button>
        <button type="button" name="reset" id="reset" class="button button">Reset</button>
    </p>

    <form method="post" action="options.php" id="yj_sc_builder" style="height: 600px; overflow: scroll;">
        <table class="form-table">
            <tbody>
                <?php
                foreach ($this->settings as $key => $value) {
                    if ($value['type'] == 'html') {
                    ?>
                    <tr><td colspan="2"><strong style="font-size: 1.2em"><?php echo $value['value']?></strong><hr class='tab-<?php echo $value['tab'] ?>' /></td></tr>                        
                    <?php
                    } else {
                    ?>
                    <tr style="display: table-row;">
                        <th scope="row"><?php echo $value['name'] ?></th>
                        <td>               
                            <?php
                            $this->yj_output_settings_field($value);
                            ?>
                        </td>
                    </tr>       
                    <?php
                    }
                }
                ?>
                </form>
            </tbody>
        </table>
    </form>

    <div class="demo-brick">
        <div class='brick-debug' title="Post Type: custom" data-cols="4">4</div>
        <div class="gridcontainer">
            <div class="inner above" style="background-image: url(&quot;<?php echo YJ_MASONRY_PLUGIN_URL .'assets/images/mini.jpg'; ?>&quot;); background-size: cover; background-position: center center; height:255px; display:none;">
                <p class="substring">
                    The Mini is a small economy car produced by the English based British Motor Corporation (BMC) and its successors from 1959 until 2000. The original is considered an icon of 1960s British popular culture.[7][8][9][10] Its space-saving transverse engine front-wheel drive layout - allowing 80 percent of the area of the car's floorpan to be used for passengers and luggage - influenced a generation of car makers.[11] In 1999 the Mini was voted the second most influential car of the 20th century                    
                </p>
                <a href="#" class="readmore"><i class="fa fa-eye" aria-hidden="true"></i> READ MORE</a>     
            </div>
            <div class="headerblock">
                <a href="#">
                    <h2>BMW finally build a proper mini</h2>
                    <h3><?php echo $current_user->display_name ?></h3>
                    <h4><?php echo date('jS F Y'); ?></h4>
                </a>   
                <p class="substring">
                    The Mini is a small economy car produced by the English based British Motor Corporation (BMC) and its successors from 1959 until 2000. The original is considered an icon of 1960s British popular culture.[7][8][9][10] Its space-saving transverse engine front-wheel drive layout - allowing 80 percent of the area of the car's floorpan to be used for passengers and luggage - influenced a generation of car makers.[11] In 1999 the Mini was voted the second most influential car of the 20th century                    
                </p>     
                <a href="#" class="readmore"><i class="fa fa-eye" aria-hidden="true"></i> READ MORE</a>           
            </div>
            <div class="inner below" style="background-image: url(&quot;<?php echo YJ_MASONRY_PLUGIN_URL .'assets/images/mini.jpg'; ?>&quot;); background-size: cover; background-position: center center; height:255px">
                <p class="substring">
                    The Mini is a small economy car produced by the English based British Motor Corporation (BMC) and its successors from 1959 until 2000. The original is considered an icon of 1960s British popular culture.[7][8][9][10] Its space-saving transverse engine front-wheel drive layout - allowing 80 percent of the area of the car's floorpan to be used for passengers and luggage - influenced a generation of car makers.[11] In 1999 the Mini was voted the second most influential car of the 20th century                    
                </p>
                <a href="#" class="readmore"><i class="fa fa-eye" aria-hidden="true"></i> READ MORE</a>     
            </div>
        </div>
    </div>

</div>