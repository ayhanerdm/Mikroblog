<?php
namespace Mikroblog
{
	use \PDO;
	use \Exception;
	use \Mikroblog\Cryptography;

	class Database
	{
		private static $instance, $settings = [
			'host' => 'localhost',
			'port' => 3306,
			'dbname' => null,
			'charset' => 'utf8mb4',
			'username' => 'root',
			'password' => ''

		], $dsn;
		public static $conn, $sql = [], $prepared_sql, $executed_sql;
		public static $method = PDO::FETCH_OBJ;

		public static function connect($settings)
		{
			if(is_array($settings))
			{
				self::$settings = $settings;
				self::$dsn = 'mysql:';
				foreach(self::$settings as $key => $val){ if($key != 'username' && $key != 'password') self::$dsn .= "$key=$val;"; }
				try{ self::$conn = new PDO(self::dsn(), self::$settings['username'], self::$settings['password']); return true; }
				catch(\PDOException $e){ throw new Exception('Cannot be coonnected: '.$e->getMessage()); return false; }
			}
			elseif(is_file($settings))
			{
				$open = fopen($settings, 'r');
				$read = fread($open, filesize($settings));
				fclose($open);

				if(self::is_json($read))
				{
					self::$settings = (array) json_decode($read);
					self::$dsn = 'mysql:';
					foreach(self::$settings as $key => $val){ if($key != 'username' && $key != 'password') self::$dsn .= "$key=$val;"; }
					try{ self::$conn = new PDO(self::dsn(), self::$settings['username'], self::$settings['password']); return true; }
					catch(\PDOException $e){ throw new Exception('Cannot be coonnected: '.$e->getMessage()); return false; }
				}
				else return false;
			}
		}

		public static function prepare($sql, array $params)
		{
			self::$prepared_sql = self::$conn->prepare($sql);
			self::$executed_sql = self::$prepared_sql->execute($params);
			return new \Mikroblog\Database;
		}

		public static function fetch($sql = null, $params = null, $method = PDO::FETCH_OBJ)
		{
			if($method) self::$method = $method;
			if($sql === null)
			{
				if(self::$prepared_sql !== null)
				{
					self::$sql[] = self::$prepared_sql;
					return self::$prepared_sql->fetch(self::$method);
				}
				else throw new Exception('There wasn\'t any SQL prepared for 1 '.__METHOD__);
			}
			else
			{
				if(isset($sql))
				{
					self::$sql[] = $sql;

					self::prepare($sql, $params);
					return self::$prepared_sql->fetch(self::$method);
				}
				else throw new Exception('There wasn\'t any SQL prepared for 2 '.__METHOD__);
			}
		}

		public static function fetchAll($sql = null, $params = null, $method = PDO::FETCH_OBJ)
		{
			if($method) self::$method = $method;
			if($sql === null)
			{
				if(self::$prepared_sql !== null)
				{
					self::$sql[] = self::$prepared_sql;
					return self::$prepared_sql->fetchAll(self::$method);
				}
				else throw new Exception('There wasn\'t any SQL prepared for 1 '.__METHOD__);
			}
			else
			{
				if(isset($sql))
				{
					self::$sql[] = $sql;

					self::prepare($sql, $params);
					return self::$prepared_sql->fetchAll(self::$method);
				}
				else throw new Exception('There wasn\'t any SQL prepared for 2 '.__METHOD__);
			}
		}

		/**
		* These methods only returns properties of this class.
		*/

		private static function dsn()
		{
			return self::$dsn;
		}

		public static function conn()
		{
			return self::$conn;
		}

		public static function last_sql()
		{
			return end(self::$sql);
		}

		// Helper methods.
		public static function is_json($string)
		{
			return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
		}
	}
}