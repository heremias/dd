<?php 
namespace Drupal\gavias_blockbuilder\shortcodes;
if(!class_exists('gsc_download')):
   class gsc_download{

      public function render_form(){
         $fields = array(
            'type' => 'gsc_download',
            'title' => t('Download box'),
            'size' => 3,
            'fields' => array(
               array(
                  'id'        => 'title',
                  'type'      => 'text',
                  'title'     => t('Title'),
                  'class'     => 'display-admin'
               ),
               array(
                  'id'        => 'content',
                  'type'      => 'textarea',
                  'title'     => t('Content')
               ),
               array(
                  'id'        => 'animate',
                  'type'      => 'select',
                  'title'     => ('Animation'),
                  'desc'      => t('Entrance animation for element'),
                  'options'   => gavias_blockbuilder_animate(),
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),
               array(
                  'id'            => 'style',
                  'type'          => 'select',
                  'options'       => array(
                     'vertical'        => t('Vertical'),
                     'horizontal'        => t('Horizontal'),
                  ),
                  'title'  => t('Style'),
               ), 
            ),                                     
         );

         for($i=1; $i<=5; $i++){
            $fields['fields'][] = array(
               'id'     => "info_${i}",
               'type'   => 'info',
               'desc'   => "Information for item file {$i}"
            );
            $fields['fields'][] = array(
               'id'     => "name_${i}",
               'type'   => 'text',
               'title'   => "File Name {$i}"
            );
            $fields['fields'][] = array(
               'id'        => "link_{$i}",
               'type'      => 'text',
               'title'     => t("File Link Download {$i}")
            );
         }
         return $fields;
      }

      public function render_content( $item ) {
         print self::sc_download( $item['fields'] );
      }

      public static function sc_download( $attr, $content = null ){
         global $base_url;
         $default = array(
            'title'      => '',
            'el_class'   => '',
            'animate'    => '',
            'content'    => '',
            'style'      => 'vertical'
         );

         for($i=1; $i<=10; $i++){
            $default["name_{$i}"] = '';
            $default["link_{$i}"] = '';
         }

         extract(shortcode_atts($default, $attr));

         if($animate){
            $el_class .= ' wow';
            $el_class .= ' ' . $animate;
         }
         $el_class .= ' ' . $style;
         $_id = gavias_blockbuilder_makeid();
         
         ?>
         <?php ob_start() ?>
            <div class="gsc-box-download <?php echo $el_class ?>"> 
               <div class="box-content">
                  <div class="info">
                     <?php if($title){ ?>
                        <div class="title"><?php print $title ?></div>
                     <?php } ?>
                      <?php if($content){ ?>
                        <div class="desc"><?php print $content ?></div>
                     <?php } ?>
                  </div>
                  <div class="box-files">
                  <?php for($i=1; $i<=10; $i++){ ?>
                     <?php 
                        $name = "name_{$i}";
                        $link = "link_{$i}";
                     ?>
                     <?php if($$name){ ?>
                        <div class="item">
                          <div class="file">
                              <a href="<?php print $$link ?>"><?php print $$name ?></a></div>
                        </div>
                     <?php } ?>    
                  <?php } ?>
                  </div>
               </div> 
           
         <?php return ob_get_clean();
      }

      public function load_shortcode(){
         add_shortcode( 'sc_download', array($this, 'sc_download') );
      }
   }
 endif;  



