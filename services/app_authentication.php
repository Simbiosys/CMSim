<?php
  class AppAuthentication extends \Singular\AuthenticationMethod  {
    protected static $identifier = "cmsim";

    public static function log_in($user, $password) {
      $model = new UserModel();
      $user_info = $model->validate_user($user, $password);

      if (!empty($user_info)) {
        self::set_user($user, $user_info);

        return TRUE;
      }

      return FALSE;
    }

    public static function get_log_in() {
      $host = \Singular\Configuration::get_host();
      return  "$host/login";
    }
  }
