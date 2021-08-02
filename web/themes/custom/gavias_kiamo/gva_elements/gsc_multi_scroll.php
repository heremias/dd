<?php 
namespace Drupal\gavias_blockbuilder\shortcodes;
if(!class_exists('gsc_multi_scroll')):
   class gsc_multi_scroll{

      public function render_form(){
         $fields = array(
            'type' => 'gsc_multi_scroll',
            'title' => t('Multi Scroll'),
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
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),   
            ),                                     
         );

         for($i=1; $i<=10; $i++){
            $fields['fields'][] = array(
               'id'     => "info_${i}",
               'type'   => 'info',
               'desc'   => "Information for item {$i}"
            );
            $fields['fields'][] = array(
               'id'        => "title_{$i}",
               'type'      => 'text',
               'title'     => t("Title {$i}")
            );
            $fields['fields'][] = array(
               'id'           => "content_{$i}",
               'type'         => 'textarea_no_html',
               'title'        => t("Content {$i}"),
               'desc'         => t('Shortcodes and HTML tags allowed.'),
               'shortcodes'   => 'on'
            );
            $fields['fields'][] = array(
               'id'        => "link_{$i}",
               'type'      => 'text',
               'title'     => t("Link {$i}")
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
         print self::sc_multi_scroll( $item['fields'] );
      }

      public static function sc_multi_scroll( $attr, $content = null ){
         global $base_url;
         $default = array(
            'title'      => '',
            'el_class'   => '',
            'animate'    => '',
         );

         for($i=1; $i<=10; $i++){
            $default["title_{$i}"] = '';
            $default["content_{$i}"] = '';
            $default["link_{$i}"] = '';
            $default["image_{$i}"] = '';
         }

         extract(shortcode_atts($default, $attr));

         if($animate){
            $el_class .= ' wow';
            $el_class .= ' ' . $animate;
         }
         $_id = gavias_blockbuilder_makeid();
         
         ?>
         <?php ob_start() ?>
         <script src="<?php print drupal_get_path('theme', 'gavias_foxin') ?>/vendor/multiscroll/jquery.easings.min.js"></script>
         <script src="<?php print drupal_get_path('theme', 'gavias_foxin') ?>/vendor/multiscroll/jquery.multiscroll.min.js"></script>
         <link rel="stylesheet" href="<?php print drupal_get_path('theme', 'gavias_foxin') ?>/vendor/multiscroll/jquery.multiscroll.min.css" media="screen" />
         <div class="gsc-multi-sroll <?php echo $el_class ?>"> 
            <div id="multi-sroll-<?php echo $_id ?>">
               
               <div class="ms-left"> 
                  <?php for($i=1; $i<=10; $i++){ ?>
                        <?php 
                           $title = "title_{$i}";
                           $content = "content_{$i}";
                           $link = "link_{$i}";
                           $image = "image_{$i}";
                        ?>
                        <?php if($$title){ ?>
                           <div class="ms-section" id="left-<?php echo ($_id . $i) ?>">
                              
                              <?php if($i%2==1){ ?>
                                 
                                 <div class="main-content">
                                    <div class="number"><?php echo $i; ?></div>
                                    <?php if($$title){ ?>
                                       <div class="title"><a href="<?php print $$link ?>"><?php print $$title ?></a></div>
                                    <?php } ?>
                                    <?php if($$content){ echo '<div class="desc">' . $$content . '</div>'; } ?>
                                    <?php if($$link){ ?>
                                       <div class="action margin-top-30"><a class="btn btn-theme" href="<?php print $$link ?>"><?php print t('Read more') ?></a></div>
                                    <?php } ?>
                                 </div>
                                 
                              <?php }else{ ?>

                                 <?php if($$image){?>
                                   <div class="bg-full" style="background-image:url('<?php echo ($base_url . $$image) ?>') "></div>
                                 <?php }  ?>

                              <?php } ?>

                           </div>

                        <?php } ?>    
                  <?php } ?>
               </div> 

               <div class="ms-right"> 
                  <?php for($i=1; $i<=10; $i++){ ?>
                     
                        <?php 
                           $title = "title_{$i}";
                           $content = "content_{$i}";
                           $link = "link_{$i}";
                           $image = "image_{$i}";
                        ?>
                        <?php if($$title){ ?>
                           <div class="ms-section" id="right-<?php echo ($_id . $i) ?>">
                              
                              <?php if($i%2==0){ ?>
                                 
                                 <div class="main-content">
                                    <div class="number"><?php echo $i; ?></div>
                                    <?php if($$title){ ?>
                                       <div class="title"><a href="<?php print $$link ?>"><?php print $$title ?></a></div>
                                    <?php } ?>
                                    <?php if($$content){ echo '<div class="desc">' . $$content . '</div>'; } ?>
                                    <?php if($$link){ ?>
                                       <div class="action margin-top-30"><a class="btn btn-theme" href="<?php print $$link ?>"><?php print t('Read more') ?></a></div>
                                    <?php } ?>
                                 </div>
                                 
                              <?php }else{ ?>

                                 <?php if($$image){?>
                                   <div class="bg-full" style="background-image:url('<?php echo ($base_url . $$image) ?>') "></div>
                                 <?php }  ?>

                              <?php } ?>

                           </div>
                        <?php } ?>  
                  <?php } ?>
               </div> 

            </div>   
         </div>   

         <script type="text/javascript">
           jQuery(document).ready(function() {
             jQuery('#multi-sroll-<?php echo $_id ?>').multiscroll({
               sectionsColor: ['#F8F8F8', '#F8F8F8', '#F8F8F8', '#F8F8F8', '#F8F8F8', '#F8F8F8', '#F8F8F8', '#F8F8F8', '#F8F8F8', '#F8F8F8', '#F8F8F8'],
               navigation: false,
               loopBottom: true,
               loopTop: true
             });
           });
         </script>
         <?php return ob_get_clean();
      }

      public function load_shortcode(){
         add_shortcode( 'sc_multi_scroll', array($this, 'sc_multi_scroll') );
      }
   }
 endif;  



