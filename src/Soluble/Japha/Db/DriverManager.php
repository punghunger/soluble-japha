<?php

namespace Soluble\Japha\Db;

use Soluble\Japha\Bridge;
use Soluble\Japha\Bridge\Exception;
use Soluble\Japha\Interfaces;

class DriverManager
{
    /**
     * @Java(java.sql.DriverManager)
     */
    protected $driverManager;

    /**
     * @var Bridge\Adapter
     */
    protected $ba;

    /**
     * @param Bridge\Adapter $ba
     */
    public function __construct(Bridge\Adapter $ba)
    {
        $this->ba = $ba;
    }

    /**
     * Create an sql connection to database.
     *
     *
     * @throws Exception\JavaException
     * @throws Exception\ClassNotFoundException
     * @throws Exception\InvalidArgumentException
     *
     * @param string $dsn
     * @param string $driverClass
     *
     * @return Interfaces\JavaObject Java('java.sql.Connection')
     */
    public function createConnection($dsn, $driverClass = 'com.mysql.jdbc.Driver')
    {
        if (!is_string($dsn) || trim($dsn) == '') {
            $message = 'DSN param must be a valid (on-empty) string';
            throw new Exception\InvalidArgumentException(__METHOD__ . ' ' . $message);
        }

        $class = $this->ba->javaClass('java.lang.Class');
        try {
            $class->forName($driverClass);
        } catch (Exception\JavaException $e) {
            throw $e;
        }

        try {
            $conn = $this->getDriverManager()->getConnection($dsn);
        } catch (Exception\JavaExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            $message = 'Unexpected exception thrown with message ' . $e->getMessage();
            throw new Exception\UnexpectedException(__METHOD__ . ' ' . $message);
        }

        return $conn;
    }

    /**
     * Return underlying java driver manager.
     *
     * @return Interfaces\JavaObject Java('java.sql.DriverManager')
     */
    public function getDriverManager()
    {
        if ($this->driverManager === null) {
            $this->driverManager = $this->ba->javaClass('java.sql.DriverManager');
        }

        return $this->driverManager;
    }

    /**
     * Return a JDBC DSN formatted string.
     *
     * @param string $db         database name
     * @param string $host       server ip or name
     * @param string $user       username to connect
     * @param string $password   password to connect
     * @param string $driverType diverType (mysql/oracle/postgres...)
     *
     * @return string
     */
    public static function getJdbcDsn($db, $host, $user, $password, $driverType = 'mysql')
    {
        return "jdbc:$driverType://$host/$db?user=$user&password=$password";
    }
}
