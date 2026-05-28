<?php
if (!defined('CMS_VERSION')) {
    exit;
}

/**
 * @param array<string,mixed> $params
 * @param string $id CMSMS action id prefix
 * @return string
 */
function mas_md_get_request_action($params, $id)
{
    if (!empty($_REQUEST['mas_action'])) {
        return (string) $_REQUEST['mas_action'];
    }
    if (!empty($params['mas_action'])) {
        return (string) $params['mas_action'];
    }
    if (!is_string($id) || $id === '') {
        return '';
    }
    $prefixed = $id . 'mas_action';
    if (!empty($_REQUEST[$prefixed])) {
        return (string) $_REQUEST[$prefixed];
    }
    if (!empty($params[$prefixed])) {
        return (string) $params[$prefixed];
    }

    return '';
}

/**
 * @param array<string,mixed> $params
 * @param string $id
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function mas_md_request_param($params, $id, $key, $default = null)
{
    if (isset($_REQUEST[$key])) {
        return $_REQUEST[$key];
    }
    if (isset($params[$key])) {
        return $params[$key];
    }
    if (is_string($id) && $id !== '') {
        $prefixed = $id . $key;
        if (isset($_REQUEST[$prefixed])) {
            return $_REQUEST[$prefixed];
        }
        if (isset($params[$prefixed])) {
            return $params[$prefixed];
        }
    }

    return $default;
}
