<?php

	namespace MPHB\Payments\Gateways;

	use \MPHB\Admin\Groups;
	use \MPHB\Admin\Fields;

	require_once('QuickPay/API/Constants.php');
	require_once('QuickPay/API/Response.php');

	require_once('QuickPay/API/Client.php');
	require_once('QuickPay/API/Request.php');
	require_once('QuickPay/QuickPay.php');

	use QuickPay\QuickPay;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class QuickpayGateway extends Gateway {

	protected $api_key;

    public function __construct(){
        add_filter( 'mphb_gateway_has_instructions', array( $this, 'hideInstructions' ), 10, 2 );
        parent::__construct();
    }

    /**
     * @param bool $show
     * @param string $gatewayId
     * @return bool
     *
     * @since 3.6.1
     */
    public function hideInstructions( $show, $gatewayId ){
        if ( $gatewayId == $this->id ) {
            $show = false;
        }
        return $show;
    }

	protected function setupProperties(){
		parent::setupProperties();
		$this->adminTitle = __( 'Quickpay', 'motopress-hotel-booking' );
		$this->api_key		= $this->getOption( 'api_key' );
	}

	protected function initDefaultOptions(){
		$defaults = array(
			'title'			 => __( 'Quickpay Payment', 'motopress-hotel-booking' ),
			'description'	 => '',
			'enabled'		 => false,
			'api_key' 		=> ''
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	protected function initId(){
		return 'quickpay';
	}

	public function registerOptionsFields( &$subTab ){

		parent::registerOptionsFields( $subTab );
		$group = new Groups\SettingsGroup( "mphb_payments_{$this->id}_group2", '', $subTab->getOptionGroupName() );

		$groupFields = array(
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_api_key", array(
				'type'		 => 'text',
				'label'		 => __( 'Quickpay API Key', 'motopress-hotel-booking' ),
				'default'	 => ''
			) )
		);

		$group->addFields( $groupFields );

		$subTab->addGroup( $group );

	}

	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ){

		$isComplete	 = $this->paymentCompleted( $payment );
		
		$successUrl = MPHB()->settings()->pages()->getReservationReceivedPageUrl($payment);

		$faildeUrl 	= MPHB()->settings()->pages()->getPaymentFailedPageUrl($payment);
		
		$cancelUrl 	= MPHB()->settings()->pages()->getUserCancelRedirectPageUrl();

		$url = $this->getPaymentUrl( $booking, $payment, $successUrl, $faildeUrl, $cancelUrl );

		// Redirect to paypal checkout
		wp_redirect( $url );
		exit;
	}

	public function getPaymentUrl( $booking, $_payment, $successUrl, $faildeUrl, $cancelUrl ){

		$url = $faildeUrl;

		$amount = $_payment->getAmount() . '00';

		$order_id = '00' . $_payment->getId();

		try {
		    //Initialize client
		    $client = new QuickPay(":$this->api_key");//new QuickPay(":$this->api_key");

		    //Create payment
		    $payment = $client->request->post('/payments', [
		        'order_id' => $order_id,
		        'currency' => 'DKK'
		    ]);

		    $status = $payment->httpStatus();

		    //Determine if payment was created successfully
		    if ($status === 201) {

		        $paymentObject = $payment->asObject();

		        //Construct url to create payment link
		        $endpoint = sprintf("/payments/%s/link", $paymentObject->id);

		        //Issue a put request to create payment link
		        $link = $client->request->put($endpoint, [
		            'amount' 		=> $amount, //amount in cents
			        "continueurl" 	=> $successUrl,
					"cancelurl"   	=> $cancelUrl,
					"callbackurl" 	> $faildeUrl
		        ]);

		        //Determine if payment link was created succesfully
		        if ($link->httpStatus() === 200) {
		            //Get payment link url
		            $url = $link->asObject()->url;
		        }
		    }
		} catch (\Exception $e) {
		    echo $e->getMessage();
		}

		return $url;

	}

}
