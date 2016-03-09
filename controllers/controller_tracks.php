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
        "template" => "public/track_detail",
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
  //                                   TRACKS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks(/page/:page)", "tracks", "list", function($page = 1) {
    $model = new TracksModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];
    $limit = $pagination["limit"];
    $pages = ceil($count/$limit);

    $label_model = new LabelModel();
    $all_labels = $label_model->get_all();

    CMSView::render(array(
        "template" => "private/tracks",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL,
          "count" => $count,
          "pages" => $pages,
          "all_labels" => $all_labels
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
    $model = new TracksModel();
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
  //                              IMPORT TRACKS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks/import", "tracks", "edit", function() {
    $model_tracks = new TracksModel();
    $tracks = $model_tracks->get_all_from_last_import();

    CMSView::render(array(
        "template" => "private/import_tracks",
        "data" => array(
          "last_import_elements" => $tracks
        ),
        "page_navigation" => "tracks",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT TRACKS (POST)
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/tracks/import", "tracks", "edit", function() {
    $file = $_FILES["file_to_import"]["tmp_name"];
    $contents = file_get_contents($file);

    $model_tracks = new TracksModel();
    $response = $model_tracks->import($contents);
    $tracks = $model_tracks->get_all_from_last_import();

    CMSView::render(array(
        "template" => "private/import_tracks",
        "data" => array(
          "last_import_elements" => $tracks,
          "import" => $response
        ),
        "page_navigation" => "tracks",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT TRACKS (DELETE)
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/tracks/import/delete", "tracks", "edit", function() {
    \Singular\Controller::set_content_type("application/json");
    $ids = \Singular\Controller::get_post_variable("tracks");

    $model = new TracksModel();

    for ($i = 0; $i < count($ids); $i++) {
      $id = $ids[$i];

      $model->delete($id);
    }

    echo json_encode(array(
      "success" => TRUE
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF TRACKS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks/new", "tracks", "edit", function() {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    CMSView::render(array(
        "template" => "private/track_detail",
        "data" => array(
          "all_labels" => $all_labels
        ),
        "page_navigation" => "tracks",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW PIECE OF TRACKS (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/tracks/new", "tracks", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["tracks"]["customer_id"] = $customer;

    $model = new TracksModel();

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
  //                                TRACKS DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks/:piece", "tracks", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new TracksModel();
    $info = $model->find($piece);
    $identifier = $info["tracks"]["identifier"];

    $info["all_labels"] = $all_labels;

    $other_tracks = $model->get_all_with_deleted("identifier = '$identifier' AND id <> '$piece'");

    CMSView::render(array(
        "template" => "private/track_detail",
        "data" => $info,
        "page_navigation" => "tracks",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE ),
          "other_tracks" => $other_tracks
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                TRACKS UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/tracks/:piece/save", "tracks", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["tracks"]["customer_id"] = $customer;

    $model = new TracksModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("track_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/tracks/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                TRACKS DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/tracks/:piece/delete", "tracks", "edit", function($piece) {
    $model = new TracksModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("track_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/tracks");
  });
