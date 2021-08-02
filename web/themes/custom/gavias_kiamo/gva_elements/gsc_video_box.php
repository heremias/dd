<?php 
namespace Drupal\gavias_blockbuilder\shortcodes;

use Drupal\gavias_blockbuilder\includes\gavias_blockbuilder_embed;
if(!class_exists('gsc_video_box')):
   class gsc_video_box{

      public function render_form(){
         $fields = array(
            'type' => 'gsc_video_box',
            'title' => ('Video Box'), 
            'size' => 3,
            'fields' => array(
               array(
                  'id'        => 'title',
                  'type'      => 'text',
                  'title'     => t('Title Admin'),
                  'class'     => 'display-admin'
               ),
               array(
                  'id'        => 'content',
                  'type'      => 'text',
                  'title'     => t('Data Url'),
                  'desc'      => t('example: https://www.youtube.com/watch?v=4g7zRxRN1Xk'),
               ),
               array(
                  'id'        => 'image',
                  'type'      => 'upload',
                  'title'     => t('Image Preview'),
               ),
               array(
                  'id'        => 'animate',
                  'type'      => 'select',
                  'title'     => t('Animation'),
                  'desc'      => t('Entrance animation for element'),
                  'options'   => gavias_blockbuilder_animate(),
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),
            ),                                       
         );
         return $fields;
      }

      public function render_content( $item ) {
         if( ! key_exists('content', $item['fields']) ) $item['fields']['content'] = '';
         print self::sc_video_box( $item['fields'], $item['fields']['content'] );
      }

      public static function sc_video_box( $attr, $content = null ){
         global $base_url;
         extract(shortcode_atts(array(
            'title'           => '',
            'image'           => '',
            'animate'         => '',
            'el_class'        => '',
         ), $attr));

         $_id = gavias_blockbuilder_makeid();
         if($image){
            $image = $base_url .$image; 
         }
         if($animate){
            $el_class .= ' wow';
            $el_class .= ' '. $animate;
         }
      ?>
      <?php ob_start() ?>
      <div class="widget gsc-video-box <?php print $el_class;?> clearfix">
         <div class="video-inner">
            <div class="image"><img src="<?php print $image ?>" alt="<?php print $title ?>"/></div>
            <div class="video-body">
               <a class="popup-video gsc-video-link" href="<?php print $content ?>">
                  <span class="icon-play"><i class="fa fa-play space-40"></i></span>
               </a>
            </div>
         </div>   
      </div>   
      <?php return ob_get_clean() ?>
       <?php
      }

      public function load_shortcode(){
         add_shortcode( 'video_box', array('gsc_video_box', 'sc_video_box') );
      }
   }
endif;   




