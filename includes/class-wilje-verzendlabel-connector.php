<?php

class Class_Wilje_Online_Verzendlabel_Connector {
    /**
	 * @var Class_Wilje_Online_Verzendlabel_Shipping_Method shipping_method
	 */
	private $shipping_method;
	
	/**
	 * @var WP_Http
	 */
	private $wp_http;

    public function __construct(Class_Wilje_Online_Verzendlabel_Shipping_Method $shipping_method)
    {
        $this->shipping_method = $shipping_method;
		$this->wp_http = new WP_Http();
    }

	private function get_total_order_weight(array $order_items)
	{
		$total_weight = 0;
		foreach ($order_items as $order_item) {
			$total_weight = (int) $total_weight + (int) $order_item->get_product()->get_weight();
		}

		return $total_weight;
	}

    public function get_verzendlabel_label(WC_Order $order)
    {
		if (!isset($_GET[ '_wpnonce' ]) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET[ '_wpnonce' ])), 'print_postnl_label')) {
			return;
		}
		// First we go ahead and prepare some variables, if they are brought with us in the sanitizied request.
		$barcode = "";
		if (isset($_GET['barcode'])){
			$barcode = sanitize_text_field(wp_unslash($_GET['barcode']));
		}

		$parcel_option = "";
		if (isset($_GET['parceloption'])){
			$parcel_option = sanitize_text_field(wp_unslash($_GET['parceloption']));
		}

		// Then we request PostNL kindly for a shipment label, potentially using the above prepared variables.
		$shipping_address = $order->get_address();
		$response = $this->wp_http->post($this->shipping_method->get_option('environment').'/shipment/v2_2/label', 
		[
			'headers' => [
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
				'apikey'        => $this->shipping_method->get_option('api_keys_sandbox'),
			],
			'body'    => wp_json_encode([
				"Customer" => [
					"Address" => [
						"AddressType"   => "02", // Sender
						"City"          => get_option('woocommerce_store_city'),
						"CompanyName"   => get_option('woocommerce_store_company_name'),
						"Countrycode"   => get_option('woocommerce_default_country'),
						"HouseNr"       => get_option('woocommerce_store_address_2'),
						"Street"        => get_option('woocommerce_store_address'),
						"Zipcode"       => get_option('woocommerce_store_postcode')
					],
					"CollectionLocation" => $this->shipping_method->get_option('collection_location_bls'),
					"CustomerCode"       => $this->shipping_method->get_option('customer_code'),
					"CustomerNumber"     => $this->shipping_method->get_option('customer_number')
				],
				"Message" => [
					"MessageID"         => "1",
					"MessageTimeStamp"  => gmdate('d-m-Y h:i:s'),
					"Printertype"       => "GraphicFile|PDF"
				],
				"Shipments" => [
					[
						"Addresses" => [
							[
								"AddressType" => "01", // Reciever - this is the customer
								"City"        => $shipping_address['city'],
								"Countrycode" => $shipping_address['country'],
								"FirstName"   => $shipping_address['first_name'],
								"HouseNr"     => $order->get_meta('_shipping_house_number'),
								"Name"        => $shipping_address['last_name'],
								"Street"      => $shipping_address['address_1'],
								"Zipcode"     => $shipping_address['postcode']
							]
						],						
						"Barcode" => $barcode,
						"Amounts" => [
							[
								"AmountType" => "02",
								"Currency"   => "EUR",
								"Value"      => $order->get_total()
							]
						],
						"Dimension" => [
							"Weight" => $this->get_total_order_weight($order->get_items())
						],
						"ProductCodeDelivery" => $parcel_option
						]
				]
			]),
			'timeout' => 60,
		]);

		if (is_wp_error($response)) {
			throw new \Exception(esc_html('Error: ' . $response->get_error_message()));
		}

		return json_decode(wp_remote_retrieve_body($response), true);
    }
}
