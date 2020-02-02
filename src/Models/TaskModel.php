<?php
declare(strict_types=1);

namespace TaskList\Models;

use TaskList\Domain\Task;
use TaskList\Exceptions\NotFoundException;
use Illuminate\Support\Collection;

class TaskModel extends AbstractModel {

    private $total;
    private $collection;
    private $tasks;

    public function __construct(string $file) {
        parent::__construct($file);
        $tasksJson = file_get_contents($this->file);
        $this->tasks = json_decode($tasksJson);
        $this->collection = collect($this->tasks);
    }

    public function get(int $taskId): Task {
        $filtered = $this->collection->where('id', $taskId);
        $tasks = $filtered->all();
        foreach($tasks as $value) {
            $task = $value;
        }
        return new Task($task->id, $task->username, $task->email, $task->content, $task->edited, $task->done);
    }

    public function getAll(int $page, int $pageLength, string $sortBy = NULL, string $order = NULL): array {

        if($sortBy == 'status') {
            if($order == 'desc') {
                $chunk = $this->collection->sortBy(function ($task, $key) {
                    $status = 0;
                    if($task->edited) $status = 1;
                    if($task->done and $task->edited) $status = 2;
                    if($task->done) $status = 3;
                    return $status;
                });
            } else {
                $chunk = $this->collection->sortByDesc(function ($task, $key) {
                    $status = 0;
                    if($task->edited) $status = 1;
                    if($task->done and $task->edited) $status = 2;
                    if($task->done) $status = 3;
                    return $status;
                });                
            }
        } elseif($sortBy) {
            if($order == 'desc') {
                $chunk = $this->collection->sortByDesc($sortBy);
            } else {
                $chunk = $this->collection->sortBy($sortBy);
            }
        } else {
            $chunk = $this->collection;
        }
        $chunk = $chunk->forPage($page, $pageLength);
        return  $chunk->all();
    }

    public function getTotal(): int {
        return  $this->collection->count();
    }

    public function getMaxId(): int {
        return  $this->collection->max('id') + 1;
    }

    public function create(Task $task): int {
        $this->tasks[] = [
            'id' => $task->getId(),
            'username' => $task->getUsername(),
            'email' => $task->getEmail(),
            'content' => $task->getContent(),
            'edited' => $task->getEdited(),
            'done' => $task->getDone()
        ];
        $tasksJson = json_encode($this->tasks);
        file_put_contents($this->file, $tasksJson);
        $tasksJson = file_get_contents($this->file);
        $this->tasks = json_decode($tasksJson);        
        $this->collection = collect($this->tasks); 
        return  $task->getId();  
    }

    public function change(Task $task): int {
        foreach($this->tasks as &$task_temp) {
            if($task_temp->id == $task->getId()) { 
                $task_temp = [
                    'id' => $task->getId(),
                    'username' => $task->getUsername(),
                    'email' => $task->getEmail(),
                    'content' => $task->getContent(),
                    'edited' => $task->getEdited(),
                    'done' => $task->getDone()
                ];
            }
        }

        $tasksJson = json_encode($this->tasks);
        file_put_contents($this->file, $tasksJson);
        $tasksJson = file_get_contents($this->file);
        $this->tasks = json_decode($tasksJson);        
        $this->collection = collect($this->tasks); 
        return  $task->getId();  
    }
}