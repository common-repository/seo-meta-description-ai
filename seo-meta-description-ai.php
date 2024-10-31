<?php
/**
 * Plugin Name: SEO Meta Description AI
 * Plugin URI:  https://binarysolutions.biz/wordpress-plugins/seo-meta-description-ai/
 * Description: Set and customize an AI generated SEO meta description for each post or page.
 * Version:     1.2
 * Author:      Binary Solutions
 * Author URI:  https://binarysolutions.biz
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define("SEO_META_DESCRIPTION_AI_PLUGIN_NAME",    "SEO Meta Description AI");
define("SEO_META_DESCRIPTION_AI_PLUGIN_URI",     "https://binarysolutions.biz/wordpress-plugins/seo-meta-description-ai/");
define("SEO_META_DESCRIPTION_AI_PLUGIN_VERSION", "1.2");

/**
 * Add the meta box to the post and page editor
 */
function seo_meta_description_ai_add_meta_box() {
    
    $screens = array("post", "page");
    foreach ($screens as $screen) {
        add_meta_box(
            "seo_meta_description_ai_meta_box",
            SEO_META_DESCRIPTION_AI_PLUGIN_NAME,
            "seo_meta_description_ai_meta_box_callback",
            $screen,
            "normal",
            "high"
        );
    }
}
add_action("add_meta_boxes", "seo_meta_description_ai_add_meta_box");

/**
 * Display the meta box content
 */
function seo_meta_description_ai_meta_box_callback($post) {
    
    wp_nonce_field(
        "seo_meta_description_ai_save_meta_box_data", 
        "seo_meta_description_ai_nonce"
    );
    
    $meta_description = get_post_meta(
        $post->ID, 
        "seo_meta_description_ai_meta_description", 
        true
    );
    
    $activation_code = get_option("seo_meta_description_ai_activation_code", "");
?>

<p><strong>Before generating a new meta description, please save the draft or publish your changes.</strong></p>
<label    id="seo-meta-description-ai-label" for="seo-meta-description-ai-textarea">Meta description (length <span><?php echo strlen(esc_html($meta_description)) ?></span>):</label>
<textarea id="seo-meta-description-ai-textarea" name="seo_meta_description_ai_meta_description" style="width: 100%;" rows="4"><?php echo esc_textarea($meta_description) ?></textarea>
<button   id="seo-meta-description-ai-button" type="button" class="button">Replace with new SEO description</button>
<p />
<label for="seo-meta-description-ai-activation-code">Premium version activation code (optional):</label><br />
<input type="text" id="seo-meta-description-ai-activation-code" name="seo_meta_description_ai_activation_code" size="20" value="<?php echo esc_attr($activation_code) ?>" />&nbsp;&nbsp;
<a href="https://binarysolutions.biz/wordpress-plugins/seo-meta-description-ai/" target="_blank">Get the activation code</a>
<?php
}

/**
 * Save the meta box data
 */
function seo_meta_description_ai_save_meta_box_data($post_id) {

    if (!isset($_POST["seo_meta_description_ai_nonce"])) {
        return;
    }
    
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["seo_meta_description_ai_nonce"])), "seo_meta_description_ai_save_meta_box_data")) {
        return;
    }
    
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can("edit_post", $post_id)) {
        return;
    }
    
    $meta_description = isset($_POST["seo_meta_description_ai_meta_description"]) ? sanitize_text_field($_POST["seo_meta_description_ai_meta_description"]) : "";
    $activation_code  = isset($_POST["seo_meta_description_ai_activation_code"])  ? sanitize_text_field($_POST["seo_meta_description_ai_activation_code"])  : "";
    
    update_post_meta($post_id, "seo_meta_description_ai_meta_description", $meta_description);
    update_option("seo_meta_description_ai_activation_code", $activation_code);
}
add_action("save_post", "seo_meta_description_ai_save_meta_box_data");

/**
 * Output the SEO  meta description in the head section
 */
function seo_meta_description_ai_output() {
    
    $meta_description = "";
    if (is_singular()) {
        $meta_description = get_post_meta(
            get_the_ID(), 
            "seo_meta_description_ai_meta_description", 
            true
        );
    }
    
    if (!empty($meta_description)) {
?>

<!-- This site is optimized with the <?php echo esc_html(SEO_META_DESCRIPTION_AI_PLUGIN_NAME) ?> plugin v<?php echo esc_html(SEO_META_DESCRIPTION_AI_PLUGIN_VERSION) ?> - <?php echo esc_url(SEO_META_DESCRIPTION_AI_PLUGIN_URI) ?> -->
<meta name="description" content="<?php echo esc_attr($meta_description) ?>" />
<!-- / <?php echo esc_html(SEO_META_DESCRIPTION_AI_PLUGIN_NAME) ?> plugin. -->

<?php         
    }
}
add_action("wp_head", "seo_meta_description_ai_output", 1);

/**
 * 
 */
function seo_meta_description_ai_get_stripped_post_content($post) {
    
    $content = $post->post_content;
    $content = apply_filters("the_content", $content);
    $content = str_replace("<", " <", $content);
    $content = wp_strip_all_tags($content, true);
    
    if (strlen($content) > 0) {
        $content = $post->post_title . ". " . $content;
    } else {
        $content = $post->post_title;
    }
    
    return $content;
}

/**
 * Add JavaScript
 */
function seo_meta_description_ai_scripts() {
    
    wp_enqueue_script(
        "seo-meta-description-ai-scripts",
        plugin_dir_url( __FILE__ ) . "seo-meta-description-ai.js",
        array()
    );
    
    $content = seo_meta_description_ai_get_stripped_post_content(get_post());
    wp_localize_script(
        "seo-meta-description-ai-scripts",
        "data",
        array(
            "version"   => SEO_META_DESCRIPTION_AI_PLUGIN_VERSION,
            "host_name" => parse_url(get_bloginfo("url"), PHP_URL_HOST),
            "content"   => $content,
        )
    );
}
add_action("admin_enqueue_scripts", "seo_meta_description_ai_scripts");