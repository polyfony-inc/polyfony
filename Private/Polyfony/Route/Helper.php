<?php


namespace Polyfony\Route;
use Polyfony\Config as Conf;
use Polyfony\Request as Req;

class Helper {

	public static function prefixIt(string $url='/', bool $force_tls = false) :string {
		$desired_protocol = self::getProtocol($force_tls);
		return 
			$desired_protocol . 
			'://' . 
			Conf::get('router', 'domain') . 
			self::getPort($desired_protocol) . 
			$url;
	}

	public static function isThisAParameterName($unknown_string) :bool {
		return 
			// if we've got an old school parameter
			substr($unknown_string, 0, 1) == ':' || 
			(
				// or if we've got a new syntax parameter
				substr($unknown_string, 0, 1) == '{' && 
				substr($unknown_string, -1, 1) == '}'
			);
	}

	private static function getProtocol(bool $force_tls=false) :string {
		return 
			// if we are in prod and tls is to be enforced
			((Conf::isProd() && $force_tls) || 
			// or if we already are rolling https
			Req::getProtocol() == 'https') ? 
				'https' : 
				'http';
	}

	private static function getPort(string $desired_protocol) {
		// if the port is not standard (and its not something SSL related)
		return Req::getPort() != 80 && $desired_protocol == 'http' ? 
			':'.Req::getPort() : 
			'';
	}

	

}

?>
