<?php

class CustomerModel extends \Singular\Model {
      protected $table = "customers";
      protected $query_fields = array("id", "name", "identifier");
      protected $order = array("name ASC");
      protected $filter = NULL;
      protected $search_fields = array("name");

      protected $fields = array(
        "id" => array(
          "type" => "integer",
          "null" => FALSE,
          "auto_increment" => TRUE
        ),
        "name" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        ),
        "identifier" => array(
          "type" => "string",
          "size" => 20,
          "null" => FALSE
        )
      );

      protected $primary_key = "id";

      protected function init() {

      }

      public function process($data) {
        return $data;
      }
}
