<?php

class UserModel extends \Singular\Model {
      protected static $table = "users";
      protected static $sql_query = "SELECT id, name, surname, email, customer_id, role
                                     FROM users";
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

      protected static $primary_key = "id";

      public function process($data) {
        return $data;
      }

      public function validate_user($user, $password) {
        $user_info = $this->get_all("email = '$user' AND password = '$password'");

        return count($user_info) > 0 ? $user_info[0] : NULL;
      }
}
