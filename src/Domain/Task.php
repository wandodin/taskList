<?php
declare(strict_types=1);

namespace TaskList\Domain;

class Task {
    private $id;
    private $username;
    private $email;
    private $content;
    private $edited;
    private $done;

    public function __construct(int $id, string $username, string $email, string $content, bool $edited = NULL, bool $done = NULL) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->content = $content;
        $this->edited = $edited;
        $this->done = $done;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getEdited(): bool {
        return $this->edited;
    }

    public function getDone(): bool {
        return $this->done;
    }

    public function setId(int $id) {
        return $this->id = $id;
    }

    public function setUsername($username) {
        return $this->username = $username;
    }

    public function setEmail($email) {
        return $this->email = $email;
    }

    public function setContent($content) {
        return $this->content = $content;
    }

    public function setEdited($edited) {
        return $this->edited = $edited;
    }

    public function setDone($done) {
        return $this->done = $done;
    }

    public function __toString(): string {
        $result = 'Задача <i>' . $this->id . '</i> поставленная ' . $this->username;
        if (!$this->available) {
            $result .= ' <b>не доступна</b>';
        }
        return $result;
    }
}
