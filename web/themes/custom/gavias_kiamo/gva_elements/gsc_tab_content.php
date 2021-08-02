<?php 
namespace Drupal\gavias_blockbuilder\shortcodes;
if(!class_exists('gsc_tab_content')):
   class gsc_tab_content{

      public function render_form(){
         $fields = array(
            'type' => 'gsc_tab_content',
            'title' => t('Tab Content'),
            'size' => 3,
            'fields' => array(
               array(
                  'id'     => 'title',
                  'type'      => 'text',
                  'title'  => t('Title For Admin'),
                  'class'     => 'display-admin'
               ),
               array(
                  'id'     => 'animate',
                  'type'      => 'select',
                  'title'  => ('Animation'),
                  'desc'  => t('Entrance animation for element'),
                  'options'   => gavias_blockbuilder_animate(),
               ),
               array(
                  'id'        => 'style',
                  'type'      => 'select',
                  'title'     => ('Style'),
                  'options'   => array( '' => 'Default', 'text-light' => 'Text Light'  ),
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),   
            ),                                     
         );

         for($i=1; $i<=8; $i++){
            $fields['fields'][] = array(
               'id'     => "info_${i}",
               'type'   => 'info',
               'desc'   => "Information for item {$i}"
            );
            $fields['fields'][] = array(
               'id'        => "icon_{$i}",
               'type'      => 'text',
               'title'     => t("Icon Class {$i}"),
               'desc'     => t('Use class icon font <a target="_blank" href="http://fontawesome.io/icons/">Icon Awesome</a> or <a target="_blank" href="'.base_path().drupal_get_path('theme', 'gavias_kiamo').'/demo-font/index.html'.'">Custom icon</a>'),
            );
            $fields['fields'][] = array(
               'id'        => "title_{$i}",
               'type'      => 'text',
               'title'     => t("Title {$i}")
            );
            $fields['fields'][] = array(
               'id'           => "content_{$i}",
               'type'         => 'textarea',
               'title'        => t("Content {$i}"),
               'desc'         => t('Shortcodes and HTML tags allowed.'),
               'shortcodes'   => 'on'
            );
            $fields['fields'][] = array(
               'id'        => "image_{$i}",
               'type'      => 'upload',
               'title'     => t("Image {$i}")
            );
         }
         return $fields;
      }

      public function render_content( $item ) {
         print self::sc_tab_content( $item['fields'] );
      }

      public static function sc_tab_content( $attr, $content = null ){
         global $base_url;
         $default = array(
            'title'      => '',
            'style'      => '',
            'el_class'   => '',
            'animate'    => '',
         );
         for($i=1; $i<=10; $i++){
            $default["icon_{$i}"] = '';
            $default["title_{$i}"] = '';
            $default["content_{$i}"] = '';
            $default["image_{$i}"] = '';
         }
         extract(shortcode_atts($default, $attr));
         $el_class .= ' ' . $style;
         if($animate){
            $el_class .= ' wow';
            $el_class .= ' ' . $animate;
         }
         $_id = gavias_blockbuilder_makeid();
      ?>

      <?php ob_start() ?> 

      <div class="gsc-tabs-content <?php echo $el_class ?>"> 
         <div class="carousel-nav">
            <div class="tab-carousel-nav slick-slider">
               <?php for($i=1; $i<=10; $i++): 
                  $icon = "icon_{$i}";
                  $title = "title_{$i}";
                  $content = "content_{$i}";
                  $image = "image_{$i}";
               ?>
               <?php if($$title){ ?>
                  <div class="slick-slide"><div class="item"><a><?php if($$icon){ ?><i class="<?php print $$icon ?>"></i><?php } ?><?php print $$title ?></a></div></div>
               <?php } ?> 
               <?php endfor; ?>
            </div>
         </div>

         <div class="tab-lists-content">
            <div class="tab-carousel-list-here slick-slider"> 
               <?php for($i=1; $i<=10; $i++): 
                  $icon = "icon_{$i}";
                  $title = "title_{$i}";
                  $content = "content_{$i}";
                  $image = "image_{$i}";
               ?>
               <?php if($$title){ ?>
                  <div class="slick-slide">
                     <div class="item">
                        <?php if($$image){ ?>
                           <div class="images"><img src="<?php echo ($base_url . $$image) ?>"></div>
                        <?php } ?>
                        <div class="content-inner"><?php if($$content){ print $$content; } ?></div>
                     </div>
                  </div>
               <?php } ?> 
               <?php endfor; ?>
            </div>
         </div>      
      </div>

      <?php return ob_get_clean();
      }

      public function load_shortcode(){
         add_shortcode( 'sc_tab_content', array($this, 'sc_tab_content') );
      }
   }
 endif;  



