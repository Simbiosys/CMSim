<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                settings DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/settings", "settings", "edit", function() {
    $model = new LabelModel();
    $all_labels = $model->get_all();

    $model = new settingsModel();

    $pieces = $model->get_all();
    $piece = $pieces[0]["settings"]["id"];

    $info = $model->find($piece);

    $info["all_labels"] = $all_labels;

    CMSView::render(array(
        "template" => "private/piece_settings_detail",
        "data" => $info,
        "page_navigation" => "settings",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "resort" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                settings UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/settings/save", "settings", "edit", function() {
    $data = \Singular\Controller::get_post();
    $customer = AppAuthentication::get_user_customer();
    $data["settings"]["customer_id"] = $customer;

    $model = new settingsModel();

    $pieces = $model->get_all();
    $piece = $pieces[0]["id"];


    $model->update($piece, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("settings_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/settings");
  });
