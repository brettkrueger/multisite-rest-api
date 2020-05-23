<?php
/**
 * Plugin Name: Multisite Rest API
 * Plugin URI: https://krux.us/multisite-rest-api/
 * Description: Adds multisite functionality to Rest API
 * Version: 1.0
 * Author: Brett Krueger
 * Author URI: https://krux.us
 */
include_once(get_template_directory()."/../../../wp-includes/ms-site.php");
include_once(get_template_directory()."/../../../wp-includes/ms-functions.php");
#include_once(get_template_directory()."/../../../wp-admin/ms-admin.php");


/* Setup routes for site management */

add_action( 'rest_api_init', function () {
  register_rest_route('wp/v2', '/sites/?(?P<blog_id>\d+)?', [
    'methods'  => "GET",
    'callback' => 'sites_callback',
    'args' => [
      'blog_id',
    ],
  ]);
});

add_action( 'rest_api_init', function () {
  $now = current_time( 'mysql', true );
  register_rest_route('wp/v2', '/sites/create', [
    'methods'  => "POST",
    'callback' => 'sites_callback',
    'args' => [
      'domain',
  		'path',
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
  register_rest_route('wp/v2', '/sites/update', [
    'methods'  => "PUT",
    'callback' => 'sites_callback',
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
    'callback' => 'sites_callback',
    'args' => [
      'blog_id',
    ],
  ]);
});
/* End Site Route setup */

/* Begin Sites Callback */
function sites_callback( $request ) {
    if ( is_multisite() ) {
      $params = $request->get_params();
      $route = $request->get_route();
      $user = get_current_user_id();
      if(! $params["blog_id"]) {
        $site_id = explode('/', $matches[0])[1];
      } else {
        $site_id = $params["blog_id"];
      }
      // v This currently only works when using a route, not params. v
      if(preg_match("/sites\/.$/", $route, $matches)) {
        return rest_ensure_response( get_site( $site_id ));
      }
      elseif(preg_match("/sites\/?$/", $route)) {
        $user_sites = get_blogs_of_user( $user );
        return rest_ensure_response( get_sites( $user_sites ));
      // ^ This currently only works when using a route, not params. ^
      }
      elseif (preg_match("/sites\/create/", $route, $matches)) {
        if(! current_user_can('create_sites')){
          return $user;
          return rest_ensure_response('Invalid user permissions.');
          die();
        } else {
          $domain = $params["domain"];
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
          $options = [
            "admin_email" => $params["admin_email"],
          ];
          return rest_ensure_response(wpmu_create_blog($domain, $path, $title, $user_id, $options));
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
