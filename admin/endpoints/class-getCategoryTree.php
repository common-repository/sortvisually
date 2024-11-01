<?php
/**
 * The getCategoryTree endpoint.
 *
 * @link       https://sortvisually.com/
 * @since      1.0.0
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin
 */

/**
 * The getCategoryTree endpoint.
 *
 * Class with functions to get list of categories 
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin/endpoints
 * @author     Optalenty Ltd. <ronen@optalenty.com>
 */
class Sortvisually_getCategoryTree
{

    /**
     * Retrieve array of categories
     *
	 * @since    1.0.0
     * 
     * @return array    $categories     Array of categories
     */
    public function get_data() {
        $categories = $this->get_taxonomy_hierarchy_multiple();
        
        return $categories;
    }

    /**
     * Prepare hierarchy categories
     *
	 * @since    1.0.0
	 * @access   private
     * 
     * @param    array       $taxonomy      Taxonomy slugs
     * @param    integer     $parent        Parent term id
     * @param    integer     $level         Level of category
     * 
     * @return   array       $categories    Array of category data
     */
    private function get_taxonomy_hierarchy( $taxonomy, $parent = 0, $level) {
        $categories = array();
        $level++;

        $taxonomy = is_array( $taxonomy) ? array_shift( $taxonomy): $taxonomy;
        $terms = get_terms( $taxonomy, array( 'parent'=> $parent, 'hide_empty'=> true));

        foreach ( $terms as $term) {
            $term->children = $this->get_taxonomy_hierarchy( $taxonomy, $term->term_id, $level );
            $categories[] = $this->categories_response($term, $level);
        }

        return $categories;
    }
    
    /**
     * Get hierarchy categories inside default category
     *
	 * @since    1.0.0
	 * @access   private
     *
     * @return   array      $categories     Array of categories
     */
    private function get_taxonomy_hierarchy_multiple() {
        $level = 1;
        $parent = 0;
        $categories = array(
            'id'                => '0',
            'name'              => __('Default Category', 'sortvisually'),
            'url'               => '#',
            'url_key'           => 'default',
            'level'             => '1',
            'type'              => 'category'
        );

        $terms = $this->get_taxonomy_hierarchy( 'product_cat', $parent, $level );
        if ( $terms ) {
            $categories['children'] = $terms;
        }


        return $categories;
    }

    /**
     * Prepare category data for object
     *
	 * @since    1.0.0
	 * @access   private
     * 
     * @param    object      $category           Category object with data
     * @param    integer     $level              Level of category
     *
     * @return   array       $category_data      Prepared array of category data
     */
    private function categories_response($category, $level){
        global $woocommerce;

        $category_data = array(
            'id'                => (string) $category->term_id,
            'name'              => $category->name,
            'url'               => get_category_link( $category->term_id ),
            'url_key'           => $category->slug,
            'level'             => (string) $level,
            'type'              => 'category',
            
        );

        if($category->children){
            $category_data['children'] = $category->children;
        }
        
        return $category_data;
    }
}