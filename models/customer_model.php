<?php

class CustomerModel extends \Singular\Model {
      protected static $table = "customers";
      protected static $sql_query = "SELECT id, name
                                     FROM customers";
      protected static $order = "name ASC";

      protected static $fields = array(
        "id" => array(
          "type" => "integer",
          "null" => FALSE,
          "auto_increment" => TRUE
        ),
        "name" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        )
      );

      protected static $primary_key = "id";

      public function process($data) {
        return $data;
      }
}
