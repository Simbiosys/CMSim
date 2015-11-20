<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PUBLIC ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  $model = new PageModel();
  $user_pages = $model->get_all();

  foreach ($user_pages as $page) {
    $info = $page["pages"];
    $translations = $page["page_translations"];
    $id = $info["id"];

    if ($translations) {
      foreach ($translations as $translation) {
        $path = $translation["path"];
        $language = $translation["language"];

        \Singular\Controller::get_public("/$language$path", function() use ($path, $language, $id) {
          AppAuthentication::set_language($language);
          $model = new PageModel();

          CMSView::render(array(
              "template" => "public/page",
              "data" => $model->find($id),
              "page_navigation" => $path,
              "layout" => "public.hbs"
          ));
        });
      }
    }
  }

  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                   PAGES
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pages(/page/:page)", "pages", "list", function($page = 1) {
    $model = new PageModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/pages",
        "data" => array (
          "results" => $model->get_all_filtered_by_language($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "pages",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pages/search(/page/:page)", "pages", "list", function($page = 1) {
    $model = new PageModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/pages",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "pages",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                 NEW PAGE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pages/new", "pages", "edit", function() {
    CMSView::render(array(
        "template" => "private/page_detail",
        "data" => array(),
        "page_navigation" => "pages",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                             NEW PAGE (POST)
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/pages/new", "pages", "edit", function() {
    $data = \Singular\Controller::get_data_by_entity("pages");
    $customer = AppAuthentication::get_user_customer();
    $data["pages"]["customer_id"] = $customer;

    $model = new PageModel();

    $result = $model->create($data);
    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("new_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/pages/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/pages/new");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                PAGE DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pages/:page", "pages", "edit", function($page) {
    $model = new PageModel();

    CMSView::render(array(
        "template" => "private/page_detail",
        "data" => $model->find($page),
        "page_navigation" => "pages",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "web" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                PAGE UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/pages/:page/save", "pages", "edit", function($page) {
    $data = \Singular\Controller::get_data_by_entity("pages");
    $customer = AppAuthentication::get_user_customer();
    $data["pages"]["customer_id"] = $customer;

    $model = new PageModel();
    $model->update($page, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/pages/$page");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                PAGE DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/pages/:page/delete", "pages", "edit", function($page) {
    $model = new PageModel();
    $model->delete($page);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/pages");
  });
