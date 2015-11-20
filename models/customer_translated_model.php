<?php

class TranslatedModel extends CustomerRelatedModel {
  protected $filtered_language_entity = "";

  public function get_all_filtered_by_language($condition = NULL) {
    $language = AppAuthentication::get_language();
    $data = $this->get_all($condition);

    $filtered_language_entity = $this->filtered_language_entity;

    for ($j = 0; $j < count($data); $j++) {
      $row = $data[$j];

      $translations = isset($row[$filtered_language_entity]) ?
                        $row[$filtered_language_entity] : array();

      $filtered = NULL;

      for ($i = 0; $i < count($translations); $i++) {
        $translation = $translations[$i];
        $translation_language = isset($translation["language"])
                                  ? $translation["language"] : NULL;

        if ($language === $translation_language) {
          $filtered = $translation;
          break;
        }
      }

      $row[$filtered_language_entity] = array();

      if (!empty($filtered)) {
        array_push($row[$filtered_language_entity], $filtered);
      }

      $data[$j] = $row;
    }

    return $data;
  }
}
