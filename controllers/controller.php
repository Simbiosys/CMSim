<?php
  //////////////////////////////////////////////////////////////////////////////
  //                                  ROOT
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_public("/", function() {
    if (redirect_when_logged()) {
      return;
    }

    \Singular\View::render(array(
        "template" => "index"
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                  LOGIN
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_public("/login", function() {
    if (redirect_when_logged()) {
      return;
    }

    \Singular\View::render(array(
        "template" => "login"
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
    \Singular\Controller::flash("Usuario y/o contraseña incorrectos");

    \Singular\Controller::redirect("/login");
  });

  \Singular\Controller::get_public("/logout", function() {
    AppAuthentication::log_out();
    \Singular\Controller::redirect("/login");
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
      $default_path = "/login";
    }

    \Singular\Controller::redirect($default_path);
  }
