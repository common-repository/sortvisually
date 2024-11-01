<?php
/**
 * The settings page of the plugin.
 *
 * @link       https://sortvisually.com/
 * @since      1.0.0
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin
 */

/**
 * The settings page of the plugin.
 *
 * Defines the field wich helps to configure plugin and connect to Editea app
 *
 * @package    Sortvisually
 * @subpackage Sortvisually/admin
 * @author     Optalenty Ltd. <ronen@optalenty.com>
 */
class Sortvisually_Settings_Page
{
    /**
     * Holds the values to be used in the fields callbacks
     * 
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    The array with field value from WP option.
	 */
    private $options;

	/**
	 * Plugin settings page slug
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $slug    The slug of this plugin.
	 */
    private $slug;
    
	/**
	 * API endpoint
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $endpoint        The url of API endpoint of this plugin.
	 */
    private $endpoint;
    
    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
     * 
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $endpoint          The API endpoint of this plugin.
	 */
	public function __construct( $plugin_name, $endpoint )
    {

		$this->slug = $plugin_name;
        $this->endpoint = $endpoint;

        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_filter( "plugin_action_links_$this->slug/$this->slug.php", array( $this, 'sortvisually_plugin_settings_link' ) );
		add_filter('woocommerce_default_catalog_orderby', array( $this, 'set_default_catalog_orderby' ) );
    }


	/**
	 * Set default catalog sorting to show new ordering from app
	 *
	 * @since     1.0.0
     * 
	 * @return    string    Option value for default ordering
	 */
	public function set_default_catalog_orderby() {
		 return 'menu_order'; 
	}
	
	/**
	 * Add plugin seeting link on Plugins page
	 *
	 * @since     1.0.0
     * 
     * @param     array    $links   Array of links
     * 
	 * @return    array    $links   Updated array of links
	 */
    public function sortvisually_plugin_settings_link($links) { 
        $settings_link = '<a href="options-general.php?page=' . $this->slug . '">' . __('Settings', 'sortvisually') . '</a>'; 
        array_unshift($links, $settings_link); 
        return $links; 
    }
	/**
	 * Register a plugin settings page.
	 *
	 * @since    1.0.0
	 */
    public function add_plugin_page()
    {
        add_options_page(
			__( 'Sortvisually Settings', 'sortvisually' ),
			__( 'Sortvisually', 'sortvisually' ),
            'manage_options', 
            $this->slug,
            array( $this, 'create_settings_page' )
        );
    }

    /**
     * Create settings page
	 *
	 * @since     1.0.0
	 */
    public function create_settings_page()
    {
        $this->options = $this->get_settings_options();
        ?>
        <div class="sortvisually_settings wrap">
            <h1><?php _e( 'Sortvisually Settings', 'sortvisually' ); ?></h1>
            <form method="post" action="options.php">
            <?php
                settings_fields( 'sortvisually_options_group' );
                do_settings_sections( $this->slug );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
	 * 
	 * @since    1.0.0
     */
    public function page_init()
    {        
        register_setting(
            'sortvisually_options_group',
            'sortvisually_settings',
            array( $this, 'sanitize' )
        );

        add_settings_section(
            'setting_section_id',
            __('API Details', 'sortvisually'),
            array( $this, 'print_section_info' ),
            $this->slug
        );  

        add_settings_field(
            'request_url',
            'Request URL',
            array( $this, 'request_url_callback' ),
            $this->slug,
            'setting_section_id',
            array(
                'description' => __('Copy this field to your Editea web app for integration', 'sortvisually')
            )
        );  
		
        add_settings_field(
            'user_API',
            'User API',
            array( $this, 'field_callback' ),
            $this->slug,
			'setting_section_id',
			array( 
				'type' => 'text',
                'id' => 'user_API',
                'description' => __('Set in this field your Editea User API for integration', 'sortvisually')
			)
        );  

        add_settings_field(
            'token_api',
            'Token API',
            array( $this, 'field_callback' ),
            $this->slug,
            'setting_section_id',
			array( 
				'type' => 'text',
				'id' => 'token_api',
                'description' => __('Set in this field your Editea Token API key for integration', 'sortvisually')
			)   
        );   

        add_settings_field(
            'min_item',
            'Min Item To Be In Stock',
            array( $this, 'field_callback' ),
            $this->slug,
            'setting_section_id',
			array( 
				'type' => 'number',
				'id' => 'min_item',
                'description' => __('Set in this field your min qty to decide if item is in stock, if qty is bigger or equal then this field the item will show as in stock else if the qty will be below, it will as out of stock, leave empty for default 0', 'sortvisually'),
				'min' => 0
			)       
        );   

        add_settings_field(
            'clear_cache',
            'Clear Cache After Sort',
            array( $this, 'field_callback' ),
            $this->slug,
            'setting_section_id',
			array( 
				'type' => 'checkbox',
				'id' => 'clear_cache',
                'description' => __('Check or uncheck for clearing the cache automatically after your sort', 'sortvisually')
			)    
		);   
		
        add_settings_field(
            'without_images',
            'Display product without images',
            array( $this, 'field_callback' ),
            $this->slug,
            'setting_section_id',
			array( 
				'type' => 'checkbox',
				'id' => 'without_images',
                'default' => 'checked',
                'description' => __('Send product without images to sortvisual app', 'sortvisually')
			)    
		);   

        add_settings_field(
            'additional_product_data',
            'Additional Product Data',
            array( $this, 'field_callback' ),
            $this->slug,
            'setting_section_id',
			array( 
				'type' => 'select',
                'id' => 'additional_product_data',
                'input_class' => 'select2',
				'options' => array(
					'sku' => 'SKU'
                ),
                'multiple' => true,
                'description' => __('Select your product data for view on product list', 'sortvisually')
			) 
        );  
        
        add_settings_field(
            'additional_attributes',
            'Additional Attributes',
            array( $this, 'field_callback' ),
            $this->slug,
            'setting_section_id',
			array( 
				'type' => 'select',
				'id' => 'additional_attributes',
                'input_class' => 'select2',
                'options' => $this->attributes_list(),
                'multiple' => true,
                'description' => __('Select your additional attributes for view on product list', 'sortvisually')
			) 
        );   
    }

    /**
     * Sanitize each setting field as needed
     *
	 * @since     1.0.0
     * 
     * @param     array     $input Contains all settings fields as array keys
     * 
	 * @return    array     $input Updated field after sanitize
	 */
    public function sanitize( $input )
    {
        if( isset( $input['user_API'] ) && $input['user_API'] < 0 )
            $input['user_API'] =  0 ;
        return $input;
    }

    /** 
     * Print the Section text
	 * 
	 * @since     1.0.0
	 */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
	 * 
	 * @since       1.0.0
     * 
     * @param       array       $args Contains all custom arguments of field
     * 
     * @return      string      $html Compiled html field
	 */
    public function field_callback($args)
    {
        $html = '';
        $value = isset( $this->options[$args['id']] ) ? $this->options[$args['id']] : '';
        switch ($args['type']) {
            case 'select':
                $html = '<div class="input-field">';
                if($args['multiple']){
                    $html .= '<select class="' . $args['input_class'] . '" id="' . $args['id'] . '" name="sortvisually_settings[' . $args['id'] . '][]" multiple>';
                    foreach($args['options'] as $option_value => $option_name) {
                        if($value)
                            $selected = (in_array($option_value, $value))? 'selected': '';
                            
                        $html .= '<option value="' . $option_value . '"' . $selected . '>' . $option_name . '</option>';
                    }
                }
                else {
                    $html .= '<select class="' . $args['input_class'] . '" id="' . $args['id'] . '" name="sortvisually_settings[' . $args['id'] . ']">';
                    foreach($args['options'] as $option_value => $option_name) {
                        $selected = ($value == $option_value)? 'selected': '';
                        $html .= '<option value="' . $option_value . '"' . $selected . '>' . $option_name . '</option>';
                    }
                }
                
                $html .= '</select>';
                $html .= '</div>';
                break;
            case 'checkbox':
                $checked = ( isset( $this->options[$args['id']] ) || ( !isset( $this->options[$args['id']] ) && $args['default'] == 'checked' ) ) ? 'checked' : '';
                $html = '<div class="input-field">';
                $html .= '<input type="' . $args['type'] . '" class="' . $args['input_class'] . '" id="' . $args['id'] . '" name="sortvisually_settings[' . $args['id'] . ']" value="1" ' . $checked . ' />';
                break;
            
            default:
                $min = ($args['type'] == 'number' && $args['min'] !== '')? 'min="' . $args['min'] . '"': '';
                $html = '<div class="input-field">';
                $html .= '<input type="' . $args['type'] . '" class="' . $args['input_class'] . '" id="' . $args['id'] . '" name="sortvisually_settings[' . $args['id'] . ']" value="' . $value . '" ' . $min . ' />';
                $html .= '</div>';
                break;
        }

        $html .= '<div class="description">' . $args['description'] . '</div>';

        $allowed_html = array(
            'div'      => array(
                'class'     => array(),
            ),
            'select'   => array(
                'class'     => array(),
                'id'        => array(),
                'name'      => array(),
                'multiple'  => array(),
            ),
            'option'   => array(
                'value'     => array(),
                'selected'  => array(),
            ),
            'input'    => array(
                'type'      => array(),
                'class'     => array(),
                'id'        => array(),
                'value'     => array(),
                'min'       => array(),
                'checked'   => array(),
            ),
        );
        echo wp_kses( $html, $allowed_html );
	}
	
    /** 
     * Get the list of attributes
	 *
	 * @since     1.0.0
	 * @access    private
     * 
	 * @return    array    $options Array of shop attributes
	 */
	private function attributes_list() {
		$options = array();
		$attributes = wc_get_attribute_taxonomies();
		foreach ( $attributes as $attribute ) {
			$options[$attribute->attribute_name] = $attribute->attribute_label;
		}
		
		return $options;
	}

    /** 
     * Get request url for api
     * 
	 * @since     1.0.0
     * 
	 * @return    string    $html Compiled html field
	 */
	public function request_url_callback() {
        $url = get_site_url() . '/wp-json/' . $this->endpoint . '/api/';
        $html = '<div class="input-field">';
        $html .= '<input type="text" id="request_url" value="' . esc_url($url) . '"  readonly="true" />';
        $html .= '</div>';
		$html .= '<button class="copy_btn">' . __('Copy to clipboard', 'sortvisually') . '</button>';
        $html .= '<span class="copied_info" style="display:none;">' . __('Copied!', 'sortvisually') . '</span>';
        $allowed_html = array(
            'div'      => array(
                'class' => array(),
            ),
            'input'    => array(
                'type'  => array(),
                'id'    => array(),
                'value' => array(),
                'readonly' => array(),
            ),
            'button'    => array(
                'class' => array(),
            ),
            'span'      => array(
                'class' => array(),
                'style' => array(),
            ),
        );
        echo wp_kses( $html, $allowed_html );
    }
    
	/**
	 * Retrieve array of plugin options by key.
	 *
	 * @since     1.0.0
     * 
     * @param     string    $key        Field name of settings option
     * 
	 * @return    array     $settings   Array with option value
	 */
    public function get_settings($key = false) {
        if($key == 'additional_attributes'){
            $settings = array();
            $settings['additional_product_data'] = self::get_settings_options()['additional_product_data'];
            $settings['additional_attributes'] = self::get_settings_options()[$key];
        }
        else {
            $settings = self::get_settings_options();
        }
        return $settings;
    }

	/**
	 * Retrieve the values of plugin settings options
	 *
	 * @since     1.0.0
	 * @access    private
     * 
	 * @return    array    The array with field value from WP option.
	 */
    private function get_settings_options(){
        return get_option( 'sortvisually_settings' );
    }
}