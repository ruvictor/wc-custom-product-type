<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */


class WC_Product_Membersonly extends WC_Product_Simple {

    // Return the product type
    public function get_type() {
        return 'membersonly';
    }

    // show the mermbers only price if user is logged in
        public function get_price( $context = 'view' ) {

        if ( is_user_logged_in() ) {
            $price = $this->get_meta( '_membersonly_price', true );
            if ( is_numeric( $price ) ) {
                return $price;
            }
        
        }
        return $this->get_prop( 'price', $context );
        }
}




// add the product type to the dropdown
function add_type_to_dropdown( $types ) {
    $types['membersonly'] = __( 'Members Only', 'vicodemedia' );
   
    return $types;
}
add_filter( 'product_type_selector', 'add_type_to_dropdown');






// add the product type as a taxonomy
function install_taxonomy() {
    // If there is no membersonly product type taxonomy, add it.
    if ( ! get_term_by( 'slug', 'membersonly', 'product_type' ) ) {
        wp_insert_term( 'membersonly', 'product_type' );
    }
}
register_activation_hook( __FILE__, 'install_taxonomy');




// add advanced pricing
function add_membersonly_field() {
    global $product_object;
    ?>
    <div class='options_group show_if_membersonly'>
        <?php

        woocommerce_wp_text_input(
            array(
                'id'          => '_membersonly_price',
                'label'       => __( 'Price for members', 'vicodemedia' ),
                'value'       => $product_object->get_meta( '_membersonly_price', true ),
                'default'     => '',
                'placeholder' => 'Enter Price',
                'data_type' => 'price',
            )
        );
        ?>
    </div>
     
<?php
}
add_action( 'woocommerce_product_options_pricing', 'add_membersonly_field');




// Generl Tab not showing up
add_action( 'woocommerce_product_options_general_product_data', function(){
    echo '<div class="options_group show_if_membersonly clear"></div>';
} );




// add show_if_advanced calass to options_group
function enable_product_js() {
    global $post, $product_object;

    if ( ! $post ) { return; }

    if ( 'product' != $post->post_type ) :
  return;
    endif;

    $is_membersonly = $product_object && 'membersonly' === $product_object->get_type() ? true : false;

    ?>
    <script type='text/javascript'>
    jQuery(document).ready(function () {
    //for Price tab
    jQuery('#general_product_data .pricing').addClass('show_if_membersonly');

    <?php if ( $is_membersonly ) { ?>
        jQuery('#general_product_data .pricing').show();
    <?php } ?>
    });
    </script>
    <?php
}
add_action( 'admin_footer', 'enable_product_js');





// save data on submission
function save_membersonly_price( $post_id ) {
    $price = isset( $_POST['_membersonly_price'] ) ? sanitize_text_field( $_POST['_membersonly_price'] ) : '';
    update_post_meta( $post_id, '_membersonly_price', $price );
}
add_action( 'woocommerce_process_product_meta_membersonly', 'save_membersonly_price');




// display add to cart button
function add_cart_button() {
    global $product;
    $id = $product->get_id();
    if(WC_Product_Factory::get_product_type($id) == 'membersonly')
    echo '
        <form class="cart" action="" method="post" enctype="multipart/form-data">
            <div class="quantity">
                <label class="screen-reader-text" for="quantity_5fe8134674b0d">Test quantity</label>
                <input type="number" id="quantity_5fe8134674b0d" class="input-text qty text" step="1" min="1" max="" name="quantity" value="1" title="Qty" size="4" placeholder="" inputmode="numeric">
            </div>
            <button type="submit" name="add-to-cart" value="'.$id.'" class="single_add_to_cart_button button alt">Add to cart</button>
        </form>
    ';
}
add_action( 'woocommerce_single_product_summary', 'add_cart_button', 15 );