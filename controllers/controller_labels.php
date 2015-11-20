<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////
/*
  LanguageController::get_public_language("/labels", function() {
    $model = new LabelModel();

    CMSView::render(array(
        "template" => "public/labels",
        "data" => $model->get_all_filtered_by_language(),
        "page_navigation" => "labels",
        "layout" => "public.hbs"
    ));
  });

  LanguageController::get_public_language("/labels/:label(/:title)", function($label) {
    $model = new LabelModel();

    CMSView::render(array(
        "template" => "public/label_news_detail",
        "data" => $model->find($label),
        "page_navigation" => "labels",
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
  //                                   LABELS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/labels(/page/:page)", "labels", "list", function($page = 1) {
    $model = new  LabelModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/labels",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "labels",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/labels/search(/page/:page)", "labels", "list", function($page = 1) {
    $model = new LabelModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/labels",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "labels",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                NEW LABEL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/labels/new", "labels", "edit", function() {
    $model = new LabelModel();
    $other_labels = $model->get_all_filtered_by_language();

    CMSView::render(array(
        "template" => "private/label_detail",
        "data" => array(
          "other_labels" => $other_labels
        ),
        "page_navigation" => "labels",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                             NEW LABEL (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/labels/new", "labels", "edit", function() {
    $data = \Singular\Controller::get_data_by_entity("labels");
    $customer = AppAuthentication::get_user_customer();
    $data["labels"]["customer_id"] = $customer;

    $model = new LabelModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("new_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/labels/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/labels/new");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                LABEL DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/labels/:label", "labels", "edit", function($label) {
    $model = new LabelModel();
    $other_labels = $model->get_all_filtered_by_language("labels.id <> '$label'");
    $data = $model->find($label);

    $data["other_labels"] = $other_labels;

    CMSView::render(array(
        "template" => "private/label_detail",
        "data" => $data,
        "page_navigation" => "labels",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                LABEL UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/labels/:label/save", "labels", "edit", function($label) {
    $data = \Singular\Controller::get_data_by_entity("labels");
    $customer = AppAuthentication::get_user_customer();
    $data["labels"]["customer_id"] = $customer;

    $model = new LabelModel();
    $model->update($label, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/labels/$label");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                LABEL DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/labels/:label/delete", "labels", "edit", function($label) {
    $model = new LabelModel();
    $model->delete($label);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/labels");
  });
