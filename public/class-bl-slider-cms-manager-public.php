<?php

class Bl_Slider_Cms_Manager_Public {

    private $version;

    private $data_model;

    private $cache_manager;

    private $options;

    function __construct( $version, $options, $data_model, $cache_manager ) {

        $this->version = $version;

        $this->options = $options;

        $this->data_model = $data_model;

        $this->cache_manager = $cache_manager;

    }

    function create_shortcode_bl_slider( $atts ){

        add_shortcode( 'bl-slider', array( $this, 'render_bl_slider_shortcode') );

    }

    public function render_bl_slider_shortcode( $atts ){

        $atts = $this->get_bl_slider_shortcode_atts( $atts );

        $bl_slides = $this->data_model->get_slider_posts( $atts );

        return $this->render_bl_slider( $bl_slides, $atts );

    }


    private function render_bl_slider( $bl_slides, $atts ){

        global $bl_sliders_printed, $locale;

        $id_cache = $this->cache_manager->create_id_cache_html( 'bl-slider-' . $locale . serialize($atts) );

        $html_carousel = $this->cache_manager->has_cached_html( $id_cache );

        if( false === $html_carousel ){

            ob_start();

            $this->include_start_bl_slider_template( $bl_slides, $atts );

            foreach( $bl_slides as $bl_slide_index => $bl_slide ){

                $this->include_item_bl_slider_template( $bl_slide, $bl_slide_index, $atts );

            }

            $this->include_end_bl_slider_template( $bl_slides, $atts );

            $html_carousel = ob_get_clean();

            $this->cache_manager->cache_html( $html_carousel, $id_cache );

        }

        return $html_carousel;

        // store atts for each slider printed.it could be used in the template to
        // implement specific code when gallery are printed like include JS, or execute JS initiazations
        $bl_sliders_printed[] = $atts;

    }

    private function get_bl_slider_shortcode_atts( $atts ){

        $a = shortcode_atts( array(
            'categories' => null,
            'limit' => 5,
            'template' => null
        ), $atts, 'bl-slider');

        return $a;

    }


    private function include_start_bl_slider_template( $bl_slides, $atts ){

        include $this->locate_template_bl_slider( 'start-slider.php', $atts );

    }


    private function include_end_bl_slider_template( $bl_slides, $atts ){

        include $this->locate_template_bl_slider( 'end-slider.php', $atts );

    }

    private function include_item_bl_slider_template( $bl_slide, $bl_slide_index, $atts ){

        include $this->locate_template_bl_slider( 'item-slider.php', $atts );

    }

    private function locate_template_bl_slider( $template, $atts ){

        global $post;

        $custom_template_folder = get_template_directory() . '/' . $this->options['bl-slider-custom-template-folder'];

        $check_templates = array();

        if( isset( $atts['template'] ) ) {}{

            $check_templates[] =  $custom_template_folder . '/' . substr( $template, 0, -4 ) . '-' . $atts['template'] . '.php';

        }

        if( $post ){

            $check_templates[] =  $custom_template_folder . '/' . substr( $template, 0, -4 ) . '-' . $post->post_name . '.php';

        }

        if( isset( $atts['categories'] ) ) {}{

            $check_templates[] =  $custom_template_folder . '/' . substr( $template, 0, -4 ) . '-' . $atts['categories'] . '.php';

        }

        $check_templates[] =  $custom_template_folder . '/' . $template;

        foreach( $check_templates as $file_path ){

            if( file_exists( $file_path ) ){

                return $file_path;

            }

        }

        return dirname( __FILE__) . '/partials/' . $template;

    }

}