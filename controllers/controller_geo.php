<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////
/*
  LanguageController::get_public_language("/geo", function() {
    $model = new GeoModel();

    CMSView::render(array(
        "template" => "public/geo",
        "data" => $model->get_all_filtered_by_language(),
        "page_navigation" => "geo",
        "layout" => "public.hbs"
    ));
  });

  LanguageController::get_public_language("/geo/:piece(/:title)", function($piece) {
    $model = new GeoModel();

    CMSView::render(array(
        "template" => "public/geo_detail",
        "data" => $model->find($piece),
        "page_navigation" => "geo",
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
  //                                   GEO
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/geo(/page/:page)", "geo", "list", function($page = 1) {
    $model = new GeoModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/geo",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "geo",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/geo/search(/page/:page)", "geo", "list", function($page = 1) {
    $model = new GeoModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/geo",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "geo",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW GEO
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/geo/geo", "geo", "edit", function() {
    CMSView::render(array(
        "template" => "private/geo_detail",
        "data" => array(),
        "page_navigation" => "geo",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW GEO (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/geo/geo", "geo", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["geo"]["customer_id"] = $customer;

    $model = new GeoModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("geo_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/geo/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/geo/geo");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                GEO DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/geo/:piece", "geo", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new GeoModel();
    $info = $model->find($piece);

    $info["all_labels"] = $all_labels;

    CMSView::render(array(
        "template" => "private/geo_detail",
        "data" => $info,
        "page_navigation" => "geo",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                GEO UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/geo/:piece/save", "geo", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["geo"]["customer_id"] = $customer;

    $model = new GeoModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("geo_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/geo/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                GEO DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/geo/:piece/delete", "geo", "edit", function($piece) {
    $model = new GeoModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("geo_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/geo");
  });
