<?php
# Emulate the awesome Buffer, bufferapp.com
add_action('init', 'buf_init');
add_action('admin_enqueue_scripts', 'buf_enqueue_scripts');

function buf_init() {
  $labels = array(
    'name' => _x('Scheduled Posts', 'post type general name'),
    'singular_name' => _x('Post', 'post type singular name'),
    'add_new' => _x('Add New', 'buffer'),
    'add_new_item' => __('Add New Scheduled Post'),
    'edit_item' => __('Edit Scheduled Post'),
    'new_item' => __('New Scheduled Post'),
    'all_items' => __('All Scheduled Posts'),
    'view_item' => __('View Scheduled Post'),
    'search_items' => __('Search Schedule'),
    'not_found' =>  __('No scheduled posts found'),
    'not_found_in_trash' => __('No scheduled posts found in Trash'), 
    'parent_item_colon' => '',
    'menu_name' => __('Schedule')
  );

  $args = array(
    'labels' => $labels,
    'public' => false,
    'publicly_queryable' => false,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => false,
    'rewrite' => false,
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => 2,
    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
  ); 

  register_post_type('buffer', $args);

  $args = array(
    'public' => false,
    'publicly_queryable' => false,
    'show_ui' => false, 
    'show_in_menu' => false, 
    'query_var' => false,
    'rewrite' => false,
    'capability_type' => 'post',
    'has_archive' => false, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
  ); 

  register_post_type('profile', $args);
}

function buf_enqueue_scripts($hook) {
  $screen = get_current_screen();
  if ($screen->id == 'buffer') {
    wp_enqueue_script('buffer', plugins_url('js/buffer.js', SHAREPRESS), array('jquery'));
    wp_enqueue_style('buffer-admin', plugins_url('css/admin.css', SHAREPRESS));
  }
}

function buf_has_facebook() {
  $id = sp_get_opt('fb_app_id', constant('SP_FB_APP_ID'));
  $secret = sp_get_opt('fb_secret', constant('SP_FB_SECRET'));

  if ($id && $secret) {
    return (object) array(
      'id' => $id,
      'secret' => $secret
    );  
  }
  return false;
}

/**
 * @return Facebook client, when configured; otherwise, null.
 * @see buf_has_facebook()
 */
function &buf_facebook() {
  global $facebook;

  if (is_null($facebook)) {
    if ($keys = buf_has_facebook()) {
      @session_start();  
      $facebook = new Facebook(array(
        'appId' => $keys->id,
        'secret' => $keys->secret
      ));
    }
  }

  return $facebook;
}

function buf_get_profiles($args = '') {

}

function buf_update_schedule($profile_id, $schedule) {

}

function buf_update_profile($settings, $user = false) {
  /*
           "avatar" : "http://a3.twimg.com/profile_images/1405180232.png",
    "created_at" :  1320703028,
    "default" : true,
    "formatted_username" : "@skinnyoteam",
    "id" : "4eb854340acb04e870000010",
    "schedules" : [{ 
        "days" : [ 
            "mon",
            "tue",
            "wed",
            "thu",
            "fri"
        ],
        "times" : [ 
            "12:00",
            "17:00",
            "18:00"
        ]
    }],
    "service" : "twitter",
    "service_id" : "164724445",
    "service_username" : "skinnyoteam",
    "statistics" : { 
        "followers" : 246 
    },
    "team_members" : [
        "4eb867340acb04e670000001"
    ],
    "timezone" : "Europe/London",
    "user_id" : "4eb854340acb04e870000010"
    */
}

function buf_remove_profile($settings, $user = false) {

}
