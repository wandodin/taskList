<?php
declare(strict_types=1);

namespace TaskList\Controllers;

use TaskList\Core\Request;
use TaskList\Utils\DependencyInjector;

abstract class AbstractController {

    const GUEST = 'Гость';

    protected $request;
    protected $file;
    protected $config;
    protected $view;
    protected $log;
    protected $di;
    protected $auth;
    protected $user;

    public function __construct(DependencyInjector $di, Request $request) {
        $this->request = $request;
        $this->di = $di;

        $this->file = $di->get('File');
        $this->log = $di->get('Logger');
        $this->view = $di->get('Twig');
        $this->config = $di->get('Utils\Config');
        $cookies = $this->request->getCookies();
        $this->auth = $cookies->has('user');
        $this->user = $cookies->has('user') ? $this->config->get('admin')['user'] : self::GUEST;
    }

    protected function render(string $template, array $params): string {
        return $this->view->load($template)->render($params);
    }
}