<?php
	class CMSView extends \Singular\View {
		protected function add_custom_page_info($defaults) {
			$host = $defaults["host"];
			$customer_model = new CustomerModel();

			$settings_model = new SettingsModel();
			$settings = $settings_model->get_all();

			if (count($settings) > 0) {
				$settings = $settings[0]["settings"];
			}
			else {
				$settings = array();
			}

			return array(
				"customers" => $customer_model->get_all(),
				"impersonated_customer" => AppAuthentication::get_impersonated_customer(),
				"manager" => "$host/manager",
				"settings" => $settings
			);
		}
  }
