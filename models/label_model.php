<?php

  class LabelModel extends TranslatedLabelledModel {
      protected $table = "labels";
      protected $query_fields = array("id", "customer_id", "creation", "parent_id", "identifier");
      protected $order = array("creation ASC");
      protected $filter = NULL;
      protected $search_fields = array("label_translations.name");

      protected $filtered_language_entity = "label_translations";

      protected $fields = array(
        "id" => array(
          "type" => "integer",
          "null" => FALSE,
          "auto_increment" => TRUE
        ),
        "customer_id" => array(
          "type" => "integer",
          "null" => FALSE
        ),
        "parent_id" => array(
          "type" => "integer",
          "null" => TRUE
        ),
        "identifier" => array(
          "type" => "string",
          "null" => FALSE,
          "size" => 50
        ),
        "creation" => array(
          "type" => "timestamp",
          "null" => FALSE,
          "default" => "CURRENT_TIMESTAMP"
        )
      );

      protected $primary_key = "id";

      protected $dependencies = array(
        "label_translations" => array(
          "entity" => "LabelModelTranslations",
          "key" => "label_id",
          "filter" => NULL,
          "order" => "",
          "dependent" => TRUE // Cascade delete
        )
      );

      public function process($data) {
        return $data;
      }

      protected function get_search_results($condition = NULL) {
        return $this->get_all($condition);
      }
  }

////////////////////////////////////////////////////////////////////////////////
//                              Translations
////////////////////////////////////////////////////////////////////////////////

  class LabelModelTranslations extends \Singular\Model {
    protected $table = "label_translations";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "label_id" => array(
        "type" => "integer",
        "null" => FALSE
      ),
      "name" => array(
        "type" => "string",
        "size" => 200,
        "null" => FALSE
      ),
      "description" => array(
        "type" => "string",
        "size" => 200,
        "null" => FALSE
      ),
      "language" => array(
        "type" => "string",
        "size" => "3"
      )
    );

    protected $primary_key = "id";
  }
