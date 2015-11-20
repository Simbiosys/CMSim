<?php

  class NoPoisModel extends TranslatedLabelledModel {
      protected $table = "nopois";
      protected $query_fields = array("id", "customer_id",  "tipo", "coordinates", "url_info", "timeofyear", "resort_reference", "occupancy", "capacity", "duration", "heating", "creation");
      protected $order = array("creation ASC");
      protected $filter = NULL;
      protected $search_fields = array("nopoi_translations.title", "nopoi_translations.content");

      protected $filtered_language_entity = "nopoi_translations";

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
        "tipo" => array(
          "type" => "integer",
          "null" => FALSE
        ),
        "coordinates" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "url_info" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "timeofyear" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "resort_reference" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "occupancy" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "capacity" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "duration" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "heating" => array(
          "type" => "string",
          "size" => 400,
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
        "nopoi_translations" => array(
          "entity" => "NoPoiModelTranslations",
          "key" => "nopoi_id",
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

  class NoPoiModelTranslations extends \Singular\Model {
    protected $table = "nopoi_translations";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "nopoi_id" => array(
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
