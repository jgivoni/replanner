<?php

namespace Jot\api\controllers;

/**
 * @method \Jot\api\ApiServer getServer
 */
class ApiController extends \Ophp\JsonController {

	protected $dba;
	protected $itemMapper;

	/**
	 * 
	 * @return \Ophp\dba\DynamoDbDatabaseAdapter
	 */
	protected function getDynamoDbDatabaseAdapter() {
		if (!isset($this->dba)) {
			$this->dba = $this->getServer()->newDynamoDbDatabaseAdapter('jot');
		}
		return $this->dba;
	}

	/**
	 * 
	 * @return \Jot\models\ItemMapper
	 */
	protected function getItemMapper() {
		if (!isset($this->itemMapper)) {
			$this->itemMapper = new \Jot\models\ItemMapper;
			$this->itemMapper->setDba($this->getDynamoDbDatabaseAdapter());
		}
		return $this->itemMapper;
	}

	public function __invoke() {
		return $this->newResponse()->body(['result' => 'ok']);
	}

	/**
	 * Returns the caller's identity Item ID
	 * 
	 * @return string
	 */
	public function getIdentity() {
		$identity = $this->getRequest()->getHeader('X-Jot-Identity');
		if (!isset($identity)) {
			$identity = $this->getRequest()->getCookie('Jot-Identity');
		}
		return $identity;
	}

}
