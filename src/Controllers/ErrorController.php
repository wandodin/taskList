<?php
declare(strict_types=1);

namespace TaskList\Controllers;

class ErrorController extends AbstractController {
    public function notFound(): string {
        $properties = ['errorMessage' => 'Страница не найдена!'];
        return $this->render('error.twig', $properties);
    }
}