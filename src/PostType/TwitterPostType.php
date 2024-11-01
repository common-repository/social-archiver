<?php

namespace SocialArchiver\PostType;

class TwitterPostType
{
    /**
     * @var string
     */
    protected static $post_type_name = 'sa_twitter';

    /**
     * The constructor
     *
     * @since 1.0
     */
    public function __construct()
    {
        $this->register_post_type();
    }

    /**
     * Getter for the post type name
     *
     * @return string
     */
    public static function get_post_type_name()
    {
        return self::$post_type_name;
    }

    /**
     * Register the twitter post type
     *
     * @since 1.0
     */
    protected function register_post_type()
    {
        $args = apply_filters(
            'sa_twitter_post_type_args_filter',
            array(
                'labels' => array(
                    'name'               => __('Twitter', 'social-archiver'),
                    'singular_name'      => __('Twitter', 'social-archiver'),
                    'menu_name'          => __('Twitter', 'social-archiver'),
                    'name_admin_bar'     => __('Twitter', 'social-archiver'),
                    'add_new'            => __('Add New', 'social-archiver'),
                    'add_new_item'       => __('Add New Tweet', 'social-archiver'),
                    'new_item'           => __('New Tweet', 'social-archiver'),
                    'edit_item'          => __('Edit Tweet', 'social-archiver'),
                    'view_item'          => __('View Tweet', 'social-archiver'),
                    'all_items'          => __('All Tweets', 'social-archiver'),
                    'search_items'       => __('Search Tweets', 'social-archiver'),
                    'parent_item_colon'  => __('Parent Tweets:', 'social-archiver'),
                    'not_found'          => __('No tweet found.', 'social-archiver'),
                    'not_found_in_trash' => __('No tweets found in Trash.', 'social-archiver'),
                ),
                'has_archive'        => false,
                'public'             => true,
                'publicly_queryable' => true,
                'capability_type'    => 'post',
                'menu_icon'          => 'dashicons-twitter',
                'menu_position'      => 20,
                'query_var'          => true,
                'rewrite'            => array(
                    'slug' => 'twitter'
                ),
                'show_in_menu'       => true,
                'show_ui'            => true,
                'supports'           => array('title', 'editor', 'custom-fields'),
                'taxonomies'         => array('post_tag')
            )
        );

        register_post_type(
            self::$post_type_name,
            $args
        );
    }
}
