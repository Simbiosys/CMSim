<?php

  class SettingsModel extends TranslatedModel {
      protected $table = "settings";
      protected $query_fields = array("id", "customer_id", "creation", "km", "resort_name", "map_latitude", "map_longitude", "map_zoom");
      protected $order = array("creation ASC");
      protected $filter = NULL;
      protected $search_fields = array("setting_translations.title", "setting_translations.content");

      protected $filtered_language_entity = "setting_translations";

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
        "km" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "resort_name" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "map_latitude" => array(
          "type" => "double",
          "null" => TRUE
        ),
        "map_longitude" => array(
          "type" => "double",
          "null" => TRUE
        ),
        "map_zoom" => array(
          "type" => "integer",
          "null" => TRUE
        )
      );

      protected $primary_key = "id";

      protected $dependencies = array(
        "setting_translations" => array(
          "entity" => "SettingModelTranslations",
          "key" => "setting_id",
          "filter" => NULL,
          "order" => "",
          "dependent" => TRUE // Cascade delete
        ),
        "setting_labels" => array(
          "entity" => "SettingModelLabels",
          "key" => "setting_id",
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

  class SettingModelTranslations extends \Singular\Model {
    protected $table = "setting_translations";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "setting_id" => array(
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

  class SettingModelLabels extends \Singular\Model {
    protected $table = "setting_labels";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "setting_id" => array(
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
