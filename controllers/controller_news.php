<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  LanguageController::get_public_language("/news", function() {
    $model = new InformationModel();

    CMSView::render(array(
        "template" => "public/news",
        "data" => $model->get_all_filtered_by_language(),
        "page_navigation" => "news",
        "layout" => "public.hbs",
    ));
  });

  LanguageController::get_public_language("/news/:piece(/:title)", function($piece) {
    $model = new InformationModel();

    CMSView::render(array(
        "template" => "public/piece_news_detail",
        "data" => $model->find($piece),
        "page_navigation" => "news",
        "layout" => "public.hbs",
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                   NEWS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/news(/page/:page)", "news", "list", function($page = 1) {
    $model = new InformationModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];
    $limit = $pagination["limit"];
    $pages = ceil($count/$limit);

    CMSView::render(array(
        "template" => "private/news",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL,
          "count" => $count,
          "pages" => $pages
        ),
        "page_navigation" => "news",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/news/search(/page/:page)", "news", "list", function($page = 1) {
    $model = new InformationModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/news",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL,
          "count" => $count
        ),
        "page_navigation" => "news",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF NEWS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/news/new", "news", "edit", function() {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    CMSView::render(array(
        "template" => "private/piece_news_detail",
        "data" => array(
          "all_labels" => $all_labels
        ),
        "page_navigation" => "news",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                           NEW PIECE OF NEWS (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/news/new", "news", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["informations"]["customer_id"] = $customer;

    $model = new InformationModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("new_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/news/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/news/new");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                NEWS DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/news/:piece", "news", "edit", function($piece) {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new InformationModel();
    $info = $model->find($piece);

    $info["all_labels"] = $all_labels;

    CMSView::render(array(
        "template" => "private/piece_news_detail",
        "data" => $info,
        "page_navigation" => "news",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                NEWS UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/news/:piece/save", "news", "edit", function($piece) {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["informations"]["customer_id"] = $customer;

    $model = new InformationModelLabels();
    $model->delete_by_condition("information_id = '$piece'");

    $model = new InformationModel();
    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/news/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                NEWS DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/news/:piece/delete", "news", "edit", function($piece) {
    $model = new InformationModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/news");
  });
