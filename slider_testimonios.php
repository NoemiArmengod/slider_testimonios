<?php
/**
* Plugin Name: Carrusel de testimonios
* Plugin URI: https://www.noemiarmengod.info/
* Description: Plugin para gestionar un carrusel de testimonios
* Version: 1.0
* Author: Noemí Armengod
* Author URI: https://noemiarmengod.info/
**/

/*
*
* Scripts del plugin
*
*/ 
function testimonios_widget_enqueue_script() {   
    wp_enqueue_script( 'splidejs', plugin_dir_url( __FILE__ ) . 'js/splide.min.js', array('jquery'), '', true );
    wp_enqueue_script( 'customscript', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array('jquery'), '1.0.0', true );
    wp_enqueue_style( 'splidecss', plugin_dir_url( __FILE__ ) . 'css/splide.min.css' );
    wp_enqueue_style( 'style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
}
add_action('wp_enqueue_scripts', 'testimonios_widget_enqueue_script');

/*
* CPT Testimonios
*/
function cpt_testimonios() {
 
      $labels = array(
          'name'                => _x( 'Testimonios', 'Post Type General Name', 'cpt_nam_testimonios' ),
          'singular_name'       => _x( 'Testimonio', 'Post Type Singular Name', 'cpt_nam_testimonios' ),
          'menu_name'           => __( 'Testimonios', 'cpt_nam_testimonios' ),
          'parent_item_colon'   => __( 'Padre Testimonio', 'cpt_nam_testimonios' ),
          'all_items'           => __( 'Todos los testimonios', 'cpt_nam_testimonios' ),
          'view_item'           => __( 'Ver testimonio', 'cpt_nam_testimonios' ),
          'add_new_item'        => __( 'Añadir nuevo testimonio', 'cpt_nam_testimonios' ),
          'add_new'             => __( 'Añadir nuevo', 'cpt_nam_testimonios' ),
          'edit_item'           => __( 'Editar testimonio', 'cpt_nam_testimonios' ),
          'update_item'         => __( 'Actualzar testimonio', 'cpt_nam_testimonios' ),
          'search_items'        => __( 'Buscar testimonio', 'cpt_nam_testimonios' ),
          'not_found'           => __( 'No encontrado', 'cpt_nam_testimonios' ),
          'not_found_in_trash'  => __( 'No encontrado', 'cpt_nam_testimonios' ),
      );
       
      $args = array(
          'label'               => __( 'testimonios', 'cpt_nam_testimonios' ),
          'description'         => __( 'Información sobre los testimonios', 'cpt_nam_testimonios' ),
          'labels'              => $labels,
          'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
          'register_meta_box_cb' => 'empresa_testimonio',
          'hierarchical'        => true,
          'public'              => true,
          'show_ui'             => true,
          'show_in_menu'        => true,
          'show_in_nav_menus'   => true,
          'show_in_admin_bar'   => true,
          'menu_position'       => 20,
          'menu_icon'            => 'dashicons-groups',
          'can_export'          => true,
          'has_archive'         => false,
          'exclude_from_search' => true,
          'publicly_queryable'  => true,
          'capability_type'     => 'page',
      );
       
      register_post_type( 'testimonios', $args );
   
  }
   
  add_action( 'init', 'cpt_testimonios', 0 );

  /*
  *
  * Campo personalizado
  *
  */
// Metabox información de la empresa
  function empresa_testimonio($post) {
    add_meta_box(
        'empresa_meta_box',  // $id
        'Empresa', // $titulo
        'testimonio_empresa_meta_box', // $callback
        'testimonios', // $cpt
        'normal', // $contexto
        'default' // $prioridad
    );
}

// Función que muestra la meta box
function testimonio_empresa_meta_box() {
    global $custom_meta_fields, $post;

    // Verificación del contenido del campo mediante nonce
    echo '<input type="hidden" '
    . 'name="empresa_meta_box_noncename" '
    . 'id="empresa_meta_box_noncename" '
    . 'value = "' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    // Variable donde optenemos el valor del campo, si es que ya teniamos información en él
    $contact = get_post_meta($post->ID, '_empresa', true);

    // Mostramos el input, vacío o no, según si estamos editando o no un testimonio
    echo '<input type = "text" name="_empresa" value="' . esc_textarea( $contact )  . '" class = "testimonio-empresa"> ';
}

// Hook que nos permite guardar el valor del campo personalizado (custom field)
add_action('save_post', 'save_testimonio_empresa_meta_box', 1, 2);

// Función que nos permite guardar el valor del campo personalizado (custom field)
function save_testimonio_empresa_meta_box($post_id, $post) {
    
    // Verificamos que tenemos autorización para acceder al campo con nonce 
    if ( !isset($_POST['empresa_meta_box_noncename']) || !wp_verify_nonce( $_POST['empresa_meta_box_noncename'], plugin_basename(__FILE__) )) {
        return $post->ID;
    }

    // Verificamos que el usuario tiene permisos de edición
    if (!current_user_can('edit_post', $post->ID)) {
        return $post->ID;
    }

    // Añadimos en una array los valores del campo personalizado
    $property_meta['_empresa'] = $_POST['_empresa'];

    // Añadimos, modificamos o eliminamos los valores de la array del testiminio
    foreach ($property_meta as $key => $value) {
        if ($post->post_type == "revision")
            return;
        $value = implode(',', (array) $value);

        if (get_post_meta($post->ID, $key, FALSE)) {
            update_post_meta($post->ID, $key, $value);
        } else {
            add_post_meta($post->ID, $key, $value);
        }
        if (!$value) {
            delete_post_meta($post->ID, $key);
        }
    }
}


  /*
  *
  * Shortcode con WP_Query para mostrar la informaciuón de los testimonios
  *
  */
  
  function mostrar_testimonios(){
  // Genero los argumentos para el query
    $args = array(
          'post_type' => 'testimonios',
          'posts_per_page'  => -1,
          'post_status' => 'publish',
          'orderby'     => 'menu_order',
          'order'       => 'ASC'
      );

      // Construyo el resultado que se mostrará
      $output = '';
      
      $query = new WP_Query( $args );

      if( $query->have_posts() ){

        // Elementos que generan el slide
          $output .= '<section class="splide" aria-label="Splide Basic HTML Example">
          <div class="splide__track">
                <ul class="splide__list">';
      
          while( $query->have_posts() ){
              $query->the_post();
              // Cada slider debe estar envuelto en un elemento "li"
              $output .= '<li class="splide__slide">';
              $output .= '<div class="testimonio-image">'. get_the_post_thumbnail( get_the_ID(), 'thumbnail' ) .'</div>';
              $output .= '<div class="testimonio-texto"><p>' . get_the_content() . '</p></div>';
              $output .= '<div class="testimonio-firma"><h4>' . get_the_title() . ' - ';
              $output .= '<span class="testimonio-empresa">' .  get_post_meta( get_the_ID(), "_empresa", true ) . '</span>';
              $output .= '</h4></div>';
              $output .= '</li>';
            }
      
            $output .= '</ul>
            </div>
          </section>';
      }
      wp_reset_postdata(); // Reseteo WP_Query

      return $output; // Devuelvo output
  }

  // Shortcode necesario para mostrar el slide de testimonios
  add_shortcode( 'slidertestimonios', 'mostrar_testimonios' );