<?php
/**
 * The getProductDetail endpoint.
 *
 * @link       https://sortvisually.com/
 * @since      1.0.0
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin
 */

/**
 * The getProductDetail endpoint.
 *
 * Class with functions to get product details
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin/endpoints
 * @author     Optalenty Ltd. <ronen@optalenty.com>
 */
class Sortvisually_getProductDetail
{

	/**
	 * Category id
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $category_id    The id of selected category from app.
	 */
    private $category_id;

	/**
	 * Sorter type
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $sorter_type    can be “category” or “brand” values for indicate which collection we need for this request.
	 */
    private $sorter_type;

	/**
	 * Page number
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string     $page               The page number for requested category
	 */
    private $page;

	/**
	 * Total pages
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      integer    $total_pages        Total pages of products
	 */
    private $total_pages;

	/**
	 * Requested pages
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string     $requested_pages    The number of pages of products required to return
	 */
    private $requested_pages;

	/**
	 * Retrieve array of products
	 *
	 * @since    1.0.0

     * @param    string     $category_id        The id of selected category from app
     * @param    string     $sorter_type        Sorter type
     * @param    integer    $page               The page number for requested category
     * @param    string     $requested_pages    The number of pages of products required to return
     * 
     * @return   array      $categories         Array of categories
	 */
    public function get_data($category_id, $sorter_type, $page, $requested_pages) {

        $this->category_id      = $category_id;
        $this->sorter_type      = $sorter_type;
        $this->page             = (int) $page;
        $this->requested_pages  = $requested_pages;

        return $this->get_products();
    }

	/**
	 * Get products
	 *
	 * @since    1.0.0
	 * @access   private
     * 
     * @return   array      $responce      Prepared array of products
	 */
    private function get_products(){
        $responce = array();

        $get_pages = (int) $this->requested_pages + $this->page;
        $meta_query = array();
        
        for ( $i = $this->page; $i < $get_pages; $i++ ) { 
            $args = array(
                'post_type'             => 'product',
                'post_status'           => 'publish',
                'orderby'               => 'menu_order',
                'order'                 => 'ASC',
                'posts_per_page'        => 12,
                'paged'                 => $i,
                'tax_query'             => array(
                    array(
                        'taxonomy'      => 'product_cat',
                        'field'         => 'term_id',
                        'terms'         => $this->category_id,
                        'operator'      => 'IN'
                    ),
                    array(
                        'taxonomy'      => 'product_visibility',
                        'field'         => 'slug',
                        'terms'         => 'exclude-from-catalog',
                        'operator'      => 'NOT IN'
                    )
                )
            );
            
            if( get_option( 'woocommerce_hide_out_of_stock_items', true ) == 'yes') {
                $meta_query[] = array(
                    'key' => '_stock_status',
                    'value'   => 'outofstock',
                    'compare' => '!=',
                );
            }

            if( !Sortvisually_Settings_Page::get_settings()['without_images'] ){
                $meta_query[] = array(
                    'key' => '_thumbnail_id',
                    'compare' => 'EXISTS'
                );
            }

            if( !empty( $meta_query ) ) {
                $args['meta_query'] = $meta_query;
            }

            $products = new WP_Query( $args );

            if( !$this->total_pages ){
                $this->total_pages  = $products->max_num_pages;
            }
            
            $products = $this->products_response( $products->posts );
            $responce[] = array(
                'page'          => $i,
                'total_pages'   => (int) $this->total_pages,
                'products'      => $products
            );
        }

        return $responce;
    }

    /**
     * Prepare products data for object
     *
	 * @since    1.0.0
	 * @access   private
     * 
     * @param    array      $products           Default products array
     *
     * @return   array      $products_data      Prepared array of products
     */
    private function products_response( $products ){
        global $woocommerce;

        $products_data = array();

        foreach( $products as $product ){
            $wc_product = wc_get_product( $product->ID );
            $wc_product_image_id  = $wc_product->get_image_id();
            $wc_product_image_url = wp_get_attachment_image_url( $wc_product_image_id, 'full' );

            $product_data = array(
                'id'                => (string) $wc_product->get_id(),
                'sku'               => $wc_product->get_sku(),
                'name'              => $wc_product->get_name(),
                'url'               => get_permalink( $wc_product->get_id() ),
                'image'             => $wc_product_image_url,
                'price'             => $wc_product->get_regular_price(),
                'special_price'     => ( $wc_product->get_sale_price() )? $wc_product->get_sale_price(): 0,
                'qty'               => ( $wc_product->get_stock_quantity() )? $wc_product->get_stock_quantity(): 0,
            );
			
            $currency_symbol = html_entity_decode( get_woocommerce_currency_symbol() );
            
            if ( $wc_product->is_type( 'variable' ) ) {
                $variable_product = new WC_Product_Variable( $wc_product->get_id() );
                $qty_view = $this->get_product_variations( $variable_product );
				$prices = $variable_product->get_variation_prices( false );
				$regular_prices = $prices['regular_price'];
				$special_prices = $prices['sale_price'];
				

                if( empty( $regular_prices ) && empty( $special_prices ) ){
                    $special_price = __('Out of stock', 'sortvisually');
                    $regular_price = __('Out of stock', 'sortvisually');
					$currency_symbol = '';
                }
                else {
                    $range = false;
                    $range_price = array();
                    $regular_price = max( $regular_prices );
                    
                    if( min( $regular_prices ) !== max( $regular_prices ) ){
                        $range = true;
                        $range_price['min_regular_prices'] = min( $regular_prices );
                        $range_price['max_regular_prices'] = max( $regular_prices );
                    }
                    
                    $special_price = max($special_prices);
                    
                    if( min( $special_prices ) !== max( $special_prices ) ){
                        $range = true;
                        $range_price['min_special_prices'] = min( $special_prices );
                        $range_price['max_special_prices'] = max( $special_prices );
                    }
                    
                    if( $range && !empty( $range_price ) ) {
                        $special_price = '';
                        $regular_price = min( $range_price ) . ' - ' . max( $range_price );
                    }
                        
                }
				

                $product_data['price']                  = $regular_price;
                $product_data['special_price']          = $special_price;
                $product_data['currency_symbol']        = $currency_symbol;
                $product_data['qty_view']               = $qty_view;
                $product_data['total_childs_in_stock']  = count( $variable_product->get_children() );
                $product_data['qty']                    = $this->wc_get_variable_product_stock_quantity( $variable_product );
            }
            
            $product_data['additional_attributes'] = $this->get_additional_attributes( $wc_product) ;

            $min_item_to_be_in_stock = ( $wc_product->get_stock_quantity() )? Sortvisually_Settings_Page::get_settings()['min_item']: 0;
            if( intval( $min_item_to_be_in_stock == 0 ) || intval( $min_item_to_be_in_stock ) <= $wc_product->get_stock_quantity() ){
                $products_data[] = $product_data;
            }
        }

        return $products_data;
    }

	/**
	 * Get data from product variation
	 *
	 * @since    1.0.0
	 * @access   private
     * 
     * @param    object     $variable_product   Object of product variation
     * 
     * @return   array      $products_data      Prepared array of product variation
	 */
    private function get_product_variations( $variable_product ){
        $variations_arr = array();
        $variations = $variable_product->get_children();

        foreach ( $variations as $value ) {
            $single_variation = new WC_Product_Variation( $value );
            $variations_arr[$single_variation->get_id()] = array(
                'label'     => urldecode( implode( " => ", $single_variation->get_variation_attributes() ) ) . ' => ',
                'qty'       => ( $single_variation->get_stock_quantity() )? $single_variation->get_stock_quantity(): '',
                'in_stock'  => ( $single_variation->get_manage_stock() === true )? $single_variation->get_manage_stock(): false
            );
        }

        return $variations_arr;
    }

	/**
	 * Get array of product by additional attributs from settings page of plugin
	 *
	 * @since    1.0.0
	 * @access   private
     * 
     * @param    object     $wc_product                 Object of product
     * 
     * @return   array      $additional_attributes      Prepared array of additional attributs product
	 */
    private function get_additional_attributes( $wc_product ){
        $additional_attributes = array();
        $additional_attributes_settings = Sortvisually_Settings_Page::get_settings( 'additional_attributes' );

        if( array_key_exists( 'additional_product_data', $additional_attributes_settings ) ){
            foreach( $additional_attributes_settings['additional_product_data'] as $additional_product_data ){
                switch ( $additional_product_data ) {
                    case 'sku':
                        $additional_attributes['sku'] = $wc_product->get_sku();
                        break;
                }
            }
        }
        foreach( $additional_attributes_settings['additional_attributes'] as $attribute ) {
            if( $wc_product->get_attribute( $attribute ) ){
                $additional_attributes[$attribute] = $wc_product->get_attribute( $attribute );
            }
        }

        return $additional_attributes;
    }

	/**
	 * Get total stock quantity from variations
	 *
	 * @since    1.0.0
	 * @access   private
     * 
     * @param    object     $product            Object of product
     * 
     * @return   integer    $stock_quantity     Summ of total quantity from all variations
	 */
    private function wc_get_variable_product_stock_quantity( $variable_product ){
        $variations_arr = array();
        $variations = $variable_product->get_children();
        $stock_quantity = 0;
        
        if( $variations ){
    
            foreach ( $variations as $value ) {
                $single_variation = new WC_Product_Variation( $value );
                $stock_quantity = ( $single_variation->get_stock_quantity() )? $stock_quantity + $single_variation->get_stock_quantity(): $stock_quantity;
            }
    
            return $stock_quantity;
        }
    }
}