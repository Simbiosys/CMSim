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
        },
        'brief' => function ($args) {
          $text = $args[0];
          $count = isset($args[1]) ? $args[1] : "50";

          $pieces = explode(" ", $text);
          return implode(" ", array_splice($pieces, 0, $count));
        },
        'inc' => function ($args) {
          $value = $args[0];

          return intval($value) + 1;
        },
        'hightlight' => function ($args) {
          $text = $args[0];
          $terms = $args[1];
          $count = isset($args[2]) ? intval($args[2]) : 50;

          if ($terms) {
            preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $terms, $terms);
            $terms = $terms[0];
          }

          $pieces = explode(" ", $text);

          $found = 0;

          if ($terms) {
            foreach ($terms as $term) {
              $term = rtrim(ltrim($term, '"'), '"');

              for ($i = 0; $i < count($pieces); $i++) {
                $piece = $pieces[$i];

                if (strpos(strtolower($piece), strtolower($term)) !== FALSE) {
                  $found = $i;
                  break;
                }
              }
            }
          }

          if ((count($pieces) - $found) < $count) {
            $found = count($pieces) - $count;

            if ($found < 0) {
              $found = 0;
            }
          }

          $new_text = implode(" ", array_splice($pieces, $found, $count));

          if ($terms) {
            foreach ($terms as $term) {
              $term = rtrim(ltrim($term, '"'), '"');
              $new_text = str_ireplace($term, "<span class='term-highlight'>$term</span>", $new_text);
            }
          }

          return $new_text;
        }
      );
    }
  }
