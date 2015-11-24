<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////
/*
  LanguageController::get_public_language("/media", function() {
    $model = new InformationModel();

    CMSView::render(array(
        "template" => "public/media",
        "data" => $model->get_all_filtered_by_language(),
        "page_navigation" => "media",
        "layout" => "public.hbs",
    ));
  });

  LanguageController::get_public_language("/media/:piece(/:title)", function($piece) {
    $model = new InformationModel();

    CMSView::render(array(
        "template" => "public/piece_news_detail",
        "data" => $model->find($piece),
        "page_navigation" => "media",
        "layout" => "public.hbs",
    ));
  });
*/
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                 MEDIA
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/media(/page/:page)", "media", "list", function($page = 1) {
    $model = new MediaModel();
    $pagination = get_pagination($page);

    if ($pagination["condition"]) {
      $pagination["condition"] .= " AND ";
    }
    else {
      $pagination["condition"] = "";
    }

    $pagination["condition"] .= "media.parent IS NULL";

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/media",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "media",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                  SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/media/search(/page/:page)", "media", "list", function($page = 1) {
    $model = new MediaModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/media",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "media",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  function check_folder($folder) {
    if (!file_exists($folder) && !is_dir($folder)) {
      mkdir($folder);
      chmod($folder, 0777);
    }
  }

  function check_customer_folder($customer) {
    $root = \Singular\Configuration::get_root();
    $full_path = $root . \Singular\Configuration::get_app_settings("file_path")
        . "/$customer";

    check_folder($full_path);
  }

  //////////////////////////////////////////////////////////////////////////////
  //                                NEW FOLDER
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/media/new_folder", "media", "edit", function() {
    $node = \Singular\Controller::get_post_variable("node");
    $name = \Singular\Controller::get_post_variable("name");

    $model = new MediaModel();

    if ($node) {
      $info = $model->find($node);

      if ($info) {
        $path = $info["media"]["file_path"] . "/$name";
      }
    }
    else {
      $path = $root . \Singular\Configuration::get_app_settings("file_path")
          . "/$customer/$path";
    }

    $customer = AppAuthentication::get_user_customer();
    check_customer_folder($customer);

    $root = \Singular\Configuration::get_root();

    check_folder($path);

    $model->create(array(
      "media" => array(
        "file_path" => $path,
        "title" => $name,
        "is_folder" => TRUE,
        "customer_id" => $customer,
        "parent" => $node
      ),
    ));

    \Singular\Controller::redirect("/manager/media");
  });

/*
  //////////////////////////////////////////////////////////////////////////////
  //                              NEW PIECE OF NEWS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/media/new", "media", "edit", function() {
    CMSView::render(array(
        "template" => "private/piece_news_detail",
        "data" => array(),
        "page_navigation" => "media",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });
*/
  //////////////////////////////////////////////////////////////////////////////
  //                           NEW PIECE OF NEWS (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/media/new", "media", "edit", function() {
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
      \Singular\Controller::redirect("/manager/media/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/media/new");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                NEWS DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/media/:file", "media", "edit", function($file) {
    //$model = new LabelModel();
    //$all_labels = $model->get_all();

    $model = new MediaModel();
    $file_info = $model->find($file);

    $info = $model->get_all_filtered_by_language(array(
      "condition" => "media.parent = '$file'"
    ));

    //$info["all_labels"] = $all_labels;
    CMSView::render(array(
        "template" => "private/media",
        "data" => array(
          "results" => $info,
          "node" => $file_info
        ),
        "page_navigation" => "media",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

/*
  //////////////////////////////////////////////////////////////////////////////
  //                                NEWS UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/media/:piece/save", "media", "edit", function($piece) {
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

    \Singular\Controller::redirect("/manager/media/$piece");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                NEWS DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/media/:piece/delete", "media", "edit", function($piece) {
    $model = new InformationModel();
    $model->delete($piece);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/media");
  });
*/
