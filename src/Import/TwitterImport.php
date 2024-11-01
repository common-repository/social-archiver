<?php

namespace SocialArchiver\Import;

use SocialArchiver\PostType\TwitterPostType;

class TwitterImport
{
    /**
     * @var string
     */
    protected $api_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';

    /**
     * Getter for query arg default values
     *
     * @since 1.0
     *
     * @return array
     */
    protected function get_default_query_arg()
    {
        $options = get_option('social_archiver');

        $default_query_arg = apply_filters(
            'sa_twitter_import_default_query_arg_filter',
            array(
                'screen_name'     => urlencode($options['twitter_username']),
                'count'           => 25,
                'exclude_replies' => false,
                'trim_user'       => true
            )
        );

        return $default_query_arg;
    }

    /**
     * Call twitter api
     *
     * @since 1.0
     *
     * @param  array $args
     * @return array
     */
    protected function call_api($args)
    {
        $options = get_option('social_archiver');

        $url     = add_query_arg($args, $this->api_url);
        $headers = array('Authorization' => 'Bearer ' . $options['twitter_token']);

        return wp_remote_get($url, array(
            'headers' => $headers
        ));
    }

    /**
     * Check if the API returned an error, die if yes, return true otherwise
     *
     * @since 1.0
     *
     * @param  integer $status_code
     * @return boolean
     */
    protected function check_status_code($status_code)
    {
        if ($status_code != 200) {
            if ($status_code == 401) {
                $message = __('Twitter API return a 401 error, please check your consumer key and consumer secret.', 'social-archiver');
            } else if ($status_code == 403) {
                $message = __('Twitter API return a 403 error, have you reached the limit ?', 'social-archiver');
            } else {
                $message = sprintf(
                    __('Twitter API return a %1$d error, please reload and retry.', 'social-archiver'),
                    $status_code
                );
            }

            wp_die($message);
        }

        return true;
    }

    /**
     * Import tweets not imported yet
     *
     * @since 1.0
     */
    public function import()
    {
        $options = get_option('social_archiver');

        // Set args and call the api
        $args = $this->get_default_query_arg();

        if (isset($options['max_collected_id'])) {
            $args['since_id'] = $options['max_collected_id'];
        }

        $response = $this->call_api($args);

        // Check if the API returned an error
        $tweets      = json_decode(wp_remote_retrieve_body($response));

        $status_code = wp_remote_retrieve_response_code($response);

        while (!empty($tweets)) {
            $this->check_status_code($status_code);

            $max_collected_id = $options['max_collected_id'];

            // Save tweet
            foreach ($tweets as $tweet) {
                $post_id = $this->save_tweet($tweet);

                if ($post_id) {
                    if ($tweet->id_str > $max_collected_id) {
                        $max_collected_id = $tweet->id_str;
                    }
                } else {
                    // something went wrong
                }
            }

            // Save the last published tweet id
            $options['max_collected_id'] = $max_collected_id;
            update_option('social_archiver', $options);

            // Wait 1 second and restart the process
            sleep(1);
            $tweets      = json_decode(wp_remote_retrieve_body($response));
            $status_code = wp_remote_retrieve_response_code($response);
        }
    }

    /**
     * Collect and import all tweets
     *
     * @todo  Do something if something went wrong
     *
     * @see   https://dev.twitter.com/oauth/application-only
     * @see   https://dev.twitter.com/rest/reference/get/statuses/user_timeline
     *
     * @since 1.0
     */
    public function import_all()
    {
        check_admin_referer('social_archiver_import_all_tweets');

        ?>

        <h2><?php _e('Archiving tweets', 'social-archiver'); ?></h2>

        <?php

        $options = get_option('social_archiver');

        // Set args and call the api
        $args = $this->get_default_query_arg();

        if (isset($_GET['twitter_min_collected_id'])) {
            $args['max_id'] = esc_attr($_GET['twitter_min_collected_id']);
        }

        $response = $this->call_api($args);

        $tweets      = json_decode(wp_remote_retrieve_body($response));
        $status_code = wp_remote_retrieve_response_code($response);

        // Check if the API returned an error
        $this->check_status_code();

        // If there is no tweet in response, assuming the import is done
        if (empty($tweets)) {
            wp_die(__('Well done, import is done!', 'social-archiver'));
        }

        $min_collected_id = null;
        $max_collected_id = isset($options['max_collected_id']) ? $options['max_collected_id'] : 0;

        // Save tweet
        foreach ($tweets as $tweet) {
            $post_id = $this->save_tweet($tweet);

            if ($post_id) {
                if (!$min_collected_id || $tweet->id_str < $min_collected_id) {
                    $min_collected_id = $tweet->id_str;
                }

                if ($tweet->id_str > $max_collected_id) {
                    $max_collected_id = $tweet->id_str;
                }
            } else {
                // something went wrong
            }
        }

        // Save the last published tweet id, will be used for automatic archiving
        $options['max_collected_id'] = $max_collected_id;
        update_option('social_archiver', $options);

        // reload the page to continue the import
        $url = wp_nonce_url(
            admin_url('options-general.php?page=social_archiver&action=import_all_tweets&twitter_min_collected_id=' . $min_collected_id),
            'social_archiver_import_all_tweets'
        );
        $this->reload($url, 2);
    }

    /**
     * Save a tweet or return the post_id if the tweet has already been imported
     *
     * @since 1.0
     *
     * @param  stdClass $tweet
     * @return integer
     */
    protected function save_tweet($tweet)
    {
        $post = new \WP_Query('post_type=' . TwitterPostType::get_post_type_name() . '&posts_per_page=1&meta_key=tweet_id&meta_value=' . $tweet->id_str);

        if ($post->post_count > 0) {
            return $post->post->ID;
        }

        $is_rewteet = isset($tweet->retweeted_status);
        $date       = strtotime($tweet->created_at) + 3600 * get_option('gmt_offset');
        $content    = !$is_rewteet ? $tweet->text : $tweet->retweeted_status->text;

        $post = array(
            'post_type'    => TwitterPostType::get_post_type_name(),
            'post_title'   => wp_strip_all_tags($tweet->text),
            'post_content' => $content,
            'post_date'    => date('Y-m-d H:i:s', $date),
            'post_status'  => 'publish',
        );

        $post_id = wp_insert_post($post);

        add_post_meta($post_id, 'tweet_id', $tweet->id_str);
        add_post_meta($post_id, 'tweet_author', $tweet->user->id_str);
        add_post_meta($post_id, 'tweet_is_retweet', $is_rewteet);

        wp_set_post_tags($post_id, $this->get_hashtags($tweet));

        return $post_id;
    }

    /**
     * Get hashtags from a tweet
     *
     * @since 1.0
     *
     * @param  stdClass $tweet
     * @return array
     */
    protected function get_hashtags($tweet)
    {
        $hashtags = array();

        foreach ($tweet->entities->hashtags as $hashtag) {
            $hashtags[] = $hashtag->text;
        }

        return $hashtags;
    }

    /**
     * Display a countdown before reloading the page
     * I agree this is not an elegant way but, have you a better idea ?
     *
     * @since 1.0
     *
     * @param  string  $url
     * @param  integer $timer
     */
    protected function reload($url, $timer = 10)
    {
        $url = html_entity_decode($url);

        printf(
            __('%1$sArchiving in progress, don\'t leave this page! The page will reload in %2$s second(s)%3$s', 'social-archiver'),
            '<p>',
            '<span id="countdown">' . $timer . '</span>',
            '</p>'
        );

        ?>

        <script type='text/javascript'>
            var now         = new Date(),
                target_date = new Date(now.getTime() + <?php echo $timer ?> * 1000).getTime(),
                delay        = 100,
                timeout;

            function social_archiver_display_countdown(){
                var current_date = new Date().getTime(),
                    seconds_left = (target_date - current_date) / 1000;

                if (seconds_left > 0) {
                    jQuery('#countdown').html(seconds_left.toFixed(1));
                    timeout = setTimeout('social_archiver_display_countdown()', delay);
                } else {
                    jQuery('#countdown').html(0);
                    clearTimeout(timeout);
                    window.location = "<?php echo $url; ?>";
                }
            }

            social_archiver_display_countdown();
        </script>
        <?php
    }
}
