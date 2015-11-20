<?php

  class GeoModel extends TranslatedLabelledModel {
      protected $table = "geos";
      protected $query_fields = array("id", "customer_id", "name", "creation");
      protected $order = array("creation ASC");
      protected $filter = NULL;
      protected $search_fields = array("geo_translations.title", "geo_translations.content");

      protected $filtered_language_entity = "geo_translations";

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
        "name" => array(
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
          "entity" => "GeoModelTranslations",
          "key" => "geo_id",
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

  class GeoModelTranslations extends \Singular\Model {
    protected $table = "geo_translations";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "geo_id" => array(
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
