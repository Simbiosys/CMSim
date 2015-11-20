<?php

  class PoisModel extends TranslatedLabelledModel {
      protected $table = "pois";
      protected $query_fields = array("id", "customer_id", "creation", "tipo", "coordinates", "url_info", "timeofyear", "phone", "estacio_propietaria");
      protected $order = array("creation ASC");
      protected $filter = NULL;
      protected $search_fields = array("poi_translations.title", "poi_translations.content");

      protected $filtered_language_entity = "poi_translations";

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
        "phone" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "estacio_propietaria" => array(
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
        "poi_translations" => array(
          "entity" => "PoiModelTranslations",
          "key" => "poi_id",
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

  class PoiModelTranslations extends \Singular\Model {
    protected $table = "poi_translations";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "poi_id" => array(
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
