<?php

class PageModel extends TranslatedLabelledModel {
      protected $table = "pages";
      protected $query_fields = array("id", "template", "visibility", "customer_id");
      protected $order = array("creation ASC");
      protected $filter = NULL;
      protected $search_fields = array("page_translations.title", "page_translations.content");

      protected $filtered_language_entity = "page_translations";

      protected $fields = array(
        "id" => array(
          "type" => "integer",
          "null" => FALSE,
          "auto_increment" => TRUE
        ),
        "template" => array(
          "type" => "string",
          "size" => 200,
          "null" => FALSE
        ),
        "visibility" => array(
          "type" => "string",
          "size" => 20,
          "null" => FALSE
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
        "page_translations" => array(
          "entity" => "PageModelTranslations",
          "key" => "page_id",
          "filter" => NULL,
          "order" => "",
          "dependent" => TRUE // Cascade delete
        )
      );

      public function process($data) {
        return $data;
      }
}

////////////////////////////////////////////////////////////////////////////////
//                              Translations
////////////////////////////////////////////////////////////////////////////////

  class PageModelTranslations extends \Singular\Model {
    protected $table = "page_translations";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "page_id" => array(
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
      "path" => array(
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
