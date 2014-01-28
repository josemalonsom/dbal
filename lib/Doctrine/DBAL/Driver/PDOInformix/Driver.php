<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL\Driver\PDOInformix;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\ExceptionConverterDriver;

/**
 * Driver for the PDO Informix extension.
 *
 * @author Jose M. Alonso M.  <josemalonsom@yahoo.es>
 * @link   www.doctrine-project.org
 */
class Driver implements \Doctrine\DBAL\Driver, ExceptionConverterDriver
{

    /**
     * {@inheritdoc}
     *
     * The params array must have the following keys/values:
     *
     * - 'dbname': The name of the database to connect to.
     * - 'host': The host name of the database to connect to.
     * - 'port': The port of the database to connecto to (optional, some
     *           protocols do not use port, e.g. ipcshm).
     * - 'protocol': The protocol to use in the connection.
     * - 'server': The server name of the database to connect to.
     *
     * Example:
     *
     * <code>
     *
     *    use Doctrine\DBAL\Driver\PDOInformix\Driver;
     *
     *    $connectionParams = array(
     *        'dbname'       => 'test',
     *        'host'         => 'test-1',
     *        'port'         => '50000',
     *        'protocol'     => 'onsoctcp',
     *        'server'       => 'test1tcp',
     *    );
     *
     *    $driverOptions = array();
     *
     *    $username = 'user';
     *    $password = 'passwd';
     *
     *    $driver = new Driver();
     *
     *    $conn = $driver->connect($connectionParams, $username,
     *        $password, $driverOptions);
     *
     * </code>
     *
     * Note: The attribute PDO::ATTR_STRINGIFY_FETCHES is set to true by
     * default in the database handle so that the LOB types are fetched as
     * the real values instead of a resource. The value can be overwritten
     * with other value in the driver options array.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {

        $dsn = $this->constructPdoDsn($params);

        $driverOptions += array(\PDO::ATTR_STRINGIFY_FETCHES => true);

        try {

            return new \Doctrine\DBAL\Driver\PDOInformix\Connection(
                $dsn,
                $username,
                $password,
                $driverOptions
            );

        } catch (\Exception $e) {
            throw DBALException::driverException($this, $e);
        }

    }

    /**
     * Constructs the Informix PDO DSN.
     *
     * @param array $params
     * @return string The DSN.
     * @throws \Doctrine\DBAL\DBALException
     * @see \Doctrine\DBAL\Driver::connect
     */
    private function constructPdoDsn(array $params)
    {

        if ( empty($params['dbname']) ) {
            throw DBALException::driverException($this,
                new \Exception("Missing 'dbname' in configuration for informix driver")
            );
        }

        if ( empty($params['host']) ) {
            throw DBALException::driverException($this,
                new \Exception("Missing 'host' in configuration for informix driver")
            );
        }

        if ( empty($params['protocol']) ) {
            throw DBALException::driverException($this,
                new \Exception("Missing 'protocol' in configuration for informix driver")
            );
        }

        if ( empty($params['server']) ) {
            throw DBALException::driverException($this,
                new \Exception("Missing 'server' in configuration for informix driver")
            );
        }

        $dsn = 'informix:'
            . 'host=' . $params['host'] . ';'
            . 'server=' . $params['server'] . ';'
            . 'database=' . $params['dbname'] . ';'
            . 'protocol=' . $params['protocol'] . ';';

        if ( ! empty($params['port']) ) {
            $dsn .= 'service=' . $params['port'] . ';';
        }

        return $dsn;

    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new \Doctrine\DBAL\Platforms\InformixPlatform;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn)
    {
        return new \Doctrine\DBAL\Schema\InformixSchemaManager($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_informix';
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(\Doctrine\DBAL\Connection $conn)
    {
        $params = $conn->getParams();

        return $params['dbname'];
    }

    /**
     * {@inheritdoc}
     */
    public function convertExceptionCode(\Exception $exception)
    {

        if ( $exception instanceof \PDOException && $exception->errorInfo[1] !== null ) {
            $errorCode = $exception->errorInfo[1];
        }
        else {
            $errorCode = $exception->getCode();
        }

        switch ( $errorCode ) {
            case '-239':
            case '-268':
                return DBALException::ERROR_DUPLICATE_KEY;
            case '-206':
                return DBALException::ERROR_UNKNOWN_TABLE;
            case '-310':
                return DBALException::ERROR_TABLE_ALREADY_EXISTS;
            case '-692':
                return DBALException::ERROR_FOREIGN_KEY_CONSTRAINT;
            case '-391':
                return DBALException::ERROR_NOT_NULL;
            case '-217':
                return DBALException::ERROR_BAD_FIELD_NAME;
            case '-324':
                return DBALException::ERROR_NON_UNIQUE_FIELD_NAME;
            case '-201':
                return DBALException::ERROR_SYNTAX;
            case '-908':
            case '-930':
            case '-951':
                return DBALException::ERROR_ACCESS_DENIED;
        }

        // In some cases the exception doesn't have the driver-specific error code

        if ( self::isErrorAccessDeniedMessage($exception->getMessage()) ) {
            return DBALException::ERROR_ACCESS_DENIED;
        }

        return 0;
    }

    /**
     * Checks if a message means an "access denied error".
     *
     * @param string
     * @return boolean
     */
    protected static function isErrorAccessDeniedMessage($message)
    {
        if ( strpos($message, 'Incorrect password or user') !== false ||
            strpos($message, 'Cannot connect to database server') !== false ||
            preg_match('/Attempt to connect to database server (.*) failed/', $message) ) {
            return true;
        }

        return false;
    }
}
