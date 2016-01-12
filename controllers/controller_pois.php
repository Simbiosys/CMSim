<?php
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
    $limit = $pagination["limit"];
    $pages = ceil($count/$limit);

    $label_model = new LabelModel();
    $all_labels = $label_model->get_all();

    CMSView::render(array(
        "template" => "private/pois",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL,
          "count" => $count,
          "pages" => $pages,
          "all_labels" => $all_labels
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
    $limit = $pagination["limit"];
    $pages = ceil($count/$limit);

    $label_model = new LabelModel();
    $all_labels = $label_model->get_all();

    CMSView::render(array(
        "template" => "private/pois",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL,
          "count" => $count,
          "pages" => $pages,
          "all_labels" => $all_labels
        ),
        "page_navigation" => "pois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT POIS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pois/import", "pois", "edit", function() {
    CMSView::render(array(
        "template" => "private/import_pois",
        "data" => array(),
        "page_navigation" => "pois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT POIS (POST)
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/pois/import", "pois", "edit", function() {
    $file = $_FILES["file_to_import"]["tmp_name"];
    $contents = file_get_contents($file);

    $model = new PoisModel();
    $response = $model->import($contents);

    CMSView::render(array(
        "template" => "private/import_pois",
        "data" => $response,
        "page_navigation" => "pois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT POIS (DELETE)
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/pois/import/delete", "pois", "edit", function() {
    $ids = \Singular\Controller::get_post_variable("pois");

    $model = new PoisModel();

    for ($i = 0; $i < count($ids); $i++) {
      $id = $ids[$i];

      $model->delete($id);
    }

    \Singular\Controller::redirect("/manager/pois");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF POIS
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::get_private("/manager/pois/new", "pois", "edit", function() {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    CMSView::render(array(
        "template" => "private/piece_pois_detail",
        "data" => array(
          "all_labels" => $all_labels
        ),
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

  \Singular\Controller::post_private("/manager/pois/new", "pois", "edit", function() {
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
    $info = $model->find_with_deleted($piece);
    $identifier = $info["pois"]["identifier"];

    $info["all_labels"] = $all_labels;

    $other_pois = $model->get_all_with_deleted("identifier = '$identifier' AND id <> '$piece'");

    CMSView::render(array(
        "template" => "private/piece_pois_detail",
        "data" => $info,
        "page_navigation" => "pois",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE ),
          "other_pois" => $other_pois
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
