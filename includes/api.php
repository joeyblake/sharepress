<?php
# RESTful API - not to be confused with AJAX stuff
class SpApi_v1 extends AbstractSpApi {

  function oauth($service) {
    if (!$service) {
      return false;
    }

    if (!is_user_logged_in()) {
      // TODO: more sophisticated error here
      return false;
    }

    $error = $user = false;

    if ($service == 'facebook') {
      if (buf_has_facebook()) {
        if ($user = buf_facebook()->getUser()) {
          try {
            $user = buf_facebook()->api('/me');
          } catch (Exception $e) {
            $error = $e;
            // TODO: figure out all of the possibilities
          }
        }

        if (!$user && !$error) {
          wp_redirect( buf_facebook()->getLoginUrl(array(
            'scope' => 'read_stream,publish_stream,manage_pages,share_item'
          )) );
        } else {
          buf_update_profile(array(
            'service' => 'facebook',
            'service_id' => $user->id,
            'formatted_username' => $user->username,
            'service_username' => $user->username,
            'avatar' => 'https://graph.facebook.com/'.$user->id.'/picture'
          ));

          if (empty($_REQUEST['redirect_uri'])) {
            $_REQUEST['redirect_uri'] = 'edit.php?post_type=buffer';
          }
          if (!$host = parse_url($_REQUEST['redirect_uri'], PHP_URL_HOST)) {
            wp_redirect( admin_url($_REQUEST['redirect_uri']) );
          } else {
            wp_redirect( $_REQUEST['redirect_uri'] );
          }
        } 
      }
    }
  }

  function updates() {

  }

  function profiles() {

  }

  function debug($fx) {
    if (constant('SP_DEBUG') && current_user_can('admin')) {
      // isolate to SharePress namespace
      list($ns) = explode('_', $fx);
      if (!in_array($ns, array('sp', 'buf'))) {
        wp_die('Not allowed to do that.');
      }
      if (!isset($_REQUEST['args']) || !is_array($_REQUEST['args'])) {
        $_REQUEST['args'] = array();
      }
      return @call_user_func_array($fx, $_REQUEST['args']);
    }
  }

}

abstract class AbstractSpApi {
  static function _method() {
    $headers = apache_request_headers();
    if (!empty($headers['X-HTTP-Method-Override'])) {
      return strtolower($headers['X-HTTP-Method-Override']);
    } else if (!empty($_REQUEST['_method'])) {
      return strtolower(trim($_REQUEST['_method']));
    } else {
      return strtolower($_SERVER['REQUEST_METHOD']);
    }
  }

  function _isGet() {
    return self::_method() == 'get';
  }

  function _isPost() {
    return self::_method() == 'post';
  }

  function _isPut() {
    return self::_method() == 'put';
  }

  function _isDelete() {
    return self::_method() == 'delete';
  }
}

add_filter('rewrite_rules_array', 'sp_rewrite_rules_array');
add_filter('query_vars', 'sp_query_vars');
add_action(constant('WP_DEBUG') || constant('SP_DEBUG') ? 'wp_loaded' : 'sp_activated', 'sp_flush_rewrite_rules');
add_action('parse_request', 'sp_parse_request');

// e.g., sp/1/profiles/:id
define('SP_API_REWRITE_RULE', '(sp)/(\d)/([a-z]+)(/(.*?))*(.json)?$');

function sp_rewrite_rules_array($rules) {
  return array(
    SP_API_REWRITE_RULE => 'index.php?_sp=$matches[3]&_v=$matches[2]&_args=$matches[5]'
  ) + $rules;
}

function sp_query_vars($vars) {
  $vars[] = '_sp';
  $vars[] = '_v';
  $vars[] = '_args';
  return $vars;
}

function sp_flush_rewrite_rules() {
  $rules = get_option( 'rewrite_rules' );
  if (!isset($rules[SP_API_REWRITE_RULE])) {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }
}

function sp_parse_request($wp) {
  if (isset($wp->query_vars['_sp'])) {
    $class = "SpApi_v{$wp->query_vars['_v']}";
    if (!class_exists($class)) {
      return;
    }
    $api = new $class();
    $fx = array($api, $wp->query_vars['_sp']);
    if (is_callable($fx)) {
      $result = call_user_func_array($fx, explode('/', $wp->query_vars['_args']));
      header('Content-Type: application/json');
      echo json_encode($result);
      exit(0);
    }  
  }
}