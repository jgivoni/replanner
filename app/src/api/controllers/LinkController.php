<?php

namespace Replanner\api\controllers;

class LinkController extends ApiController {

	protected $dba;
	protected $itemId;
	protected $toId;

	function __construct($itemId, $toId) {
		$this->itemId = $itemId;
		$this->toId = $toId;
		parent::__construct();
	}

	public function __invoke() {
		$fromItem = $this->getItemMapper()->loadByPrimaryKey($this->itemId);
		$toItem = $this->getItemMapper()->loadByPrimaryKey($this->toId);
		if (isset($fromItem) && isset($toItem)) {
			$result = $this->getItemMapper()->linkItems($fromItem, $toItem);
		} else {
			$result = false;
		}

		return $this->newResponse()->body(['result' => $result]);
	}

}
