<?php

/*
 * Plugin Name: Movie post type
 * Description: Adds custom post type of movie, which can be used to write movie reviews or presentations.
 * Author: Leon Pahole
 */
if (!class_exists('LeonP_MoviePost')) {

    class LeonP_MoviePost
    {
        public function __construct()
        {
            add_action('init', array($this, 'register_movie_post_type'));
            add_action('add_meta_boxes', array($this, 'add_movie_metaboxes'));
            add_action('save_post_leonp_movie', array($this, 'save_movie_post'));
            add_filter('the_content', array($this, 'prepend_movie_to_content'));

            add_action('admin_enqueue_scripts', array($this, 'enqueue_movie_admin_styles'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_movie_styles'));

            register_activation_hook(__FILE__, array($this, 'on_activate'));
            register_deactivation_hook(__FILE__, array($this, 'on_activate'));
        }

        public function enqueue_movie_admin_styles()
        {
            wp_register_style('leonp_movie_admin_css', plugins_url('leonp-movie-post-admin.css', __FILE__));
            wp_enqueue_style('leonp_movie_admin_css');
        }

        public function enqueue_movie_styles()
        {
            wp_register_style('leonp_movie_css', plugins_url('leonp-movie-post.css', __FILE__));
            wp_enqueue_style('leonp_movie_css');
        }

        public function register_movie_post_type()
        {
            register_post_type('leonp_movie',
                array(
                    'labels' => array(
                        'name' => 'Movies',
                        'singular_name' => 'Movie',
                        'add_new_item' => 'Add New Movie',
                        'edit_item' => 'Edit Movie',
                        'new_item' => 'New Movie',
                        'view_item' => 'View Movie',
                        'view_items' => 'View Movies',
                        'search_items' => 'Search Movies'
                    ),
                    'menu_icon' => 'dashicons-media-video',
                    'description' => 'Post for describing movies',
                    'public' => true,
                    'has_archive' => true,
                    'rewrite' => array('slug' => 'movies'), // my custom slug
                )
            );
        }

        public function add_movie_metaboxes()
        {
            add_meta_box(
                'leonp_movie_info_mb',
                'Movie information',
                array($this, 'render_movie_metabox'),
                'leonp_movie'
            );
        }

        public function render_movie_metabox()
        {
            global $post;

            wp_nonce_field(basename(__FILE__), 'leonp-movie-nonce-field');
            ?>

            <div>

                <div class="leonp-movie-input-wrapper">

                    <div class="leonp-movie-form-group">
                        <label class="leonp-movie-input-label" for="leonp-movie-full-title">Full title</label>
                        <input class="leonp-movie-input" type="text" name="leonp-movie-full-title"
                               id="leonp-movie-full-title"
                               value="<?php echo get_post_meta($post->ID, '_leonp_movie_full_title', true); ?>">
                    </div>

                    <div class="leonp-movie-form-group" style="flex: 1;">
                        <label class="leonp-movie-input-label" for="leonp-movie-director">Director</label>
                        <input class="leonp-movie-input" type="text" name="leonp-movie-director"
                               id="leonp-movie-director"
                               value="<?php echo get_post_meta($post->ID, '_leonp_movie_director', true); ?>">
                    </div>

                    <div class="leonp-movie-form-group">
                        <label class="leonp-movie-input-label" for="leonp-movie-year">Year</label>
                        <input class="leonp-movie-input" type="number" name="leonp-movie-year" id="leonp-movie-year"
                               value="<?php echo get_post_meta($post->ID, '_leonp_movie_year', true); ?>">
                    </div>

                </div>

                <div class="leonp-movie-input-wrapper">

                    <div class="leonp-movie-form-group">
                        <label class="leonp-movie-input-label" for="leonp-movie-runtime">Runtime</label>
                        <input class="leonp-movie-input" type="text" name="leonp-movie-runtime" id="leonp-movie-runtime"
                               value="<?php echo get_post_meta($post->ID, '_leonp_movie_runtime', true); ?>">
                    </div>

                    <div class="leonp-movie-form-group">
                        <label class="leonp-movie-input-label" for="leonp-movie-main-roles">In main roles</label>
                        <input class="leonp-movie-input" type="text" name="leonp-movie-main-roles"
                               id="leonp-movie-main-roles"
                               value="<?php echo get_post_meta($post->ID, '_leonp_movie_main_roles', true); ?>">
                    </div>
                </div>

                <div class="inputs-wrapper wrapper">

                    <div class="leonp-movie-form-group">
                        <label class="leonp-movie-input-label" for="leonp-movie-plot">
                            Plot<br>
                            <textarea class="leonp-movie-textarea" name="leonp-movie-plot" id="leonp-movie-plot"
                            ></textarea>
                        </label>
                    </div>

                </div>
            </div>
            <?php
        }

        public function save_movie_post($post_id)
        {

            if (!isset($_POST['leonp-movie-nonce-field']) || !wp_verify_nonce($_POST['leonp-movie-nonce-field'], basename(__FILE__))) {
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            if (isset($_REQUEST['leonp-movie-full-title'])) {
                update_post_meta($post_id, '_leonp_movie_full_title', sanitize_text_field($_POST['leonp-movie-full-title']));
            }

            if (isset($_REQUEST['leonp-movie-director'])) {
                update_post_meta($post_id, '_leonp_movie_director', sanitize_text_field($_POST['leonp-movie-director']));
            }

            if (isset($_REQUEST['leonp-movie-year'])) {
                update_post_meta($post_id, '_leonp_movie_year', sanitize_text_field($_POST['leonp-movie-year']));
            }

            if (isset($_REQUEST['leonp-movie-runtime'])) {
                update_post_meta($post_id, '_leonp_movie_runtime', sanitize_text_field($_POST['leonp-movie-runtime']));
            }

            if (isset($_REQUEST['leonp-movie-main-roles'])) {
                update_post_meta($post_id, '_leonp_movie_main_roles', sanitize_text_field($_POST['leonp-movie-main-roles']));
            }

            if (isset($_REQUEST['leonp-movie-plot'])) {
                update_post_meta($post_id, '_leonp_movie_plot', sanitize_text_field($_POST['leonp-movie-plot']));
            }
        }

        public function prepend_movie_to_content($content)
        {

            global $post, $post_type;

            if ($post_type == 'leonp_movie') {

                $movie_html = '
                <div class="leonp-movie-info-container">
                <h5>Movie information</h5>
                    <span class="leonp-movie-label">Movie title:</span> <span>' . get_post_meta($post->ID, '_leonp_movie_full_title', true) . '</span><br>
                    <span class="leonp-movie-label">Movie director:</span><span>' . get_post_meta($post->ID, '_leonp_movie_director', true) . '</span><br>
                    <span class="leonp-movie-label">Movie year:</span><span>' . get_post_meta($post->ID, '_leonp_movie_year', true) . '</span><br>
                    <span class="leonp-movie-label">Movie runtime:</span><span>' . get_post_meta($post->ID, '_leonp_movie_runtime', true) . '</span><br>
                    <span class="leonp-movie-label">Movie main roles:</span><span>' . get_post_meta($post->ID, '_leonp_movie_main_roles', true) . '</span><br>
                    <span class="leonp-movie-label">Movie plot:</span><span>' . get_post_meta($post->ID, '_leonp_movie_plot', true) . '</span><br>
                </div>';

                $content = $movie_html . $content;
            }

            return $content;
        }

        private function on_activate()
        {
            $this->register_movie_post_type();
            flush_rewrite_rules();
        }

        public function on_deactivate(){
            flush_rewrite_rules();
        }
    }

    $leonp_moviepost = new LeonP_MoviePost();
}
