<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////
/*
  LanguageController::get_public_language("/nopois", function() {
    $model = new NoPoisModel();

    CMSView::render(array(
        "template" => "public/nopois",
        "data" => $model->get_all_filtered_by_language(),
        "page_navigation" => "nopois",
        "layout" => "public.hbs"
    ));
  });

  LanguageController::get_public_language("/nopois/:piece(/:title)", function($piece) {
    $model = new NoPoisModel();

    CMSView::render(array(
        "template" => "public/piece_nopois_detail",
        "data" => $model->find($piece),
        "page_navigation" => "nopois",
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
  //                                   NOPOIS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/nopois(/page/:page)", "nopois", "list", function($page = 1) {
    $model = new NoPoisModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/nopois",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "nopois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/nopois/search(/page/:page)", "nopois", "list", function($page = 1) {
    $model = new NoPoisModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/nopois",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "nopois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW NOPOIS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/nopois/nopoi", "nopois", "edit", function() {
    CMSView::render(array(
        "template" => "private/piece_nopois_detail",
        "data" => array(),
        "page_navigation" => "nopois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW NOPOIS (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/nopois/nopoi", "nopois", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["nopois"]["customer_id"] = $customer;

    $model = new NoPoisModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("nopoi_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/nopois/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/nopois/nopoi");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                NOPOIS DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/nopois/:piece", "nopois", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new NoPoisModel();
    $info = $model->find($piece);

    $info["all_labels"] = $all_labels;

    CMSView::render(array(
        "template" => "private/piece_nopois_detail",
        "data" => $info,
        "page_navigation" => "nopois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                NOPOIS UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/nopois/:piece/save", "nopois", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["nopois"]["customer_id"] = $customer;

    $model = new NoPoisModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("nopoi_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/nopois/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                NOPOIS DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/nopois/:piece/delete", "nopois", "edit", function($piece) {
    $model = new NoPoisModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("nopoi_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/nopois");
  });
