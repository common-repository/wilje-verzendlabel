<?php

class Class_Wilje_Online_Verzendlabel_Shipping_Method extends \WC_Shipping_Flat_Rate {    
	/**
	 * The ID of this plugin settings.
	 *
	 * @var settings_id
	 */
    public const SETTINGS_ID = 'wilje_online_verzendlabel';
	public $settings_id = self::SETTINGS_ID;

	/**
	 * The name of this plugin service.
	 *
	 * @var service_name
	 */
    public const SERVICE_NAME = 'Wilje Online - Verzendlabel';
	public $service_name = self::SERVICE_NAME;

    public function __construct( $instance_id = 0 ) {
		$this->id           = self::SETTINGS_ID;
		$this->instance_id  = absint( $instance_id );
        $this->title        = self::SERVICE_NAME;
		$this->method_title = self::SERVICE_NAME;
		$this->method_description = sprintf(
            /* translators: %1$s and %2$s: Anchor tag to help our customers connect to PostNL */
            __( 'Here are all the features available to manage, organize, and handle your PostNL shipments. A valid business customer contract with PostNL is required. If you\'re not yet a PostNL business customer, you can request a quote %1$shere%2$s.', 'wilje-verzendlabel' ),
            '<a href="https://mijnpostnlzakelijk.postnl.nl/s/become-a-customer?language=nl_NL#/" target="_blank">',
            '</a>' 
        );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
			'settings',
		);

		$this->initShippingMethod();
	}

    public function initShippingMethod()
    {        
        $this->init();                
		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields() {
        $this->form_fields = [
            'title' => [
                'title'       => __('Method Title', 'wilje-verzendlabel'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'wilje-verzendlabel'),
                'default'     => __('PostNL Shipping method', 'wilje-verzendlabel'),
            ],
            'environment' => [
                'title'       => esc_html__( 'Environment Mode', 'wilje-verzendlabel'),
                'type'        => 'select',
                'description' => __( 'Choose the environment mode.', 'wilje-verzendlabel'),
                'options'     => [
                    'https://api.postnl.nl' => esc_html__( 'Production', 'wilje-verzendlabel'),
                    'https://api-sandbox.postnl.nl'    => esc_html__( 'Sandbox', 'wilje-verzendlabel'),
                ],
                'class'       => 'wc-enhanced-select',
                'default'     => 'sandbox',
                'placeholder' => '',
            ],
            'api_key_production' => [
                'title'       => esc_html__( 'Production API Key', 'wilje-verzendlabel'),
                'type'        => 'text',
                'description' => sprintf(
                    /* translators: %1$s and %2$s: Anchor tag to help our customers find their Production API key */ 
                    __( 'Please insert your PostNL production API-key. You can find your API-key on Mijn %1$sPostNL%2$s under "My Account".', 'wilje-verzendlabel'),
                    '<a href="https://mijn.postnl.nl/c/BP2_Mod_Login.app" target="_blank">',
                    '</a>'
                ),
                'default'     => '',
                'placeholder' => '',
            ],
            'api_keys_sandbox' => [
                'title'       => esc_html__( 'Sandbox API Key', 'wilje-verzendlabel'),
                'type'        => 'text',
                'description' => sprintf(
                    /* translators: %1$s and %2$s: Anchor tag to help our customers find their Sandbox API key */ 
                    __( 'Insert your PostNL sandbox API-key. You can find your API-key on Mijn %1$sPostNL%2$s under "My Account".', 'wilje-verzendlabel'),
                    '<a href="https://mijn.postnl.nl/c/BP2_Mod_Login.app" target="_blank">',
                    '</a>'
                ),
                'default'     => '',
                'placeholder' => '',
            ],
            'customer_code' => [
                'title' => esc_html__('Customer Code', 'wilje-verzendlabel'),
                'type' => 'text',
                'description' => __('This is required to connect to PostNL', 'wilje-verzendlabel'),
            ],
            'customer_number' => [
                'title' => esc_html__('Customer Number', 'wilje-verzendlabel'),
                'type' => 'text',
                'description' => __('This is required to connect to PostNL', 'wilje-verzendlabel'),
            ],
            'collection_location_bls' => [
                'title' => esc_html__('CollectionLocation (BLS)', 'wilje-verzendlabel'),
                'type' => 'text',
                'description' => __('This is required to connect to PostNL', 'wilje-verzendlabel'),
            ]
        ];
    }
}