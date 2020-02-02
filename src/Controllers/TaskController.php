<?php
declare(strict_types=1);

namespace TaskList\Controllers;

use TaskList\Exceptions\NotFoundException;
use TaskList\Models\TaskModel;
use TaskList\Domain\Task;

class TaskController extends AbstractController {
    const PAGE_LENGTH = 3;
    private $errorMessage;
    private $successMessage;
    private $error;

    public function getAllWithPage($page, $sortBy = NULL, $order = NULL): string {

        $page = (int)$page;
        $taskModel = new TaskModel($this->file);
        $params = $this->request->getParams();
        $sortBy = $params->getString('sortby') ?? NULL;
        $order = $params->getString('order') ?? NULL;
        $tasks = $taskModel->getAll($page, self::PAGE_LENGTH, $sortBy, $order);

        $properties = [
            'auth' => $this->auth,
            'user' => $this->user,
            'tasks' => $tasks,
            'sortby' => $sortBy,
            'order' => $order,
            'pagesCount' => ceil($taskModel->getTotal()/self::PAGE_LENGTH),
            'currentPage' => $page,
            'lastPage' => ceil($taskModel->getTotal()/self::PAGE_LENGTH) == $page
        ];
        return $this->render('tasks.twig', $properties);
    }

    public function getAll(): string { 
        return $this->getAllWithPage(1);
    }

    public function create(): string {
        if (!$this->request->isPost()) {
            $properties = [
                'auth' => $this->auth,
                'user' => $this->user,
                'form' => true
            ];
            return $this->render('task_create.twig', $properties);
        } 

        $params = $this->request->getParams();
        $this->error = false;
        $errorMessageTemp;
        $this->successMessage;
        $username = $params->getString('username') ?? '';
        if (empty($username)) {
            $errorMessageTemp[] = 'Не указано имя пользователя';
            $this->error = true;
        } 
        $email = $params->getString('email') ?? '';
        if (empty($email)) {
            $errorMessageTemp[] = 'Не указан email';
            $this->error = true;
        }
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessageTemp[] = 'Не валиден email';
            $this->error = true;
        }
        $content = $params->getString('content') ?? '';
        if (empty($content)) {
            $errorMessageTemp[] = 'Не сформулирована задача';
            $this->error = true;
        };
        $taskModel = new TaskModel($this->file);
        $newTask = new Task($taskModel->getMaxId(), $username, $email, $content, false, false);

        if(!$this->error) {
            try {
                $task = $taskModel->create($newTask);;
                $this->successMessage = "Задача $task успешно создана";
            } catch (\Exception $e) {
                $this->log->error('Error creating task: ' . $e->getMessage());
                $properties = ['errorMessage' => 'Задача не создана!'];
                return $this->render('error.twig', $properties);
            }
        } else {
            $this->errorMessage = implode(', ', $errorMessageTemp);
        }

        $properties = [
            'auth' => $this->auth,
            'user' => $this->user,
            'username' => $username,
            'email' => $email,
            'content' => $content,
            'form' => $this->error,
        ];
        if(!empty($this->errorMessage)) $properties['errorMessage'] = $this->errorMessage;
        if(!empty($this->successMessage)) $properties['successMessage'] = $this->successMessage;

        return $this->render('task_create.twig', $properties);
    }

    public function edit(int $taskId): string {

        if(!$this->auth) {
            header("Location: /user/login/");
        }

        $taskModel = new TaskModel($this->file);

        try {
            $taskOld = $taskModel->get($taskId);
        } catch (\Exception $e) {
            $this->log->error('Ошибка получения задачи: ' . $e->getMessage());
            $properties = ['errorMessage' => 'Задача не найдена!'];
            return $this->render('error.twig', $properties);
        }

        if (!$this->request->isPost()) {
            $properties = [
                'task' => $taskOld,
                'auth' => $this->auth,
                'user' => $this->user,
                'form' => true
            ];
            return $this->render('task_edit.twig', $properties);
        } 

        $params = $this->request->getParams();
        $this->error = false;
        $this->errorMessage = '';

        $edited = $params->getInt('edited') ? true : false;
        $content = $params->getString('content') ?? '';
        if (!$this->error and empty($content)) {
            $this->errorMessage = 'Не сформулирована задача';
            $this->error = true;
        };

        if($edited || ($content != $taskOld->getContent())) {
            $edited = true;
        }

        $done = $params->getInt('done') ? true : false;

        $taskEdited = new Task($taskId, $taskOld->getUsername(), $taskOld->getEmail(), $content, $edited, $done);

        if(!$this->error) {
            try {
                $taskId = $taskModel->change($taskEdited);
                $this->successMessage = "Задача $taskId успешно изменена";
            } catch (\Exception $e) {
                $this->log->error('Ошибка изменения задачи: ' . $e->getMessage());
                $properties = ['errorMessage' => 'Задача не изменена!'];
                return $this->render('error.twig', $properties);
            }
        }

        $properties = [
            'auth' => $this->auth,
            'user' => $this->user,
            'task' => $taskEdited,
            'form' => $this->error,
        ];
        if(!empty($this->errorMessage)) $properties['errorMessage'] = $this->errorMessage;
        if(!empty($this->successMessage)) $properties['successMessage'] = $this->successMessage;
        return $this->render('task_edit.twig', $properties);
    }
}