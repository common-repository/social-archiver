<?php

namespace SocialArchiver\Auth;

class TwitterAuth
{
    /**
     * Get token from twitter
     *
     * @since 1.0
     *
     * @see    https://dev.twitter.com/oauth/reference/post/oauth2/token
     * @see    https://dev.twitter.com/oauth/application-only
     *
     * @param  string $consumer_key
     * @param  string $consumer_secret
     * @return string|false
     */
    public function get_token($consumer_key, $consumer_secret)
    {
        $args = array(
            'method'      => 'POST',
            'body'        => array(
                'grant_type' => 'client_credentials'
            ),
            'headers'     => array(
                'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret),
                'Content-Type'  => 'application/x-www-form-urlencoded;charset=UTF-8',
            ),
        );

        $response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);

        if (wp_remote_retrieve_response_code($response) != 200) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));

        return $body->access_token;
    }
}
