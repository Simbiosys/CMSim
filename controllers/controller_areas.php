<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                   AREAS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/areas(/page/:page)", "areas", "list", function($page = 1) {
    $model = new AreasModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];
    $limit = $pagination["limit"];
    $pages = ceil($count/$limit);

    $label_model = new LabelModel();
    $all_labels = $label_model->get_all();

    CMSView::render(array(
        "template" => "private/areas",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL,
          "count" => $count,
          "pages" => $pages,
          "all_labels" => $all_labels
        ),
        "page_navigation" => "areas",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/areas/search(/page/:page)", "areas", "list", function($page = 1) {
    $model = new AreasModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/areas",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "areas",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT AREAS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/areas/import", "areas", "edit", function() {
    $model_areas = new AreasModel();
    $areas = $model_areas->get_all_from_last_import();

    CMSView::render(array(
        "template" => "private/import_areas",
        "data" => array(
          "last_import_elements" => $areas
        ),
        "page_navigation" => "areas",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT AREAS (POST)
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/areas/import", "areas", "edit", function() {
    $file = $_FILES["file_to_import"]["tmp_name"];
    $contents = file_get_contents($file);

    $model_areas = new AreasModel();
    $response = $model_areas->import($contents);
    $areas = $model_areas->get_all_from_last_import();

    CMSView::render(array(
        "template" => "private/import_areas",
        "data" => array(
          "last_import_elements" => $areas,
          "import" => $response
        ),
        "page_navigation" => "areas",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT AREAS (DELETE)
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/areas/import/delete", "areas", "edit", function() {
    \Singular\Controller::set_content_type("application/json");
    $ids = \Singular\Controller::get_post_variable("areas");

    $model = new AreasModel();

    for ($i = 0; $i < count($ids); $i++) {
      $id = $ids[$i];

      $model->delete($id);
    }

    echo json_encode(array(
      "success" => TRUE
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF AREAS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/areas/new", "areas", "edit", function() {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    CMSView::render(array(
        "template" => "private/area_detail",
        "data" => array(
          "all_labels" => $all_labels
        ),
        "page_navigation" => "areas",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW PIECE OF AREAS (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/areas/new", "areas", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["areas"]["customer_id"] = $customer;

    $model = new AreasModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("area_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/areas/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/areas/area");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                AREAS DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/areas/:piece", "areas", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new AreasModel();
    $info = $model->find($piece);
    $identifier = $info["areas"]["identifier"];

    $info["all_labels"] = $all_labels;

    $other_areas = $model->get_all_with_deleted("identifier = '$identifier' AND id <> '$piece'");

    CMSView::render(array(
        "template" => "private/area_detail",
        "data" => $info,
        "page_navigation" => "areas",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE ),
          "other_areas" => $other_areas
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                AREAS UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/areas/:piece/save", "areas", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["areas"]["customer_id"] = $customer;

    $model = new AreasModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("area_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/areas/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                AREAS DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/areas/:piece/delete", "areas", "edit", function($piece) {
    $model = new AreasModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("area_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/areas");
  });
