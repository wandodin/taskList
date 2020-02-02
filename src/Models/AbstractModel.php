<?php
declare(strict_types=1);

namespace TaskList\Models;

abstract class AbstractModel {
    protected $file;

    public function __construct(string $file) {
        $this->file = $file;
    }
}