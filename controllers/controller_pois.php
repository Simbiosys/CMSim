<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////
/*
  LanguageController::get_public_language("/pois", function() {
    $model = new PoisModel();

    CMSView::render(array(
        "template" => "public/pois",
        "data" => $model->get_all_filtered_by_language(),
        "page_navigation" => "pois",
        "layout" => "public.hbs"
    ));
  });

  LanguageController::get_public_language("/pois/:piece(/:title)", function($piece) {
    $model = new PoisModel();

    CMSView::render(array(
        "template" => "public/piece_pois_detail",
        "data" => $model->find($piece),
        "page_navigation" => "pois",
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
  //                                   POIS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pois(/page/:page)", "pois", "list", function($page = 1) {
    $model = new PoisModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/pois",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "pois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pois/search(/page/:page)", "pois", "list", function($page = 1) {
    $model = new PoisModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/pois",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "pois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF POIS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pois/poi", "pois", "edit", function() {
    CMSView::render(array(
        "template" => "private/piece_pois_detail",
        "data" => array(),
        "page_navigation" => "pois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW PIECE OF POIS (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/pois/poi", "pois", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["pois"]["customer_id"] = $customer;

    $model = new PoisModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("poi_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/pois/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/pois/poi");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                POIS DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pois/:piece", "pois", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new PoisModel();
    $info = $model->find($piece);

    $info["all_labels"] = $all_labels;

    CMSView::render(array(
        "template" => "private/piece_pois_detail",
        "data" => $info,
        "page_navigation" => "pois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                POIS UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/pois/:piece/save", "pois", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["pois"]["customer_id"] = $customer;

    $model = new PoisModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("poi_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/pois/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                POIS DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pois/:piece/delete", "pois", "edit", function($piece) {
    $model = new PoisModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("poi_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/pois");
  });
