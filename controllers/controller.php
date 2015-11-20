<?php
  //////////////////////////////////////////////////////////////////////////////
  //                                  ROOT
  //////////////////////////////////////////////////////////////////////////////
  LanguageController::get_public("/", function() {
    $language = \Singular\Configuration::get_default_language();

    \Singular\Controller::redirect("/$language");
  });

  LanguageController::get_public_language("/", function() {

    CMSView::render(array(
        "template" => "index",
        "layout" => "public.hbs"
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                  LOGIN
  //////////////////////////////////////////////////////////////////////////////
  LanguageController::get_public_language("/login", function() {
    if (redirect_when_logged()) {
      return;
    }

    CMSView::render(array(
        "template" => "login",
        "layout" => "public.hbs"
    ));
  });

  \Singular\Controller::post_public("/login", function() {
    $is_logged_in = AppAuthentication::is_logged_in();

    // If the user is already logged in we redirect to the default path of
    // his role.
    if ($is_logged_in) {
      $user_info = AppAuthentication::get_user_data();
      redirect_to_user_default_path($user_info);
      return;
    }

    $user = \Singular\Controller::get_post_variable("user");
    $password = \Singular\Controller::get_post_variable("password");

    $user_info = AppAuthentication::log_in($user, $password);

    if ($user_info) {
      // Redirection to user's role default path
      redirect_to_user_default_path($user_info);
      return;
    }

    // Invalid login

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("user_password_incorrent"),
      "code" => "error"
    ));

    $language = \Singular\Configuration::get_default_language();
    \Singular\Controller::redirect("/$language/login");
  });

  \Singular\Controller::get_public("/logout", function() {
    AppAuthentication::log_out();
    $language = \Singular\Configuration::get_default_language();
    \Singular\Controller::redirect("/$language/login");
  });

  \Singular\Controller::post_private("/impersonate/:customer",
  "customers", "list", function($customer) {
    AppAuthentication::impersonate_customer($customer);
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                 LANGUAGE
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::get_public("/language/:language", function($language) {
    AppAuthentication::set_language($language);

    $user_info = AppAuthentication::get_user_data();
    redirect_to_user_default_path($user_info);
  });

  \Singular\Controller::post_public("/language/:language", function($language) {
    AppAuthentication::set_language($language);

    $url = \Singular\Controller::get_post_variable("url");
    $languages = \Singular\Configuration::get_available_languages();

    foreach ($languages as $lang) {
      if (strpos($url, "/$lang/") !== FALSE) {
        $url = preg_replace("/\/$lang\//", "/$language/", $url, 1);
        break;
      }
      else if (endsWith($url, "/$lang")) {
        $url = str_lreplace("/$lang", "/$language", $url);
        break;
      }
    }

    echo json_encode(array(
      "url" => $url
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           AUXILIARY FUNCTIONS
  //////////////////////////////////////////////////////////////////////////////
  function redirect_when_logged() {
    $is_logged_in = AppAuthentication::is_logged_in();

    // If the user is already logged in we redirect to the default path of
    // his role.
    if ($is_logged_in) {
      $user_info = AppAuthentication::get_user_data();
      redirect_to_user_default_path($user_info);
      return TRUE;
    }

    return FALSE;
  }

  function redirect_to_user_default_path($user) {
    $role = AppAuthorisation::get_user_role();

    $default_path = \Singular\Configuration::get_app_settings(array("roles", $role, "default_path"));

    if (empty($default_path)) {
      $default_path = "/";
    }

    \Singular\Controller::redirect($default_path);
  }

  function endsWith($str, $sub) {
    return (substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub);
  }

  function str_lreplace($search, $replace, $subject) {
    $pos = strrpos($subject, $search);

    if ($pos !== FALSE) {
      $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
  }
