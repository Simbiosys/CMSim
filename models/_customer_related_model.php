<?php
  class CustomerRelatedModel extends \Singular\Model {
    protected function init() {
      $customer = AppAuthentication::get_user_customer();

      $this->filter = "customer_id = '$customer'";
    }
  }
