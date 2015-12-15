<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////
/*
  LanguageController::get_public_language("/settings", function() {
    $model = new settingsModel();

    CMSView::render(array(
        "template" => "public/settings",
        "data" => $model->get_all_filtered_by_language(),
        "page_navigation" => "settings",
        "layout" => "public.hbs"
    ));
  });

  LanguageController::get_public_language("/settings/:piece(/:title)", function($piece) {
    $model = new settingsModel();

    CMSView::render(array(
        "template" => "public/piece_settings_detail",
        "data" => $model->find($piece),
        "page_navigation" => "settings",
        "layout" => "public.hbs"
    ));
  });
*/
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                   settings
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/settings(/page/:page)", "settings", "list", function($page = 1) {
    $model = new settingsModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/settings",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "settings",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/settings/search(/page/:page)", "settings", "list", function($page = 1) {
    $model = new settingsModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/settings",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "settings",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF settings
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/settings/facility", "settings", "edit", function() {
    CMSView::render(array(
        "template" => "private/piece_settings_detail",
        "data" => array(),
        "page_navigation" => "settings",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW PIECE OF settings (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/settings/facility", "settings", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["settings"]["customer_id"] = $customer;

    $model = new settingsModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("facility_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/settings/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/settings/facility");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                settings DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/settings/:piece", "settings", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new settingsModel();
    $info = $model->find($piece);

    $info["all_labels"] = $all_labels;

    CMSView::render(array(
        "template" => "private/piece_settings_detail",
        "data" => $info,
        "page_navigation" => "settings",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                settings UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/settings/:piece/save", "settings", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["settings"]["customer_id"] = $customer;

    $model = new settingsModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("facility_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/settings/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                settings DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/settings/:piece/delete", "settings", "edit", function($piece) {
    $model = new settingsModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("facility_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/settings");
  });
