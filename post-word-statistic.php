<?php

/*
Plugin Name: Page Word Statistic
Description: Displays statistics about the current post page content
Version: 1.0.0
Author: Alex
Author URI: https://auco.pro/
Text Domain: wcplugin
Domain Path: /languages
*/


class PostWordCountPlugin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
        add_action('init', array($this, 'languages'));
    }

    public function languages()
    {
        load_plugin_textdomain('wcplugin', false, dirname(plugin_basename(__FILE__)), '/languages');
    }

    public function ifWrap($content)
    {
        if (
            is_main_query() and
            is_single() and
            (
                get_option('wcp_wordcount', '1') or
                get_option('wcp_charactercount', '1') or
                get_option('wcp_readtime', '1')
            )
        ) {
            return $this->createHTML($content);
        }
        return $content;
    }

    public function createHTML($content)
    {
        $html = '<h5>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h5><p>';

        //  word count
        if (get_option('wcp_wordcount', '1') or get_option('wcp_readtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if (get_option('wcp_wordcount', '1')) {
            $html .= esc_html(__('This post has', 'wcplugin')) . ' ' . $wordCount . ' ' . esc_html(__('words', 'wcplugin')) . '.<br>';
        }

        if (get_option('wcp_charactercount', '1')) {
            $html .= esc_html(__('This post has', 'wcplugin')) . ' ' . strlen(strip_tags($content)) . ' ' . esc_html(__('characters', 'wcplugin')) .  '.<br>';
        }

        if (get_option('wcp_readtime', '1')) {
            $html .= esc_html(__('This post will take about', 'wcplugin')) . ' ' . round($wordCount / 225) . ' ' . esc_html(__('minute(s) to read', 'wcplugin')) . '.<br>';
        }

        $html .= '</p>';

        if (get_option('wcp_location', '0') == '0') {
            return  $html . $content;
        }
        return $content . $html;
    }

    public function adminPage()
    {
        add_options_page('Word Count Settings', __('Word Count', 'wcplugin'), 'edit_posts', 'word-count-settings-page', array($this, 'settingsPageHTML'));
    }

    public function settings()
    {
        add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');
        // location
        add_settings_field('wcp_location', esc_html(__('Display Location', 'wcplugin')), array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', array('sanitize_callbacks' => array($this, 'sanitizeLocation'), 'default' => '0'));
        // headlines
        add_settings_field('wcp_headline', esc_html(__('Headline Text', 'wcplugin')), array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callbacks' => 'sanitize_text_filed', 'default' => 'Post Statistics'));
        // wordcount displays
        add_settings_field('wcp_wordcount', esc_html(__('Word Count', 'wcplugin')), array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
        register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callbacks' => 'sanitize_text_filed', 'default' => '1'));
        // Character count displays
        add_settings_field('wcp_charactercount', esc_html(__('Character Count', 'wcplugin')), array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_charactercount'));
        register_setting('wordcountplugin', 'wcp_charactercount', array('sanitize_callbacks' => 'sanitize_text_filed', 'default' => '1'));
        // Read time displays
        add_settings_field('wcp_readtime', esc_html(__('Read Time', 'wcplugin')), array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_readtime'));
        register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callbacks' => 'sanitize_text_filed', 'default' => '1'));
    }

    function checkboxHTML($args)
    { ?>
        <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName']), '1'); ?>>
    <?php }

    function headlineHTML()
    { ?>
        <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')); ?>">
    <?php }

    function settingsPageHTML()
    { ?>
        <div class="wrap">
            <h1><?php echo esc_html(__('Word Count Settings', 'wcplugin')); ?></h1>
            <form action="options.php" method="POST">
                <?php
                settings_fields('wordcountplugin');
                do_settings_sections('word-count-settings-page');
                submit_button(); ?>
            </form>
        </div>

    <?php }

    function sanitizeLocation($input)
    {
        if ($input != '0' and $input != '1') {
            add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either begining or end.');
            return get_option('wcp_location');
        }
    }

    function locationHTML()
    { ?>
        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'), '0') ?>><?php echo esc_html(__('Begining of post', 'wcplugin')); ?></option>
            <option value="1" <?php selected(get_option('wcp_location'), '1') ?>><?php echo esc_html(__('End of post', 'wcplugin')); ?></option>
        </select>
<?php  }
}



$postWordCountPlugin = new PostWordCountPlugin();
