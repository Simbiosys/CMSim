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
        ),
        "import_id" => array(
          "type" => "integer",
          "null" => TRUE
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

      public function get_all_from_last_import() {
        $model_imports = new PoiModelImports();
        $imports = $model_imports->get_all(array(
          "limit" => 1
        ));

        $last_import = NULL;
        $pois = array();

        if (!empty($imports) && count($imports) > 0) {
          $last_import = $imports[0]["poi_imports"];
        }

        if ($last_import) {
          $import_id = $last_import["id"];

          $pois = $this->get_all_with_deleted("import_id = '$import_id'");
        }

        $ids = array();

        for ($i = 0; $i < count($pois); $i++) {
          $id = $pois[$i]["pois"]["id"];
          $pois[$i]["status"] = $this->compare_with_previous($id, $pois[$i]);

          array_push($ids, $id);
        }

        $ids = implode(",", $ids);
        $other_pois = $this->get_all("id NOT IN ($ids)");

        return array(
          "import" => $last_import,
          "pois" => $pois,
          "other_pois" => $other_pois
        );
      }

      public function compare_with_previous($id, $item = NULL) {
        $comparison = array(
          "pois" => array(
            "multiple" => FALSE,
            "linking" => NULL,
            "exclude" => array(
              "id",
              "creation",
              "deletion",
              "updating",
              "deleted",
              "imported"
            )
          ),
          "poi_translations" => array(
            "multiple" => TRUE,
            "linking" => "language",
            "exclude" => array(
              "id",
              "creation",
              "deletion",
              "updating",
              "deleted",
              "imported",
              "poi_id"
            )
          )
        );

        $status = array(
          "previous" => NULL,
          "equal" => TRUE,
          "different" => array()
        );

        if (empty($item)) {
          $item = $this->find($id);

          if (empty($item)) {
            return $status;
          }
        }

        $identifier = $item["pois"]["identifier"];
        $previous = $this->get_all_with_deleted(array(
          "condition" => "identifier = '$identifier' AND id <> '$id'",
          "limit" => 1
        ));

        if (count($previous) == 0) {
          return $status;
        }

        $previous = $previous[0];
        $status["previous"] = $previous;

        $equal = TRUE;
        $different = array();

        foreach ($item as $entity => $data) {
          $comparison_options = $comparison[$entity];
          $multiple = $comparison_options["multiple"];
          $linking = $comparison_options["linking"];
          $exclude = $comparison_options["exclude"];

          $grouped = array();

          if (!$multiple) {
            $data = array(
              $data
            );
          }
          else {
            foreach ($previous[$entity] as $row) {
              $key = $row[$linking];
              $grouped[$key] = $row;
            }
          }

          foreach ($data as $row) {
            $compared = NULL;

            if (!$multiple) {
              $compared = $previous[$entity];
            }
            else {
              $key = $row[$linking];
              $compared = $grouped[$key];
            }

            foreach ($row as $key => $value) {
              if (in_array($key, $exclude)) {
                continue;
              }

              $other_value = $compared[$key];

              if ($value != $other_value) {
                $equal = FALSE;

                if (!in_array($key, $different)) {
                  array_push($different, $key);
                }
              }
            }
          }
        }

        $status["equal"] = $equal;
        $status["different"] = $different;

        return $status;
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

        $import_model = new PoiModelImports();
        $import = $import_model->create();
        $import_id = $import["main"]["message"];

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

          if (count($existing) > 0) {
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
              "coordinates" => json_encode($geometry_coordinates),
              "import_id" => $import_id
            ),
            "poi_translations" => $translations
          );

          if (count($labels) > 0) {
            $new_poi["poi_labels"] = $labels;
          }

          $inserted = $this->create($new_poi);
        }

        // Labels
        $model = new LabelModel();

        $return_data = array(
          "success" => TRUE,
          "message" => $labels["import_successfully"],
        );
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
//                                 Import
////////////////////////////////////////////////////////////////////////////////

  class PoiModelImports extends \Singular\Model {
    protected $table = "poi_imports";
    protected $order = array("creation DESC");

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      )
    );

    protected $primary_key = "id";
  }
