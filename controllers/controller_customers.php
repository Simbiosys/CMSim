<?php
  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                   CUSTOMERS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/customers(/page/:page)", "customers", "list", function($page = 1) {
    $model = new CustomerModel();
    $pagination = get_pagination($page);

    $count = $model->number($pagination["condition"]);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/customers",
        "data" => array (
          "results" => $model->get_all($pagination),
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "customers",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "customers" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   SEARCH
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/customers/search(/page/:page)", "customers", "list", function($page = 1) {
    $model = new CustomerModel();
    $pagination = get_pagination($page);
    $search = \Singular\Controller::get_get_variable("s");

    $count = $model->number_search($search);
    $next = $pagination["next"];

    CMSView::render(array(
        "template" => "private/customers",
        "data" => array(
          "results" => $model->search($search, $pagination),
          "search" => $search,
          "page" => $pagination["page"],
          "previous" => $pagination["page"] > 1 ? $pagination["page"] - 1 : NULL,
          "next" => $next < $count ? $pagination["page"] + 1 : NULL
        ),
        "page_navigation" => "customers",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "customers" => TRUE )
        )
    ));
  });


  //////////////////////////////////////////////////////////////////////////////
  //                                 NEW CUSTOMER
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/customers/new", "customers", "edit", function() {
    CMSView::render(array(
        "template" => "private/customer_detail",
        "data" => array(),
        "page_navigation" => "customers",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "customers" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                             NEW CUSTOMER (POST)
  //////////////////////////////////////////////////////////////////////////////

  \Singular\Controller::post_private("/manager/customers/new", "customers", "edit", function() {
    $model = new CustomerModel();

    $result = $model->create(array(
      "customers" => array(
        "name" => \Singular\Controller::get_post_variable("users.name")
      )
    ));

    $main_result = isset($result["main"]) ? $result["main"] : NULL;

    if ($main_result["error"] == FALSE) {
      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("new_saved_successfully"),
        "code" => "success"
      ));

      $id = $main_result["message"];
      \Singular\Controller::redirect("/manager/customers/$id");
    }
    else {
      $message = $main_result["message"];

      \Singular\Controller::flash(array(
        "message" => CMSView::get_label("an_error_occurred"),
        "code" => "error"
      ));

      \Singular\Controller::redirect("/manager/customers/new");
    }
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                CUSTOMER DETAIL
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/customers/:customer", "customers", "edit", function($customer) {
    $model = new CustomerModel();

    CMSView::render(array(
        "template" => "private/customer_detail",
        "data" => $model->find($customer),
        "page_navigation" => "customers",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "customers" => TRUE )
        )
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                CUSTOMER UPDATE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::post_private("/manager/customers/:customer/save", "customers", "edit", function($customer) {
    $data = \Singular\Controller::get_post();

    $model = new CustomerModel();

    $model->update($customer, $data);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_saved_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/customers/$customer");
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                CUSTOMER DELETE
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/customers/:customer/delete", "customers", "edit", function($customer) {
    $model = new CustomerModel();
    $model->delete($customer);

    \Singular\Controller::flash(array(
      "message" => CMSView::get_label("new_deleted_successfully"),
      "code" => "success"
    ));

    \Singular\Controller::redirect("/manager/customers");
  });
