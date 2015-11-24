<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////
/*
  LanguageController::get_public_language("/facilities", function() {
    $model = new facilitiesModel();

    CMSView::render(array(
        "template" => "public/facilities",
        "data" => $model->get_all_filtered_by_language(),
        "page_navigation" => "facilities",
        "layout" => "public.hbs"
    ));
  });

  LanguageController::get_public_language("/facilities/:piece(/:title)", function($piece) {
    $model = new facilitiesModel();

    CMSView::render(array(
        "template" => "public/piece_facilities_detail",
        "data" => $model->find($piece),
        "page_navigation" => "facilities",
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
  //                                   facilities
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities(/page/:page)", "facilities", "list", function($page = 1) {
    $model = new facilitiesModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/facilities",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "facilities",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities/search(/page/:page)", "facilities", "list", function($page = 1) {
    $model = new facilitiesModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/facilities",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "facilities",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF facilities
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities/facility", "facilities", "edit", function() {
    CMSView::render(array(
        "template" => "private/piece_facilities_detail",
        "data" => array(),
        "page_navigation" => "facilities",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW PIECE OF facilities (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/facilities/facility", "facilities", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["facilities"]["customer_id"] = $customer;

    $model = new facilitiesModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("facility_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/facilities/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/facilities/facility");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                facilities DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities/:piece", "facilities", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new facilitiesModel();
    $info = $model->find($piece);

    $info["all_labels"] = $all_labels;

    CMSView::render(array(
        "template" => "private/piece_facilities_detail",
        "data" => $info,
        "page_navigation" => "facilities",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                facilities UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/facilities/:piece/save", "facilities", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["facilities"]["customer_id"] = $customer;

    $model = new facilitiesModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("facility_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/facilities/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                facilities DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities/:piece/delete", "facilities", "edit", function($piece) {
    $model = new facilitiesModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("facility_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/facilities");
  });
