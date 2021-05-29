<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Tabs;

class GatewayManager {

	/**
	 *
	 * @var Gateway
	 */
	private $gateways = array();

	public function __construct(){
		add_action( 'plugins_loaded', array( $this, 'initPrebuiltGateways' ) );
		add_action( 'init', array( $this, 'registerGateways' ), 5 );
		add_action( 'mphb_generate_settings_payments', array( $this, 'generateSubTabs' ), 10, 2 );
	}

    /**
     * @since 3.7.0 added new action - "mphb_register_gateways".
     */
	public function registerGateways(){
        do_action( 'mphb_register_gateways' );
		do_action( 'mphb_init_gateways', $this );
	}

	public function initPrebuiltGateways(){
		new ManualGateway();
		new TestGateway();
		new CashGateway();
        new BankGateway();
		new PaypalGateway();
		new TwoCheckoutGateway();
		new StripeGateway();
		new BraintreeGateway();
		new BeanstreamGateway();
		new QuickpayGateway();
	}

	/**
	 *
	 * @param \MPHB\Payments\Gateways\GatewayInterface $gateway
	 */
	public function addGateway( GatewayInterface $gateway ){
		$this->gateways[$gateway->getId()] = $gateway;
	}

	/**
	 *
	 * @param string $id
	 * @return Gateway|null
	 */
	public function getGateway( $id ){
		return isset( $this->gateways[$id] ) ? $this->gateways[$id] : null;
	}

	/**
	 *
	 * @return Gateway[]
	 */
	public function getList(){
		return $this->gateways;
	}

	/**
	 *
	 * @return Gateway[]
	 */
	public function getListEnabled(){
		return array_filter( $this->gateways, function ( $gateway ) {
			return $gateway->isEnabled();
		} );
	}

	/**
	 *
	 * @return Gateway[]
	 */
	public function getListActive(){
		return array_filter( $this->gateways, function ( $gateway ) {
			return $gateway->isActive();
		} );
	}

	/**
	 *
	 * @param \MPHB\Admin\Tabs\SettingsTab $tab
	 */
	public function generateSubTabs( $tab ){

		foreach ( $this->gateways as $gateway ) {

			if ( !$gateway->isShowOptions() ) {
				continue;
			}

			$subTab = new Tabs\SettingsSubTab( $gateway->getId(), $gateway->getAdminTitle(), $tab->getPageName(), $tab->getName() );
			$subTab->setDescription( $gateway->getAdminDescription() );

			$gateway->registerOptionsFields( $subTab );

			$tab->addSubTab( $subTab );
		}
	}

}
