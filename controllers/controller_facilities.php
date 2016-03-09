<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                   facilities
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities(/page/:page)", "facilities", "list", function($page = 1) {
    $model = new FacilitiesModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];
    $limit = $pagination["limit"];
    $pages = ceil($count/$limit);

    $label_model = new LabelModel();
    $all_labels = $label_model->get_all();

    CMSView::render(array(
        "template" => "private/facilities",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL,
          "count" => $count,
          "pages" => $pages,
          "all_labels" => $all_labels
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
    $model = new FacilitiesModel();
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
  //                              IMPORT FACILITIES
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities/import", "facilities", "edit", function() {
    $model_facilities = new FacilitiesModel();
    $facilities = $model_facilities->get_all_from_last_import();

    CMSView::render(array(
        "template" => "private/import_facilities",
        "data" => array(
          "last_import_elements" => $facilities
        ),
        "page_navigation" => "facilities",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT FACILITIES (POST)
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/facilities/import", "facilities", "edit", function() {
    $file = $_FILES["file_to_import"]["tmp_name"];
    $contents = file_get_contents($file);

    $model_facilities = new FacilitiesModel();
    $response = $model_facilities->import($contents);
    $facilities = $model_facilities->get_all_from_last_import();

    CMSView::render(array(
        "template" => "private/import_facilities",
        "data" => array(
          "last_import_elements" => $facilities,
          "import" => $response
        ),
        "page_navigation" => "facilities",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              IMPORT FACILITIES (DELETE)
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/facilities/import/delete", "facilities", "edit", function() {
    \Singular\Controller::set_content_type("application/json");
    $ids = \Singular\Controller::get_post_variable("facilities");

    $model = new FacilitiesModel();

    for ($i = 0; $i < count($ids); $i++) {
      $id = $ids[$i];

      $model->delete($id);
    }

    echo json_encode(array(
      "success" => TRUE
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF FACILITIES
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities/new", "facilities", "edit", function() {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    CMSView::render(array(
        "template" => "private/facility_detail",
        "data" => array(
          "all_labels" => $all_labels
        ),
        "page_navigation" => "facilities",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW PIECE OF FACILITIES (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/facilities/new", "facilities", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["facilities"]["customer_id"] = $customer;

    $model = new FacilitiesModel();

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
  //                                FACILITIES DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities/:piece", "facilities", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new FacilitiesModel();
    $info = $model->find($piece);
    $identifier = $info["facilities"]["identifier"];

    $info["all_labels"] = $all_labels;

    $other_facilities = $model->get_all_with_deleted("identifier = '$identifier' AND id <> '$piece'");

    CMSView::render(array(
        "template" => "private/facility_detail",
        "data" => $info,
        "page_navigation" => "facilities",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE ),
          "other_facilities" => $other_facilities
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                FACILITIES UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/facilities/:piece/save", "facilities", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["facilities"]["customer_id"] = $customer;

    $model = new FacilitiesModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("facility_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/facilities/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                FACILITIES DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/facilities/:piece/delete", "facilities", "edit", function($piece) {
    $model = new FacilitiesModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("facility_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/facilities");
  });
