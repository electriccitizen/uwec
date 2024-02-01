<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_row_paragraphs"
 * )
 */
class CmRowParagraphs extends \Drupal\citizen_migrate\Plugin\migrate\CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    switch ($value['row_id']) {
      // Text rows.
      case 1:
        // Set the destination bundle.
        $bundle = 'text';
//        $row->setSourceProperty(
//          'field_long_text',
//          $value['standard_rte']['content']
//        );
        $this->cmTools->logToConsole($value);
        break;

      case 21:
        // Set the destination bundle.
        $bundle = 'text';
        $row->setSourceProperty(
          'field_long_text',
          $value['tabular_rte']['content']
        );
        break;

      case 54:
        // Set the destination bundle.
        $bundle = 'text';
        foreach ($value['featured_copy'] as $key => $value) {
          if ($key == 'title') {
            $row->setSourceProperty('field_widget_title', $value);
          }
          if ($key == 'content') {
            $row->setSourceProperty('field_long_text', $value);
          }
        }
        break;

      case 22:
      case 23:
        // Set the destination bundle.
        $bundle = 'text';
        $i = 1;
        $content = '';
        foreach ($value as $key => $val) {
          if (str_contains($key, 'standard')) {
            $content .= $val;
          }
          $i++;
        }
        $row->setSourceProperty('field_long_text', $content);
        break;

      // Accordion Item.*
      case 3:
        // Set the destination bundle.
        $bundle = 'accordion_item';
        foreach ($value['accordion'] as $key => $value) {
          if ($key == 'title') {
            $row->setSourceProperty('field_accordion_header', $value);
          }
          if ($key == 'content') {
            $row->setSourceProperty('field_long_text', $value);
          }
        }
        break;

      // Image gallery.*
//      case 13://Verify before removing
//        // Set the destination bundle.
//        $bundle = 'gallery';
//        foreach ($value['media_gallery'] as $key => $value) {
//          $this->cmTools->logToConsole($value);
//        }
//        break;

      case 47:
        // Set the destination bundle.
        $bundle = 'gallery';
        foreach ($value['gallery'] as $key => $value) {
          if ($key == 'photos') {
            $image_data = '';
            foreach ($value['val'] as $fieldName => $fieldValue) {
              $this->cmTools->logToConsole("$fieldName: $fieldValue");
            }
            continue;
          }
        }
        break;

      // Testimonial. (link to content type)
      // Possibly this is a pull-quote. See page ID 1821.
      // NOTE: According to documentation, these references are not migrating.
//      case 18:
//        // Set the destination bundle.
//        $bundle = 'testimonial_placer';
//        $data['row_name'] = 'testimonial';
//        $data['log_file'] = 'para_testimonial.csv';
//        $data['testimonial_id'] = $value['testimonial']['testimonial_id'];
//        break;

      // NOTE: According to documentation, these references are not migrating.
//      case 8:
//        // Set the destination bundle.
//        $bundle = 'hours_placer';
//        $data['row_name'] = 'hours';
//        $data['log_file'] = 'para_hours.csv';
//        foreach ($value['hours']['hours_ids'] as $key => $value) {
//          $data["hours_lib_id_$key"] = $value['val'];
//        }
//        break;

      case 41:
        // Set the destination bundle.
        $bundle = 'social_media_links';
//        $data['unit_id'] = $value['social']['unit_id'];
        break;

      case 25:
        // Set the destination bundle.
        $bundle = 'custom_embed';
        $row->setSourceProperty('field_widget_title', $value['stackla_widget']['title']);
        $row->setSourceProperty('field_embed_content', $value['stackla_widget']['embed_code']);
        break;

      case 20:
        // Set the destination bundle.
        $bundle = 'links_files';
        foreach ($value['utility-bar']['resources'] as $key => $item) {
//          $data['log_file'] = "para_utility-bar_{$item['name']}_links.csv";
//          $data["title_$key"] = $item['val']['title'];
//          $data["{$item['name']}_$key"] = $item['val'][$item['name']];
        }
        break;

      case 26:
        // Set the destination bundle.
        $bundle = 'video';
//        $data['row_name'] = 'video';
//        $data['log_file'] = 'para_video.csv';
//        $data['video_id'] = $value['video']['video_id'];
        break;

      case 55:
        // Set the destination bundle.
        $bundle = 'faux_item';
        foreach ($value['faux_profile']['profiles'] as $key => $profile) {
          foreach ($profile['val'] as $fieldName => $fieldValue) {

          }
        }
        break;
    }
    $row->setDestinationProperty('type', $bundle);
  }


}