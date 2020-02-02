<?php
declare(strict_types=1);

namespace TaskList\Controllers;

use TaskList\Exceptions\NotFoundException;

class UserController extends AbstractController {

    private $errorMessage;

    public function login(): string {
        $cookies = $this->request->getCookies();
        if ($cookies->has('user')) {
            $newController = new TaskController($this->di, $this->request);
            return $newController->getAll();
        }

        if (!$this->request->isPost()) {
            return $this->render('login.twig', ['guest' => true]);
        }

        if($this->validate()) {
            try {
                setcookie('user', 'admin', time()+3600, '/');
                header("Location: /");
                $newController = new TaskController($this->di, $this->request);
                return $newController->getAll();
            } catch (\Exception $e) {
                $this->log->error('Ошибка получения данных: ' . $e->getMessage());
                $properties = ['errorMessage' => 'Ошибка получения данных'];
                return $this->render('error.twig', $properties);
            }
        } else {
            $properties = [
                'errorMessage' => $this->errorMessage,
                'guest' => true
            ];
            return $this->render('login.twig', $properties);
        }
    }

    public function logout(): string {
        setcookie('user', '', -1, '/');
        header("Location: /");
        $newController = new TaskController($this->di, $this->request);
        return $newController->getAll();
    } 

    private function validate(): bool {
        try {
            $params = $this->request->getParams();
            $this->errorMessage = '';
            
            $username = $params->getString('username') ?? false;
            if (!$username) {
                $this->errorMessage = 'Не указано имя пользователя';
                $this->log->error($this->errorMessage);
                return false;
            } else {
                $username = $params->getString('username');
            }
            
            $password = $params->getString('password') ?? false;
            if (!$password) {
                $this->errorMessage = 'Не указан пароль';
                $this->log->error($this->errorMessage);
                return false;
            } else {
                $password = $params->getString('password');
            }

            $admin = $this->config->get('admin');
            if($username != $admin['user']) {
                $this->errorMessage = 'Не существует такого пользователя ' . $username;
                $this->log->error($this->errorMessage);
                return false;
            } elseif ($password != $admin['password']) {
                $this->errorMessage = 'Неправильный пароль';
                $this->log->error($this->errorMessage);
                return false;           
            } 
        } catch (\Exception $e) {
            $this->log->error('Ошибка получения данных: ' . $e->getMessage());
            $properties = ['errorMessage' => 'Ошибка получения данных'];
            return $this->render('error.twig', $properties);
        }

        return true;
    } 
}