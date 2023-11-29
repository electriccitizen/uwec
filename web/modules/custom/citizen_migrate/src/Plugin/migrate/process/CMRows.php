<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\citizen_migrate\Plugin\migrate\CMTraits;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\citizen_migrate\Plugin\migrate\CMProcess;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_rows"
 * )
 */
class CMRows extends CMProcess {

//  use CMTraits;

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if (!empty($value)) {
      // Get the storage object.
      /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
      $store = $this->cmTools->getStorage();
$this->cmTools->logToConsole($value);

      // The array of data elements to log post-rowSave.
      $uwec_row_data = [];


      foreach ($value as $id => $content_row) {
        $data = [];
        $bundle = '';
//        $this->cmTools->logToConsole([$id => $content_row]);
        $data['row_name'] = '';
        $data['src_id'] = '';
        $data['dest_bundle'] = '';
        $data['des_id'] = '';
        $data['url'] = $row->getSourceProperty('url');
        $data['page_title'] = $row->getSourceProperty('title');
        $data['data_row_id'] = $id;
        $data['row_id'] = $content_row['row_id'];

        switch ($content_row['row_id']) {
          // Text rows.
          case 1:
            // Set the destination bundle.
            $bundle = 'text';
            $this->cmTools->logToConsole($row->getIdMap());
            $data['row_name'] ='standard_rte';
            $data['log_file'] = 'para_standard_rte.csv';
            $data['content'] = $content_row['standard_rte']['content'];
            $row->setSourceProperty(
              'field_long_text',
              $content_row['standard_rte']['content']
            );
            break;

          case 21:
            // Set the destination bundle.
            $bundle = 'text';
            $data['row_name'] = 'tabular_rte';
            $data['log_file'] = 'para_tabular_rte.csv';
            $data['content'] = $content_row['tabular_rte']['content'];
            $row->setSourceProperty(
              'field_long_text',
              $content_row['tabular_rte']['content']
            );
            break;

          case 54:
            // Set the destination bundle.
            $bundle = 'text';
            $data['row_name'] = 'featured_copy';
            $data['log_file'] = 'para_featured_copy.csv';
            foreach ($content_row['featured_copy'] as $key => $value) {
              $data[$key] = $value;

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
            $data['row_name'] = $content_row['row_id'] == 22 ? 'content_box_2col' : 'content_box_3col';
            $data['log_file'] = $content_row['row_id'] == 22 ? 'para_cont-box2.csv' : 'para_cont-box3.csv';
            $i = 1;
            $content = '';
            foreach($content_row as $key => $value) {
              if (str_contains($key, 'standard')) {
                $data["standard_rte$i"] = $value;
                $content .= $value;
              }
              $i++;
            }
            $row->setSourceProperty('field_long_text', $content);
//            foreach($content_row["standard_rte$i"] as $key => $value) {
//              $data["standard_rte$i"] = $value;
//              $i++;
//            }
            break;

          // Accordion Item.*
          case 3:
            // Set the destination bundle.
            $bundle = 'accordion_item';
            $data['row_name'] ='accordion';
            $data['log_file'] = 'para_accordion.csv';
            foreach($content_row['accordion'] as $key => $value) {
              $data[$key] = $value;
              if($key == 'title') {
                $row->setSourceProperty('field_accordion_header', $value);
              }
              if($key == 'content') {
                $row->setSourceProperty('field_long_text', $value);
              }
            }
            break;

          // Image gallery.*
          case 13://Verify before removing
            // Set the destination bundle.
            $bundle = 'gallery';
            $data['row_name'] ='media_gallery';
            $data['log_file'] = 'para_media_gallery.csv';
            foreach($content_row['media_gallery'] as $key => $value) {
              $data[$key] = $value;
            }
            break;

          case 47:
            // Set the destination bundle.
            $bundle = 'gallery';
            $data['row_name'] ='gallery';
            $data['log_file'] = 'para_gallery.csv';
            foreach($content_row['gallery'] as $key => $value) {
              if ($key == 'photos') {
                $image_data = '';

                foreach ($value['val'] as $fieldName => $fieldValue) {
                  $data[$key] .= "$fieldName: $fieldValue | ";
                }
//                $data[$key] = $image_data;
                continue;
              }
              $data[$key] = $value;
            }
            break;

          // Testimonial. (link to content type)
          // Possibly this is a pull-quote. See page ID 1821.
          // NOTE: According to documentation, these references are not migrating.
          case 18:
            // Set the destination bundle.
            $bundle = 'testimonial_placer';
            $data['row_name'] ='testimonial';
            $data['log_file'] = 'para_testimonial.csv';
            $data['testimonial_id'] = $content_row['testimonial']['testimonial_id'];
            break;

          // NOTE: According to documentation, these references are not migrating.
          case 8:
            // Set the destination bundle.
            $bundle = 'hours_placer';
            $data['row_name'] = 'hours';
            $data['log_file'] = 'para_hours.csv';
            foreach ($content_row['hours']['hours_ids'] as $key => $value) {
              $data["hours_lib_id_$key"] = $value['val'];
            }
            break;

          case 41:
            // Set the destination bundle.
            $bundle = 'social_media_links';
            $data['row_name'] = 'social';
            $data['log_file'] = 'para_social_links.csv';
            $data['unit_id'] = $content_row['social']['unit_id'];
            break;

          case 25:
            // Set the destination bundle.
            $bundle = 'custom_embed';
            $data['row_name'] = 'stackla_widget';
            $data['log_file'] = 'para_stackla_widget.csv';
            $data['title'] = $content_row['stackla_widget']['title'];
            $data['embed_code'] = $content_row['stackla_widget']['embed_code'];
            $row->setSourceProperty('field_widget_title', $content_row['stackla_widget']['title']);
            $row->setSourceProperty('field_embed_content', $content_row['stackla_widget']['embed_code']);
            break;

          case 20:
            // Set the destination bundle.
            $bundle = 'links_files';
            $data['row_name'] = 'utility-bar';
            $data['title'] = $content_row['utility-bar']['title'];
            foreach ($content_row['utility-bar']['resources'] as $key => $item) {
              $data['log_file'] = "para_utility-bar_{$item['name']}_links.csv";
              $data["title_$key"] = $item['val']['title'];
              $data["{$item['name']}_$key"] = $item['val'][$item['name']];
            }
            break;

          case 26:
            // Set the destination bundle.
            $bundle = 'video';
            $data['row_name'] = 'video';
            $data['log_file'] = 'para_video.csv';
            $data['video_id'] = $content_row['video']['video_id'];
            break;

          case 55:
            // Set the destination bundle.
            $bundle = 'faux_item';
            $data['row_name'] = 'faux_profile';
            $data['log_file'] = 'para_faux_profile.csv';
            foreach ($content_row['faux_profile']['profiles'] as $key => $profile) {
              foreach ($profile['val'] as $fieldName => $fieldValue)  {
                $data["{$fieldName}_$key"] = $fieldValue;
              }
            }
            break;

          default:
            $data['log_file'] = 'not_processed.csv';
            return FALSE;
            break;
          // Hero.*
          // Tabular RTE [21]
          // Person Faux profile. (link to content type) [55] log headshot_image_id field
          // Feed profile [32] log the val field
          // Remote Video.* [26]
          // Streaming Video [27]
          // Full-Width Image [7] log full_width_image/image_id
          // Photo/Gallery.* [47] log photos/val/image_id, image_title, image_caption
          // Hours & contact.
          // Contact Info [50] log contacts/val/title, image_id, page_id,email, phone, chat_url, appointments_url, other_url
          // Facts. (link to content type)
          // Social media links.* [41] log social/unit_id
          // Embed.* (stackla_widget) [25]
          // Hours block.* [8] log hours_ids/val
          // Pull Quote.*
          // Featurettes [6] log links/val/page_id, excerpt_title, excerpt_paragraph, image_id, button_text
          // Utility Bar [20] log utility_bar/title, resources (array)
          // Links [6]
          // Wildcard* [46]
        }
        $data['dest_bundle'] = $bundle;
        if (isset($data['row_name'])) {
          $uwec_row_data[$data['row_name']][] = $data;
        }

      }
//      $this->cmTools->logToConsole($data);
      $row->setDestinationProperty('type', $bundle);
      $store->set('uwec_row_data', $uwec_row_data);
      return $data;
    }
  }


}