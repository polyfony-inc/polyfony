<?php

namespace Polyfony\Store\Session;

use Polyfony\{
	Exception, Config
};

class APCu implements \SessionHandlerInterface {

	private function getScopedId(string $id) :string {

		// retrieve the prefix
		$prefix = Config::get('apcu', 'prefix');
		// check that is has been defined
		if(!$prefix) {
			// prevent accidental omission
			Throw new Exception(
				'The APCu prefix configuration is mandatory', 
				500
			);
		}
		// format the prefix
		$prefix = mb_strtoupper(trim($prefix, ':'));
		// construct the id
		return 
			$prefix . '::'.
			'SESSION::' . 
			$id;
	}

	public function open(string $savePath, string $sessionName) :bool {
		return true;
	}

	public function close() :bool {
		return true;
	}

	public function read(string $id) :string|false {
		return \apcu_fetch($this->getScopedId($id)) ?: '';
	}

	public function write(string $id, string $data) :bool {
		return \apcu_store(
			$this->getScopedId($id), 
			$data, 
			ini_get('session.gc_maxlifetime')
		);
	}

	public function destroy(string $id) :bool {
		return \apcu_delete($this->getScopedId($id));
	}

	public function gc(int $maxlifetime) :int|false {
		return false;
	}
}