<?php
declare(strict_types=1);

namespace TaskList\Utils;

use TaskList\Exceptions\NotFoundException;

class DependencyInjector {
    private $dependencies = [];

    public function set(string $name, $object) {
        $this->dependencies[$name] = $object;
    }

    public function get(string $name) {
        if (isset($this->dependencies[$name])) {
            return $this->dependencies[$name];
        }
        throw new NotFoundException($name . ' зависимость не найдена.');
    }
}