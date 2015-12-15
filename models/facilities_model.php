<?php

  class FacilitiesModel extends TranslatedLabelledModel {
      protected $table = "facilities";
      protected $query_fields = array("id", "customer_id", "creation", "tipo", "coordinates", "url_info", "timeofyear");
      protected $order = array("creation ASC");
      protected $filter = NULL;
      protected $search_fields = array("facility_translations.title", "facility_translations.content");

      protected $filtered_language_entity = "facility_translations";

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
          "type" => "string",
          "size" => 400,
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
        "creation" => array(
          "type" => "timestamp",
          "null" => FALSE,
          "default" => "CURRENT_TIMESTAMP"
        )
      );

      protected $primary_key = "id";

      protected $dependencies = array(
        "facility_translations" => array(
          "entity" => "FacilityModelTranslations",
          "key" => "facility_id",
          "filter" => NULL,
          "order" => "",
          "dependent" => TRUE // Cascade delete
        ),
        "facility_labels" => array(
          "entity" => "FacilityModelLabels",
          "key" => "facility_id",
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

  class FacilityModelTranslations extends \Singular\Model {
    protected $table = "facility_translations";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "facility_id" => array(
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

  class FacilityModelLabels extends \Singular\Model {
    protected $table = "facility_labels";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "facility_id" => array(
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
