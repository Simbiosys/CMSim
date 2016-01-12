<?php

  class PoisModel extends TranslatedLabelledModel {
      protected $table = "pois";
      protected $query_fields = array("id", "customer_id", "identifier", "creation", "coordinates", "url_info", "time_of_year", "element_type", "geometry_type", "coordinates", "deletion", "deleted", "imported");
      protected $order = array("creation DESC");
      protected $filter = NULL;
      protected $search_fields = array("poi_translations.name", "poi_translations.description");
      protected $audit = TRUE;

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
        "identifier" => array(
          "type" => "string",
          "size" => 40,
          "null" => FALSE
        ),
        "element_type" => array(
          "type" => "integer",
          "null" => FALSE
        ),
        "time_of_year" => array(
          "type" => "string",
          "size" => 40,
          "null" => TRUE
        ),
        "url_info" => array(
          "type" => "string",
          "size" => 400,
          "null" => TRUE
        ),
        "geometry_type" => array(
          "type" => "string",
          "size" => 20,
          "null" => FALSE
        ),
        "coordinates" => array(
          "type" => "string",
          "size" => 400,
          "null" => FALSE
        ),
        "imported" => array(
          "type" => "boolean",
          "default" => 0
        )
      );

      protected $primary_key = "id";

      protected $dependencies = array(
        "poi_translations" => array(
          "entity" => "PoiModelTranslations",
          "key" => "poi_id",
          "filter" => NULL,
          "deletion" => "1 = 1", // Get deleted
          "order" => "",
          "dependent" => TRUE // Cascade delete
        ),
        "poi_labels" => array(
          "entity" => "PoiModelLabels",
          "key" => "poi_id",
          "filter" => NULL,
          "deletion" => "1 = 1", // Get deleted
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

      public function get_all_with_deleted($condition) {
        $deletion = $this->deletion;
        $this->deletion = "1 = 1"; // Change default deletion behaviour to get all items (also deleted ones)

        $data = $this->get_all($condition);
        $this->deletion = $deletion;

        return $data;
      }

      public function find_with_deleted($id) {
        $deletion = $this->deletion;
        $this->deletion = "1 = 1"; // Change default deletion behaviour to get all items (also deleted ones)

        $data = $this->find($id);
        $this->deletion = $deletion;

        return $data;
      }

      public function import($file) {
        $labels = CMSView::get_labels();
        $obj = json_decode($file, TRUE);

        if (!$obj) {
          return array(
            "success" => FALSE,
            "message" => $labels["bad_file"]
          );
        }

        if (!array_key_exists("type", $obj) || $obj["type"] != "FeatureCollection") {
          return array(
            "success" => FALSE,
            "message" => $labels["missing_type"]
          );
        }

        if (!array_key_exists("features", $obj)) {
          return array(
            "success" => FALSE,
            "message" => $labels["missing_features"]
          );
        }

        $results = array();

        $all_pois = $this->get_all();
        $all_pois_objects = array();
        $all_pois_ids = array();

        for ($i = 0; $i < count($all_pois); $i++) {
          $poi = $all_pois[$i];

          if (isset($poi["pois"])) {
            $identifier = $poi["pois"]["identifier"];
            $all_pois_ids[$identifier] = FALSE;
            $all_pois_objects[$identifier] = $poi;
          }
        }

        // Find customer importer
        $customer_id = AppAuthentication::get_user_customer();
        $model = new CustomerModel();
        $customer = $model->find($customer_id);

        $importer_class = NULL;

        if ($customer) {
          $customer_identifier = $customer["customers"]["identifier"];

          if ($customer_identifier) {
            $importer_name = $customer_identifier . "_importer.php";
            $root = \Singular\Configuration::get_root();
            $importer_path = "$root/customer/importers/$importer_name";

            if (file_exists($importer_path)) {
              include_once($importer_path);
              $importer_class = ucfirst($customer_identifier) . "_importer";
            }
          }
        }

        if ($importer_class) {
          $obj = call_user_func(array($importer_class, 'format'), $obj);
        }

        $features = $obj["features"];

        foreach ($features as $feature) {
          $type = $feature["type"];

          $geometry = $feature["geometry"];
          $geometry_type = $geometry["type"];
          $geometry_coordinates = $geometry["coordinates"];

          $properties = $feature["properties"];
          $identifier = $properties["id"];
          $element_type = $properties["element_type"];
          $time_of_year = $properties["time_of_year"];
          $url_info = $properties["url_info"];
          $names = $properties["name"];
          $descriptions = $properties["description"];

          $status = array(
            "created" => FALSE,
            "deleted" => FALSE,
            "updated" => FALSE,
            "equal" => FALSE
          );

          $all_pois_ids[$identifier] = TRUE;

          $translations = array();

          foreach ($names as $language => $name) {
            $description = array_key_exists($language, $descriptions) ? $descriptions[$language] : "";

            array_push($translations, array(
              "name" => $name,
              "description" => $description,
              "language" => $language
            ));
          }

          if (strtolower($geometry_type) == "point") {
            $geometry_coordinates = array(array($geometry_coordinates));
          }
          else if (strtolower($geometry_type) == "linestring") {
            $geometry_coordinates = array($geometry_coordinates);
          }

          $existing = $this->get_all("identifier = '$identifier' AND deleted = 0");
          $updated = FALSE;

          // To store element's labels
          $labels = array();

          if (count($existing) == 0) {
            $status["created"] = TRUE;
          }
          else {
            $updated = TRUE;

            foreach ($existing as $element) {
              if (!isset($element["pois"])) {
                continue;
              }

              // We need to insert existing labels into new object
              if ($element["poi_labels"]) {
                foreach ($element["poi_labels"] as $label) {
                  array_push($labels, array(
                    "label_id" => $label["label_id"])
                  );
                }
              }

              $id = $element["pois"]["id"];
              $this->delete($id);
            }
          }

          // Element type

          $model = new LabelModel();
          $existing_labels = $model->get_all("identifier = '$element_type' AND deleted = 0");
          $languages = \Singular\Configuration::get_available_languages();
          $type_id = NULL;

          $label_translations = array();

          for ($i = 0; $i < count($languages); $i++) {
            $language = $languages[$i];

            array_push($label_translations, array(
              "name" => $element_type,
              "description" => $element_type,
              "language" => $language
            ));
          }

          if (count($existing_labels) == 0) {
            $inserted = $model->create(array(
              "labels" => array(
                "identifier" => $element_type,
                "parent_id" => 0,
                "customer_id" => $customer_id
              ),
              "label_translations" => $label_translations
            ));

            $type_id = $inserted["main"]["message"];
          }
          else {
            $first = $existing_labels[0];
            $type_id = $first["labels"]["id"];
          }

          $new_poi = array(
            "pois" => array(
              "customer_id" => $customer_id,
              "identifier" => $identifier,
              "element_type" => $type_id,
              "time_of_year" => $time_of_year,
              "url_info" => $url_info,
              "geometry_type" => $geometry_type,
              "imported" => 1,
              "coordinates" => json_encode($geometry_coordinates)
            ),
            "poi_translations" => $translations
          );

          if (count($labels) > 0) {
            $new_poi["poi_labels"] = $labels;
          }

          $inserted = $this->create($new_poi);

          // Check if updated

          if ($updated) {
            foreach ($existing as $element) {
              if (!isset($element["pois"])) {
                continue;
              }

              $fields = $element["pois"];
              $fields_to_exclude = array("id", "creation", "customer_id", "deleted");

              $equal = TRUE;

              foreach ($fields as $field_name => $field_value) {
                if (in_array($field_name, $fields_to_exclude)) {
                  continue;
                }

                $new_field_value = $new_poi["pois"][$field_name];

                if ($field_value != $new_field_value) {
                  $equal = FALSE;
                  break;
                }
              }

              $translations = $element["poi_translations"];
              $translations_by_language = array();

              foreach ($translations as $translation) {
                $language = $translation["language"];

                $translations_by_language[$language] = $translation;
              }

              $new_translations = $new_poi["poi_translations"];
              $fields_to_check = array("name", "description");

              foreach ($new_translations as $new_translation) {
                $language = $new_translation["language"];
                $translation = $translations_by_language[$language];

                foreach ($fields_to_check as $field) {
                  if ($new_translation[$field] != $translation[$field]) {
                    $equal = FALSE;
                    break;
                  }
                }
              }
            }

            if ($equal) {
              $status["equal"] = TRUE;
            }
            else {
              $status["updated"] = TRUE;
            }
          }

          array_push($results, array(
            "id" => $inserted["main"]["message"],
            "type" => $type,
            "geometry_type" => $geometry_type,
            "identifier" => $identifier,
            "element_type" => $type_id,
            "status" => $status
          ));
        }

        // To delete
        $to_delete = array();

        foreach ($all_pois_ids as $poi => $value) {
          if (!$value) {
            $poi_data = $all_pois_objects[$poi]["pois"];
            $poi_data["status"] = array(
              "deleted" => TRUE
            );

            array_push($to_delete, $poi_data["id"]);
            array_push($results, $poi_data);
          }
        }

        // Labels
        $model = new LabelModel();
        $all_labels = $model->get_all();

        $return_data = array(
          "success" => TRUE,
          "message" => $labels["import_successfully"],
          "results" => $results,
          "results_size" => count($results),
          "all_labels" => $all_labels
        );

        if (count($to_delete) > 0) {
          $return_data["to_delete"] = $to_delete;
          $return_data["to_delete_size"] = count($to_delete);
        }

        return $return_data;
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

////////////////////////////////////////////////////////////////////////////////
//                              Coordinates
////////////////////////////////////////////////////////////////////////////////
/*
  class PoiModelCoordinates extends \Singular\Model {
    protected $table = "poi_coordinates";

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
      "latitude" => array(
        "type" => "double",
        "null" => FALSE
      ),
      "longitude" => array(
        "type" => "double",
        "null" => FALSE
      ),
      "sort" => array(
        "type" => "integer",
        "null" => FALSE
      )
    );

    protected $primary_key = "id";
  }
*/
////////////////////////////////////////////////////////////////////////////////
//                                 Labels
////////////////////////////////////////////////////////////////////////////////

  class PoiModelLabels extends \Singular\Model {
    protected $table = "poi_labels";

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

  ////////////////////////////////////////////////////////////////////////////////
  //                                 POI type
  ////////////////////////////////////////////////////////////////////////////////
/*
    class PoiModelTypes extends \Singular\Model {
      protected $table = "poi_types";

      protected $fields = array(
        "id" => array(
          "type" => "integer",
          "null" => FALSE,
          "auto_increment" => TRUE
        ),
        "identifier" => array(
          "type" => "string",
          "size" => 40,
          "null" => FALSE
        )
      );

      protected $dependencies = array(
        "poi_type_translations" => array(
          "entity" => "PoiModelTypeTranslations",
          "key" => "poi_id",
          "filter" => NULL,
          "order" => "",
          "dependent" => FALSE // Cascade delete
        )
      );

      protected $primary_key = "id";
    }

    ////////////////////////////////////////////////////////////////////////////////
    //                              POI Type Translations
    ////////////////////////////////////////////////////////////////////////////////

      class PoiModelTypeTranslations extends \Singular\Model {
        protected $table = "poi_type_translations";

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
          "name" => array(
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
*/
