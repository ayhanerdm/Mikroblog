<?php
// This class isn't done yet.
namespace Mikroblog
{
	use \Mikroblog\Exception;
	class Cryptography
	{
		public static $method = 'aes-256-ctr';
		private static $password, $random_bytes, $is_raw = false;
		private static $action_method = 'set', $_instance;

		public function __construct(){  }

		public static function _instance()
		{
			if(!isset(self::$_instance)) self::$_instance = new self;
			return self::$_instance;
		}

		/**
		 * Sets chiper method and returns it's self instance.
		 */
		public static function method($method = 'aes-256-ctr')
		{
			/**
			 * This method sets the chiper method and it's default is aes-256-ctr
			 * Call this method or not self::$method property will be aes-256-ctr
			 */
			if(isset($method)) self::$method = $method;
			return new \Mikroblog\Cryptography;
		}

		public static function encrypt($data, $password = null)
		{
			if(isset($data))
			{
				// If there's password argument with this method then set it to $method property.
				if($password !== null) self::password($password);
				else self::password(self::generate_password());

				// Now lets fill the openssl function with all these information.
				self::random_bytes(self::$method);
				return openssl_encrypt($data, self::$method, self::$password, self::$is_raw, self::random_bytes());
			}
			else throw new Exception('$data argument required for '.__METHOD__);
		}

		// Random bytes methods.
		// Returns the current random bytes.
		private static function get_random_bytes()
		{
			return self::$random_bytes;
		}

		// Sets given random bytes.
		private static function set_random_bytes($random_bytes)
		{
			if(isset($random_bytes)){ self::$_instance->__set('random_bytes', $random_bytes); return true; }
			else{ throw new \Mikroblog\Exception('To set random bytes, first argument of '.__METHOD__.' must be filled.'); return false; }
		}

		// Generates, sets and return new generated random bytes.
		private static function generate_random_bytes($size = null)
		{
			if(isset($size))
			{
				if(in_array($size, openssl_get_cipher_methods())){ self::set_random_bytes(bin2hex(random_bytes(openssl_cipher_iv_length($size)))); return self::get_random_bytes(); }
				else{ self::set_random_bytes(bin2hex(random_bytes($size))); return self::get_random_bytes(); }
			}
			elseif(isset(self::$method)){ self::set_random_bytes(bin2hex(random_bytes(openssl_cipher_iv_length(self::$method)))); return self::get_random_bytes(); }
			else{ throw new \Mikroblog\Exception('To create random bytes, a lenght or a chiper method must be specified.'); return false; }
		}

		public static function random_bytes($random_bytes = null)
		{
			switch(self::$action_method)
			{
				case 'get': return self::get_random_bytes(); break;
				case 'set': return self::set_random_bytes($random_bytes); break;
				case 'generate': return self::generate_random_bytes($random_bytes); break;
				default: return self::set_random_bytes($random_bytes);
			}
		}

		// Password methods.
		// Returns the current password.
		private static function get_password()
		{
			return self::$password;
		}

		// Sets a new password.
		private static function set_password($password)
		{
			if(isset($password)){ self::$_instance->__set('password', $password); return true; }
			else{ throw new \Mikroblog\Exception('An argument is required for '.__METHOD__); return false; }
		}

		// Generates a new password.
		private static function generate_password($length = 22)
		{
			self::$_instance->__set('password', bin2hex(random_bytes($length)));
			return self::get_password();
		}

		// Main password method.
		public static function password($password = 22)
		{
			switch(self::$action_method)
			{
				case 'set': return self::set_password($password); break;
				case 'get': return self::get_password(); break;
				case 'generate': return self::generate_password($password); break;
				default: return self::set_password($password);
			}
		}
		// End of the password methods.

		/**
		 * \Mikroblog\Cryptography
		 * To set a new password: (new thisClass)::set()::password('New Password');
		 * To get current password (new thisClass)::get()::password('New Password');
		 * To generate a new password: (new thisClass)::generate()::password([password lenght]);
		 */
		public function __get($property){ return self::$$property; }
		public function __set($property, $value){ self::$$property = $value; }

		public static function get()
		{
			self::_instance()->__set('action_method', 'get');
			return self::$_instance;
		}

		public static function set()
		{
			self::_instance()->__set('action_method', 'set');
			return self::$_instance;
		}

		public static function generate()
		{
			self::_instance()->__set('action_method', 'generate');
			return self::$_instance;
		}
	}
}