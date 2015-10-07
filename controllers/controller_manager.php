<?php
  //////////////////////////////////////////////////////////////////////////////
  //                                   ROOT
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager", "manager", "enter", function() {
    // Redirect to user's role default path
    redirect_when_logged();
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                 CUSTOMERS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/customers", "customers", "list", function() {
    \Singular\View::render(array(
        "template" => "customers"
    ));
  });

  //////////////////////////////////////////////////////////////////////////////
  //                                   USERS
  //////////////////////////////////////////////////////////////////////////////
  \Singular\Controller::get_private("/manager/users", "users", "list", function() {
    \Singular\View::render(array(
        "template" => "users"
    ));
  });
