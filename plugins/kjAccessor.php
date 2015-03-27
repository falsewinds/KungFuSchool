<?php
namespace kjPHP
{
    class Accessor
    {
        private $pdo = null;
        public $RECORD_SESSION = false;

        function __construct($server,$database,$username,$password)
        {
            $options = array( \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
            $this->pdo = new \PDO('mysql:host='.$server.';dbname='.$database,$username,$password,$options);
            if( !$this->pdo ) { $this->writeLogFile('Create PDO fail.'); }
        }

        function writeLogFile($message)
        {
            $prefix = '';
            if( $this->RECORD_SESSION )
            {
                $sid = session_id();
                if( $sid == '' )
                {
                    session_start();
                    $sid = session_id();
                }
                $prefix = 'SESSION(' . $sid . ') ';
            }
            $ipaddr = isset($_SERVER['REMOTE_ADDR']) ? str_replace( '.', '-', $_SERVER['REMOTE_ADDR'] ) : null;
            if( empty($ipaddr) ) { $ipaddr = 'UNKNOWN'; }
            $fp = fopen( date('Y-m-d') . '_' . $ipaddr . '.log', 'a' );
            fwrite($fp,$prefix.$message.PHP_EOL);
            fclose($fp);
        }

        public function _query($sql,$args = null)
        {
            $result = null;
            if( $args != null )
            {
                $stmt = $this->pdo->prepare($sql);
                if( $stmt->execute($args) ) { $result = $stmt; }
            } else { $result = $this->pdo->query($sql); }
            if( $result === null ) { $this->writeLogFile('Invalid Argument: kjPHP\Accessor::_query.'); }
            if( $result === false )
            {
                $errinfo = $this->pdo->errorInfo();
                $this->writeLogFile('PDO Exception: '.$errinfo[2]);
                return false;
            }
            return $result;
        }

        public function _execute($sql,$args = null)
        {
            //$result = null;
            if( $args == null ) { $args = array(); }
            $stmt = $this->pdo->prepare($sql);
            if( $stmt->execute($args) )
            {
                try
                {
                    $lastid = $this->pdo->lastInsertId();
                    return $lastid;
                } catch( PDOException $e ) { $this->writeLogFile('Fail to get last ID: '.$e->getMessage()); }
            }
            else
            {
                $errinfo = $stmt->errorInfo();
                $this->writeLogFile('PDO Exception: '.$errinfo[2]);
            }
        }
    }
}