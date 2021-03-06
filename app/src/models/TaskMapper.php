<?php

namespace Jot;

use Ophp\SqlCriteriaBuilder as CB;

/**
 * Model Mapper for tasks
 */
class TaskMapper extends \Ophp\DataMapper
{
	const FIELD_TASKID = 'task_id';
	const FIELD_POSITION = 'position';
	const FIELD_PARENT = 'parent';
	
	/**
	 * Name of the corresponding db table
	 * @var string
	 */
	protected $tableName = 'task';
	
	/**
	 * @var array Properties of the model. Specify 'column' if column name in db differs
	 */
	protected $fields = array(
		'taskId' => array(
			'column' => self::FIELD_TASKID,
			'type' => 'int',
		),
		'title' => array(
			'type' => 'string'
		),
		'description' => array(
			'type' => 'string'
		),
		'createdTimestamp' => array(
			'column' => 'created_timestamp',
			'type' => 'timestamp',
		),
		self::FIELD_POSITION => array(
			'type' => 'int',
		),
		'priority' => array(
			'type' => 'string'
		),
		self::FIELD_PARENT => array(
			'type' => 'int',
		),
		'userId' => array(
			'column' => 'user_id',
			'type' => 'int',
		)
	);
	
	/**
	 * Name of the property which acts a primary key
	 * @var string
	 */
	protected $primaryKey = 'taskId';

	/**
	 * 
	 * @return TaskModel
	 */
	public function newModel()
	{
		return new TaskModel;
	}

	/**
	 * 
	 * @param mixed $primaryKey
	 * @return \TaskModel
	 */
	protected function getSharedModel($primaryKey)
	{
		return parent::getSharedModel($primaryKey);
	}

	/**
	 * @param int $taskId
	 * @return TaskModel
	 */
	public function loadByPrimaryKey($taskId)
	{
		return parent::loadByPrimaryKey($taskId);
	}

	/**
	 * Stores the task model data in the db
	 * @param \Jot\TaskModel $task
	 */
	public function saveTask(TaskModel $task)
	{
		// If no position is given, make it the first (new tasks go at the top)
		if ($task->getPosition() === null) {
			$task->setPosition(1);
		}
		
		/** Find out if any other tasks are occupying the position of this task
		 * and move them and all after them down by one
		 */
		$query = $this->newSelectQuery()->comment('Push down?')
				->where(CB::is(self::FIELD_POSITION, $task->getPosition()));
		if (!$task->isNew()) {
			$query->where(CB::isnot(self::FIELD_TASKID, $task->getTaskId()));
		}
		if ($this->count($query) > 0) {
			// Move all tasks from this position and beyond to make room for this task
			$criteria = CB::notless(self::FIELD_POSITION, $task->getPosition());
			if (!$task->isNew()) {
				$criteria = $criteria->and_(CB::isnot(self::FIELD_TASKID, $task->getTaskId()));
			}
			$update = $this->newUpdateQuery()->comment('Push down!')
					->set(CB::expr('%1 = %1 + 1', CB::field(self::FIELD_POSITION)))
					->where($criteria);
			$this->dba->query($update);
		}
		
		$sql = $task->isNew() ? 
			$this->newInsertQuery() : 
			$this->newUpdateQuery()->where(CB::is(self::FIELD_TASKID, $task->getTaskId()));
		$sql->comment(__METHOD__);
		foreach ($this->fields as $modelField => $config) {
			$value = $task->$modelField;
			$name = isset($config['column']) ? $config['column'] : $modelField;
			if (isset($value)) {
				$sql->set(CB::expr('%1 = %2', [CB::field($name), $value]));
			}
		}
		
		$this->dba->query($sql);
		
		if ($task->isNew()) {
			$taskId = $this->dba->getInsertId();
			$task->setTaskId($taskId);
			$this->setSharedModel($task);
		}
	}

	/**
	 * Deletes a task
	 * 
	 * @param \Jot\TaskModel $taskModel
	 */
	public function delete(TaskModel $taskModel)
	{
		return $this->deleteByModel($taskModel);
	}

	public function loadAllOrdered() {
		$query = $this->newSelectQuery()->comment(__METHOD__)
				->orderBy(CB::field(self::FIELD_POSITION));

		return $this->loadAll($query);
	}
	
	public function loadLast() {
		$query = $this->newSelectQuery()->comment(__METHOD__)
				->orderBy(CB::expr('%1 DESC', CB::field(self::FIELD_POSITION)));
		return $this->loadOne($query);
	}
	
	public function loadParent(TaskModel $task) {
		$query = $this->newSelectQuery()->comment(__METHOD__)
				->where(CB::field(self::FIELD_TASKID, $this->tableName)->is($task->getParent()));
		return $this->loadOne($query);
	}
	
	public function loadSubtasks(TaskModel $task) {
		$query = $this->newSelectQuery()->comment(__METHOD__)
				->where(CB::field(self::FIELD_PARENT)->is($task->getTaskId()));
		return $this->loadAll($query);
	}
}