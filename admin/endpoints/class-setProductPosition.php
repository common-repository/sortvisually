<?php
/**
 * The setProductPosition endpoint.
 *
 * @link       https://sortvisually.com/
 * @since      1.0.0
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin
 */

/**
 * The setProductPosition endpoint.
 *
 * Class with functions to set new order of products getting
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin/endpoints
 * @author     Optalenty Ltd. <ronen@optalenty.com>
 */
class Sortvisually_setProductPosition
{

	/**
	 * Category id
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      integer    $category_id    The id of selected category.
	 */
    private $category_id;

	/**
	 * Sorter type
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    	$sorter_type    can be “category” or “brand” values for indicate which collection we need for this request.
	 */
    private $sorter_type;

	/**
	 * Products array 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    	$products    	Array of prducts id and order number
	 */
    private $products;

	/**
	 * Category url
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $category_url     Url of category
	 */
    private $category_url;

    /**
     * Run reordering
     *
	 * @since    1.0.0
	 * 
     * @param    integer       	$category_id      	Category id
     * @param    string     	$sorter_type		Sorter type
     * @param    array     		$products         	Array of prducts id and order number
     * @param    string     	$category_url       Url of category
     * 
     * @return 	 string      	Id of last product or false
     */
    public function set_order($category_id, $sorter_type, $products, $category_url) {

		if( empty($products) || $sorter_type !== 'category' ){
			return false;
		}

        $this->category_id  = $category_id;
        $this->sorter_type  = $sorter_type;
        $this->products     = $products;
        $this->category_url = $category_url;

		return $this->reorder();
    }

    /**
     * Update product position
     *
	 * @since    1.0.0
     * 
     * @return 	 integer    $result     Id of last product
     */
    private function reorder(){
        $order = array();
        $result = false;

        foreach( $this->products as $product_id => $product_order ){
            if ( FALSE != get_post_status( $product_id ) && get_post_status( $product_id ) == 'publish' ) {
                $product = array(
                    'ID'            => $product_id,
                    'menu_order'    => $product_order
                );
                $result = wp_update_post( wp_slash($product) );
            }
		}
		
        return $result;
    }
}