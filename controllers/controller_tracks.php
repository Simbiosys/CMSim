<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////
/*
  LanguageController::get_public_language("/tracks", function() {
    $model = new tracksModel();

    CMSView::render(array(
        "template" => "public/tracks",
        "data" => $model->get_all_filtered_by_language(),
        "page_navigation" => "tracks",
        "layout" => "public.hbs"
    ));
  });

  LanguageController::get_public_language("/tracks/:piece(/:title)", function($piece) {
    $model = new tracksModel();

    CMSView::render(array(
        "template" => "public/piece_tracks_detail",
        "data" => $model->find($piece),
        "page_navigation" => "tracks",
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
  //                                   trackS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks(/page/:page)", "tracks", "list", function($page = 1) {
    $model = new tracksModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/tracks",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "tracks",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks/search(/page/:page)", "tracks", "list", function($page = 1) {
    $model = new tracksModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/tracks",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "tracks",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF tracks
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks/new", "tracks", "edit", function() {
    CMSView::render(array(
        "template" => "private/piece_tracks_detail",
        "data" => array(),
        "page_navigation" => "tracks",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW PIECE OF tracks (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/tracks/new", "tracks", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["tracks"]["customer_id"] = $customer;

    $model = new tracksModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("track_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/tracks/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/tracks/track");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                tracks DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks/:piece", "tracks", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new tracksModel();
    $info = $model->find($piece);

    $info["all_labels"] = $all_labels;

    CMSView::render(array(
        "template" => "private/piece_tracks_detail",
        "data" => $info,
        "page_navigation" => "tracks",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                tracks UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/tracks/:piece/save", "tracks", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["tracks"]["customer_id"] = $customer;

    $model = new tracksModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("track_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/tracks/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                tracks DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks/:piece/delete", "tracks", "edit", function($piece) {
    $model = new tracksModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("track_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/tracks");
  });
