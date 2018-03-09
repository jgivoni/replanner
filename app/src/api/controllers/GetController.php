<?php

namespace Replanner\api\controllers;

class GetController extends ApiController {

	protected $dba;
	protected $itemId;

	function __construct($itemId) {
		$this->itemId = $itemId;
		parent::__construct();
	}

	public function __invoke() {
		try {
			$identity = $this->getIdentity();
			if (!isset($identity)) {
				throw new ControllerException('Missing identity. Please provide the header: X-Jot-Identity = <your user ID>');
			}
			$identityItem = $this->getItemMapper()->loadByPrimaryKey($identity);
			if (!in_array($this->itemId, (array) $identityItem->linkFrom)) {
				throw new ControllerException('Item not found');
			}
			$item = $this->getItemMapper()->loadByPrimaryKey($this->itemId);
			if (!isset($item)) {
				throw new ControllerException('Item not found');
			}

			$result = [
				'status' => 'success',
				'item' => [
					'itemId' => $item->itemId,
					'content' => $item->content,
					'belongsTo' => (array) $item->linkTo,
					'contains' => (array) $item->linkFrom,
				]];
		} catch (ControllerException $e) {
			$result = [
				'status' => 'error',
				'message' => $e->getMessage(),
			];
		}

		return $this->newResponse()->body(['result' => $result]);
	}

}
