<?php
  class AppHelpers {
    public static function get_helpers() {
      return array(
        // Compares one value to another
        'select_equals' => function ($args) {
          return $args[0] == $args[1] ? "selected" : "";
        },
        'selected_language' => function ($args) {
          return $args[0] == $args[1] ? "active-language" : "";
        },
        'language_name' => function ($args) {
          $labels = $args[0];
          $language = $args[1];
          return isset($labels[$language]) ? $labels[$language] : $language;
        },
        'equals' => function ($args) {
          return $args[0] == $args[1] ? $args[2] : "";
        },
        'get_translation' => function ($args) {
          $list = $args[0];
          $language = $args[1];
          $key = $args[2];

          if (empty($list)) {
            return "";
          }

          foreach ($list as $item) {
            $item_language = isset($item["language"]) ? $item["language"] : NULL;
            $item_value = isset($item[$key]) ? $item[$key] : NULL;

            if ($item_language == $language) {
              return $item_value;
            }
          }

          return "";
        }
      );
    }
  }
