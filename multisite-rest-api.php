<?php
/**
 * Plugin Name: Multisite Rest API
 * Plugin URI: https://krux.us/multisite-rest-api/
 * Description: Adds multisite functionality to Rest API
 * Version: 1.3.0
 * Author: Brett Krueger
 * Author URI: https://krux.us
 */

/* Setup routes for site management */

add_action( 'rest_api_init', function () {
  register_rest_route('wp/v2', '/sites/?(?P<blog_id>\d+)?', [
    'methods'  => "GET",
    'callback' => 'wmra_sites_callback',
    'args' => [
      'blog_id',
    ],
  ]);
});

add_action( 'rest_api_init', function () {
  $now = current_time( 'mysql', true );
  register_rest_route('wp/v2', '/sites/create', [
    'methods'  => "POST",
    'callback' => 'wmra_sites_callback',
    'args' => [
      'domain',
  		'path',
  		'title',
      'email',
      'password',
      'user_id',
  		'network_id'   => get_current_network_id(),
  		'registered'   => $now,
  		'last_updated' => $now,
  		'public'       => 1,
  		'archived'     => 0,
  		'mature'       => 0,
  		'spam'         => 0,
  		'deleted'      => 0,
  		'lang_id'      => 0,
    ],
  ]);
});

add_action( 'rest_api_init', function () {
  $now = current_time( 'mysql', true );
  register_rest_route('wp/v2', '/sites/assign', [
    'methods'  => "PATCH",
    'callback' => 'wmra_sites_callback',
    'args' => [
	'user_id',
	'blog_id',
	'role',
    ],
  ]);
});

add_action( 'rest_api_init', function () {
  register_rest_route('wp/v2', '/sites/update', [
    'methods'  => "PUT",
    'callback' => 'wmra_sites_callback',
    'args' => [
      'blog_id',
      'option',
      'value',
    ],
  ]);
});

add_action( 'rest_api_init', function () {
  register_rest_route('wp/v2', '/sites/delete/?(?P<blog_id>\d+)?', [
    'methods'  => "DELETE",
    'callback' => 'wmra_sites_callback',
    'args' => [
      'blog_id',
    ],
  ]);
});
/* End Site Route setup */

/**
 * Creates a new user if one doesn't already exist.
 * If it does exist, just returns the existing user's id.
 * Sanitizes email address automatically.
 * @param dirty_email string An unsanitized email
 * @param username string The username
 * @return user WP_User The Wordpress user object
 */
function get_or_create_user_by_email($dirty_email, $username, $password = null) {
  $email = sanitize_email($dirty_email);
  if ($email === "") return false;
  $user = get_user_by('email', $email);
  if ($user) return $user;
  // if the email doesn't exist, lets check for the login
  // which we create from the domain
  $user = get_user_by('login', $username);
  if ($user) return $user;
  // Create a new user with a random password
  if ($password === null) $password = wp_generate_password(12, false);
  $user_id = wpmu_create_user($username, $password, $email);
  wp_new_user_notification($user_id, $password);
  // Its possible for the $user_id to be false here, but it seems to
  // be only in extreme cases where the database is failing or some
  // other odd circumstance happens
  return (get_user_by('id', $user_id));
}

/* Begin Sites Callback */
function wmra_sites_callback( $request ) {
    if ( is_multisite() ) {
      $params = $request->get_params();
      $route = $request->get_route();
      $user = get_current_user_id();
      if(! $params["blog_id"]) {
        $site_id = explode('/', $matches[0])[1];
      } else {
        $site_id = $params["blog_id"];
      }
      if($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match("/sites\/?/", $route, $matches)) {
        if(($params["blog_id"]) || (preg_match("/sites\/\d\/?/", $route, $matches))) {
          if(! is_user_member_of_blog($user, $site_id)) {
            return rest_ensure_response('Invalid user permissions.');
          } else {
            return rest_ensure_response( get_site( $site_id ));
          }
        }
        elseif(preg_match("/sites\/?$/", $route, $matches)) {
          if(! current_user_can('create_sites')) {
            $user_sites = get_blogs_of_user( $user );
            return rest_ensure_response( get_sites( $user_sites ));
          } else {
            return rest_ensure_response( get_sites());
          }
        }
      }
      elseif (preg_match("/sites\/create/", $route, $matches)) {
        if(! current_user_can('create_sites')){
          return rest_ensure_response('Invalid user permissions.');
          die();
        } else {
          if(! $params["domain"]) {
            return rest_ensure_response('You must provide a domain.');
            die();
          } else {
            $domain = $params["domain"];
          }
          if(! $params["path"]) {
            $path = '/';
          } else {
            $path = $params["path"];
          }
          if (! $params["title"]) {
            $title = $domain;
          } else {
            $title = $params["title"];
          }
          if (! $params["user_id"]) {
            $user_id = $user;
          } else {
            $user_id = $params["user_id"];
          }
          if ($params["email"]) {
            $user = get_or_create_user_by_email($params["email"], $params["email"], $params["password"] ? $params["password"] : null);
            if (!$user) {
              return rest_ensure_response('Email is invalid.');
              die();
            }
            $user_id = $user->ID;
          }
          return rest_ensure_response(wpmu_create_blog($domain, $path, $title, $user_id));
        }
      }
      elseif (preg_match("/sites\/assign/", $route, $matches)) {
        if(! current_user_can('update_sites')){
          return rest_ensure_response('Invalid user permissions.');
          die();
        } else {
          $user_id = $params["user_id"] ?? $user;
          $blog_id = $params["blog_id"];
          $role = $params["role"];
          add_user_to_blog( $blog_id, $user_id, $role );
          return rest_ensure_response('User '.$user_id.' added to blog id '.$blog_id.'.');
        }
  	  }
      elseif (preg_match("/sites\/update/", $route, $matches)) {
        if(! current_user_can('update_sites')){
          return rest_ensure_response('Invalid user permissions.');
          die();
        } else {
          $option = $params["option"];
          $value = $params["value"];
          return rest_ensure_response(update_blog_option($site_id, $option, $value));
        }
        return $route;
      }
      elseif (preg_match("/sites\/delete/", $route, $matches)) {
        if(! current_user_can('delete_sites')){
          return rest_ensure_response('Invalid user permissions.');
          die();
        } else {
          return rest_ensure_response(wp_delete_site($site_id));
        }
        return $route;
      }
      else {
        return rest_ensure_response('Invalid REST Route.');
        die();
      }
    } else {
      return rest_ensure_response('This is not a Multisite install. Please enable Multisite to make use of this endpoint.');
      die();
    }
}
/* End Sites Callback */

?>
