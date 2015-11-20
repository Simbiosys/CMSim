<?php

  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                   USERS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/users(/page/:page)", "users", "list", function($page = 1) {
    $model = new UserModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/users",
        "data" => array (
          "results" => $model->get_all($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "users",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "users" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/users/search(/page/:page)", "users", "list", function($page = 1) {
    $model = new UserModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/users",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "users",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "users" => TRUE )
        )
    ));
  });


  //////////////////////////////////////////////////////////////////////////////
  //                                 NEW USER
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/users/new", "users", "edit", function() {
    CMSView::render(array(
        "template" => "private/user_detail",
        "data" => array(),
        "page_navigation" => "users",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "users" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                             NEW USER (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/users/new", "users", "edit", function() {
    $model = new UserModel();

    $result = $model->create(array(
      "users" => array(
        "name" => \Singular\Controller::get_post_variable("users.name"),
        "surname" => \Singular\Controller::get_post_variable("users.surname"),
        "email" => \Singular\Controller::get_post_variable("users.email"),
        "customer_id" => AppAuthentication::get_user_customer()
      )
    ));

    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("new_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/users/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/users/new");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                USER DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/users/:user", "users", "edit", function($user) {
    $model = new UserModel();

    CMSView::render(array(
        "template" => "private/user_detail",
        "data" => $model->find($user),
        "page_navigation" => "users",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "users" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                USER UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/users/:user/save", "users", "edit", function($user) {
    $model = new UserModel();
    $model->update($user, array(
      "users" => array(
        "name" => \Singular\Controller::get_post_variable("users.name"),
        "surname" => \Singular\Controller::get_post_variable("users.surname"),
        "email" => \Singular\Controller::get_post_variable("users.email"),
        "customer_id" => AppAuthentication::get_user_customer()
      )
    ));

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/users/$user");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                USER DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/users/:user/delete", "users", "edit", function($user) {
    $model = new UserModel();
    $model->delete($user);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/users");
  });
