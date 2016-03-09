<?php
  class LanguageController extends \Singular\Controller {
    public static function get_public_language($path, $handler) {
      $api = \Singular\Configuration::get_api();
      $languages = \Singular\Configuration::get_available_languages();

      // Declare middleware to set the language that appears in the URL
      $mw = function(\Slim\Route $route) use ($languages) {
        $pattern = $route->getPattern();

        foreach ($languages as $language) {
          if (strpos($pattern, "/$language/") !== FALSE) {
            AppAuthentication::set_language($language);
            break;
          }
        }
      };

      foreach ($languages as $language) {
        $api->group("/$language", function () use ($api, $path, $handler, $language, $mw) {
          // The previous middleware is invoked
          $api->get($path, $mw, $handler);
        });
      }
    }
  }

  //////////////////////////////////////////////////////////////////////////////
  //                                   UTILS
  //////////////////////////////////////////////////////////////////////////////

  function get_pagination($page) {
    $start = intval($page) - 1;

    if ($start < 0) {
      $start = 0;
    }

    $page_limit = \Singular\Configuration::get_app_settings("page_limit");
    $page_limit = intval($page_limit);
    $start *= $page_limit;

    // Filter by customer ID
    $customer = AppAuthentication::get_user_customer();
    $condition = "customer_id = '$customer'";

    return array(
      "start" => $start,
      "limit" => $page_limit,
      "page" => intval($page),
      "next" => $start + $page_limit,
      "condition" => $condition
    );
  }
