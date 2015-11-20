<?php

class UserModel extends CustomerRelatedModel {
      protected $table = "users";
      protected $query_fields = array("id", "name", "surname", "email", "customer_id", "role");
      protected $order = array("name ASC");
      protected $filter = NULL;
      protected $search_fields = array("name", "surname", "email");

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
        "surname" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        ),
        "email" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        ),
        "password" => array(
          "type" => "string",
          "size" => 50,
          "null" => FALSE
        ),
        "customer_id" => array(
          "type" => "integer",
          "null" => FALSE
        ),
        "role" => array(
          "type" => "string",
          "size" => 30
        )
      );

      protected $primary_key = "id";

      public function process($data) {
        return $data;
      }

      public function validate_user($user, $password) {
        $this->filter = "1=1";
        $user_info = $this->get_all("email = '$user' AND password = '$password'");

        $data = count($user_info) > 0 ? $user_info[0] : NULL;

        if (!empty($data)) {
          $data = isset($data[$this->table]) ? $data[$this->table] : $data;
        }

        return $data;
      }
}
