<?php
declare(strict_types=1);

namespace TaskList\Core;

use TaskList\Exceptions\NotFoundException;

class Config {
    private $data;

    public function __construct() {
        $json = file_get_contents(__DIR__ . '/../../config/app.json');
        $this->data = json_decode($json, true);
    }

    public function get($key) {
        if (!isset($this->data[$key])) { echo 'here';
            throw new NotFoundException("Ключ $key отсутствует в конфигурации.");
        }
        return $this->data[$key];
    }
}