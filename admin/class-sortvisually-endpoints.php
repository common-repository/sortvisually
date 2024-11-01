<?php
/**
 * The API endpoints functionality of the plugin.
 *
 * @link       https://sortvisually.com/
 * @since      1.0.0
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin
 */

/**
 * The API endpoints functionality of the plugin.
 *
 * Created rest routes using WP_REST_Controller
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin
 * @author     Optalenty Ltd. <ronen@optalenty.com>
 */
class Sortvisually_Endpoints extends WP_REST_Controller
{

	/**
	 * Failed response
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $faild_response  Text of failed response
	 */
    private $faild_response;

	/**
	 * Plugin name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name     The name of this plugin.
	 */
    private $plugin_name;
    
	/**
	 * API endpoint
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $endpoint        The url of API endpoint of this plugin.
	 */
    private $endpoint;
    
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version         The current version of the plugin.
	 */
    private $version;
    
    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
     * 
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $endpoint          The API endpoint of this plugin.
	 * @param      string    $version           The current version of the plugin.
	 */
	public function __construct( $plugin_name, $endpoint, $version )
    {

		$this->plugin_name = $plugin_name;
        $this->endpoint = $endpoint;
        $this->version = $version;
        $this->faild_response = __( 'Sorry, you are not allowed to do that.', 'sortvisually' );
        
        add_action( 'rest_api_init', array( $this, 'create_endpoints' ) );
    }

	/**
	 * Register plugin rest routs for endpoints
	 *
	 * @since     1.0.0
	 */
    public function create_endpoints(){
        register_rest_route( $this->endpoint, '/api/index', 
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'endpoint_main' ),
            ) 
        );
        register_rest_route( $this->endpoint, '/api/getProductDetail', 
            array(
                'methods' => 'POST',
                'callback' => array( $this, 'endpoint_get_product_detail' ),
            ) 
        );

        register_rest_route( $this->endpoint, '/api/getCategoryTree', 
            array(
                'methods' => 'POST',
                'callback' => array( $this, 'endpoint_get_category_tree' ),
            ) 
        );

        register_rest_route( $this->endpoint, '/api/setProductPosition', 
            array(
                'methods' => 'POST',
                'callback' => array( $this, 'endpoint_set_products_position' ),
            ) 
        );
    }

	/**
	 * Callback function on /api/index endpoint
	 *
	 * @since     1.0.0
     * 
     * @param     string    $data    Data params from app
     * 
	 * @return    string    The version number of the plugin.
	 */
    public function endpoint_main( $data ){
        

        return new WP_REST_Response( $this->version, 200 );
    }

	/**
	 * Callback function on /api/getProductDetail endpoint
	 *
	 * @since     1.0.0
     * 
     * @param     string    $data    Data params from app
     * 
	 * @return    object    WP_REST_Response Response with list of product from class Sortvisually_getProductDetail();
	 */
    public function endpoint_get_product_detail( $data ){
        $data = $this->get_data_from_callback($data);

        $user           = $data->user;
        $token          = $data->token;

        // Check if tocken and user_id from $data matches with data from pluin settings
        if( !$this->check_authorization_data($user, $token) ){
            return new WP_REST_Response( $this->faild_response, 401 );
        }

        $category_id    = $data->category_id;
        $sorter_type    = $data->sorter_type;
        $page           = $data->page;
        $total_pages    = $data->total_pages;
        
        $Sortvisually_getProductDetail = new Sortvisually_getProductDetail();
        $response = $Sortvisually_getProductDetail->get_data($category_id, $sorter_type, $page, $total_pages);

        return new WP_REST_Response( $response, 200 );
    }

	/**
	 * Callback function on /api/getCategoryTree endpoint
	 *
	 * @since     1.0.0
     * 
     * @param     string    $data    Data params from app
     * 
	 * @return    object    WP_REST_Response Response with list of categories from class Sortvisually_getCategoryTree();
	 */
    public function endpoint_get_category_tree( $data ){
        $data = $this->get_data_from_callback($data);

        $user           = $data->user;
        $token          = $data->token;

        // Check if tocken and user_id from $data matches with data from pluin settings
        if( !$this->check_authorization_data($user, $token) ){
            return new WP_REST_Response( $this->faild_response, 401 );
        }

        $Sortvisually_getCategoryTree = new Sortvisually_getCategoryTree();
        $response = $Sortvisually_getCategoryTree->get_data();
        return new WP_REST_Response( $response, 200 );
    }

	/**
	 * Callback function on /api/setProductPosition endpoint
	 *
	 * @since     1.0.0
     * 
     * @param     string    $data    Data params from app
     * 
	 * @return    string    WP_REST_Response Response with result of setting new position from class Sortvisually_setProductPosition();
	 */
    public function endpoint_set_products_position($data){
        $data = $this->get_data_from_callback($data);

        $user           = $data->user;
        $token          = $data->token;

        // Check if tocken and user_id from $data matches with data from pluin settings
        if( !$this->check_authorization_data($user, $token) ){
            return new WP_REST_Response( $this->faild_response, 401 );
        }

        $category_id    = $data->category_id;
        $sorter_type    = $data->sorter_type;
        $products       = $data->products;
        $category_url   = $data->category_url;
        
        $Sortvisually_setProductPosition = new Sortvisually_setProductPosition();
        $response = $Sortvisually_setProductPosition->set_order($category_id, $sorter_type, $products, $category_url);

        return new WP_REST_Response( $response, 200 );
    }

	/**
	 * Get and decode data from request
	 *
	 * @since     1.0.0
	 * @access    private
     * 
     * @param     string    $data    Data params from app
     * 
	 * @return    object    $data    Decoded params from request
	 */
    private function get_data_from_callback($data){
        $data = $data->get_params();
        $data = json_decode($data['data']);

        return $data;
    }

	/**
	 * Compare authorization data from request with data from plugin settings
	 *
	 * @since     1.0.0
	 * @access    private
     * 
     * @param     string    $user       User id param from request
     * @param     string    $token      Tocken param from request
     * 
	 * @return    boolean   Returned true or false
	 */
    private function check_authorization_data($user, $token) {
        $authorization_data_settings = Sortvisually_Settings_Page::get_settings();

        if( $user === $authorization_data_settings['user_API'] && $token === $authorization_data_settings['token_api'] ){
            return true;
        }
        else {
            return false;
        }
    }
}