<?php
	class CMSView extends \Singular\View {
		protected function add_custom_page_info($defaults) {
			$host = $defaults["host"];
			$model = new CustomerModel();

			return array(
				"customers" => $model->get_all(),
				"impersonated_customer" => AppAuthentication::get_impersonated_customer(),
				"manager" => "$host/manager"
			);
		}
  }
