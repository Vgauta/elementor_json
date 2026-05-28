<?php

namespace AIEB\Elementor;

if (! defined('ABSPATH')) {
    exit;
}

class Json_Validator
{
    public function validate(array $json): bool
    {
        return isset($json['title'], $json['type'], $json['content']) && is_array($json['content']);
    }
}
