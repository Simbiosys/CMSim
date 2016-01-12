<?php
  class AppAuthentication extends \Singular\AuthenticationMethod  {
    protected static $identifier = "cmsim";
    protected static $model_identifier = "users";

    public static function log_in($user, $password) {
      $model = new UserModel();
      $user_info = $model->validate_user($user, $password);

      if (!empty($user_info)) {
        $role = isset($user_info["role"]) ? $user_info["role"] : NULL;

        if ($role) {
          $user_info["roles"] = array();
          $user_info["roles"][$role] = TRUE;
        }

        self::set_user($user, $user_info);

        // Admin impersonates first customer
        if (self::is_admin()) {
          $first_customer = self::get_first_customer();

          if ($first_customer) {
            self::impersonate_customer($first_customer["id"]);
          }
        }

        return TRUE;
      }

      return FALSE;
    }

    public static function get_log_in() {
      $host = \Singular\Configuration::get_host();
      $language = \Singular\Configuration::get_default_language();
      return  "$host/$language/login";
    }

    public static function get_user_customer() {
      $admin_impersonation = self::get_user_attribute("admin_impersonation");
      $customer = self::get_user_attribute("customer_id");

      if (empty($customer)) {
        $customer = self::get_first_customer();
        $customer = isset($customer["id"]) ? $customer["id"] : NULL;
      }

      return empty($admin_impersonation) ? $customer : $admin_impersonation;
    }

    public static function impersonate_customer($customer) {
      self::set_user_attribute("admin_impersonation", $customer);
    }

    public static function get_impersonated_customer() {
      return self::get_user_attribute("admin_impersonation");
    }

    public static function is_admin() {
      $role = self::get_user_attribute("role");

      return $role == "administrator_role";
    }

    private static function get_first_customer() {
      $model = new CustomerModel();
      $customers = $model->get_all();

      $data = count($customers) > 0 ? $customers[0] : NULL;

      if (!empty($data)) {
        $data = isset($data["customers"]) ? $data["customers"] : $data;
      }

      return $data;
    }

    public static function get_user_id() {
      return self::get_user_attribute("id");
    }

    public static function get_user_name() {
      return self::get_user_attribute("email");
    }
  }
