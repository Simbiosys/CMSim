<?php

  class InformationModel extends TranslatedLabelledModel {
      protected $table = "informations";
      protected $query_fields = array("id", "customer_id", "creation");
      protected $order = array("creation ASC");
      protected $filter = NULL;
      protected $search_fields = array("information_translations.title", "information_translations.content", "information_labels.label_translations.name");

      protected $filtered_language_entity = "information_translations";

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
        "creation" => array(
          "type" => "timestamp",
          "null" => FALSE,
          "default" => "CURRENT_TIMESTAMP"
        )
      );

      protected $primary_key = "id";

      protected $dependencies = array(
        "information_translations" => array(
          "entity" => "InformationModelTranslations",
          "key" => "information_id",
          "filter" => NULL,
          "order" => "",
          "dependent" => TRUE // Cascade delete
        ),
        "information_labels" => array(
          "entity" => "InformationModelLabels",
          "key" => "information_id",
          "filter" => NULL,
          "order" => "",
          "dependent" => TRUE // Cascade delete
        )
      );

      public function process($data) {
        if (!empty($data["title"])) {
          $title = strtolower($data["title"]);
          $title = str_replace(array(" ", ",", "."), "-", $title);
          $title = preg_replace('/\-+/', "-", $title);

          $data["path"] = $title;
        }

        return $data;
      }

      protected function get_search_results($condition = NULL) {
        return $this->get_all($condition);
      }
  }

////////////////////////////////////////////////////////////////////////////////
//                              Translations
////////////////////////////////////////////////////////////////////////////////

  class InformationModelTranslations extends \Singular\Model {
    protected $table = "information_translations";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "information_id" => array(
        "type" => "integer",
        "null" => FALSE
      ),
      "title" => array(
        "type" => "string",
        "size" => 200,
        "null" => FALSE
      ),
      "content" => array(
        "type" => "binary"
      ),
      "language" => array(
        "type" => "string",
        "size" => "3"
      )
    );

    protected $primary_key = "id";
  }

////////////////////////////////////////////////////////////////////////////////
//                                 Labels
////////////////////////////////////////////////////////////////////////////////

  class InformationModelLabels extends \Singular\Model {
    protected $table = "information_labels";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "information_id" => array(
        "type" => "integer",
        "null" => FALSE
      ),
      "label_id" => array(
        "type" => "integer",
        "null" => FALSE
      )
    );

    protected $dependencies = array(
      "label_translations" => array(
        "entity" => "LabelModelTranslations",
        "key" => "label_id",
        "key_parent" => "label_id",
        "filter" => NULL,
        "order" => "",
        "dependent" => FALSE // Cascade delete
      )
    );

    protected $primary_key = "id";
  }
