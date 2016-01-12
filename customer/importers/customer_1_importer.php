<?php
  class Customer_1_importer {
    public static function format($data) {
      $conversion = array(
        "/^(timeofyear)$/" => function($match, $value, &$ret) {
          $ret["time_of_year"] = $value;
        },
        "/^desc_([A-z]{2})$/" => function($matches, $value, &$ret) {
          $ret["description"][$matches[1]] = $value;
        },
        "/^name_([A-z]{2})$/" => function($matches, $value, &$ret) {
          $ret["name"][$matches[1]] = $value;
        }
      );

      $features = $data["features"];
      $processed_features = array();

      foreach ($features as $feature) {
        $properties = array(
          "id" => NULL,
          "element_type" => NULL,
          "name" => array(),
          "description" => array()
        );

        foreach ($feature["properties"] as $key => $value) {
          $found = FALSE;

          foreach ($conversion as $regex => $function) {

            if (preg_match($regex, $key, $matches) == 1) {
              $function($matches, $value, $properties);

              $found = TRUE;
              break;
            }
          }

          if (!$found) {
            $properties[$key] = $value;
          }
        }

        $feature["properties"] = $properties;
        array_push($processed_features, $feature);
      }

      $data["features"] = $processed_features;

      return $data;
    }
  }
