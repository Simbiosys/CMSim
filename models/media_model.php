<?php

  class MediaModel extends TranslatedLabelledModel {
      protected $table = "media";
      protected $query_fields = array("*");
      protected $order = array("creation ASC");
      protected $filter = NULL;
      protected $search_fields = array("title", "media_translations.title", "media_translations.label_translations.name");

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
        ),
        "file_path" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        ),
        "title" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        ),
        "external" => array(
          "type" => "boolean",
          "null" => FALSE
        ),
        "parent" => array(
          "type" => "integer",
          "null" => TRUE
        ),
        "is_folder" => array(
          "type" => "boolean",
          "null" => FALSE
        ),
        "extension" => array(
          "type" => "string",
          "size" => 4,
          "null" => TRUE
        )
      );

      protected $primary_key = "id";

      protected $dependencies = array(
        "media_labels" => array(
          "entity" => "MediaModelLabels",
          "key" => "media_id",
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
//                                 Labels
////////////////////////////////////////////////////////////////////////////////

  class MediaModelLabels extends \Singular\Model {
    protected $table = "media_labels";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "media_id" => array(
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
