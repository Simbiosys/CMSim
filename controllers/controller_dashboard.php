<?php

  //////////////////////////////////////////////////////////////////////////////
  //
  //                             PRIVATE ROUTES
  //
  //////////////////////////////////////////////////////////////////////////////

  //////////////////////////////////////////////////////////////////////////////
  //                                   DASHBOARD
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/dashboard", "dashboard", "view", function() {
    CMSView::render(array(
        "template" => "private/dashboard",
        "data" => array (
        ),
        "page_navigation" => "dashboard",
        "layout" => "private.hbs",
        "extra" => array(
          "top" => array( "dashboard" => TRUE )
        )
    ));
  });
