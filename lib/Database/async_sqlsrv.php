<?php
declare(strict_types=1);

namespace ezsql\Database;

use Exception;
use ezsql\ConfigInterface;
use ezsql\Database\ez_sqlsrv;
use ezsql\Database\async_interface;

class async_sqlsrv extends ez_sqlsrv implements async_interface
{    
    protected $query = null;
    protected $use_prepare = false;

    /**
     *  Constructor - allow the user to perform a quick connect at the
     *  same time as initializing the ez_sqlsrv class
     */
    public function __construct(ConfigInterface $settings = null)
    {        
        if (empty($settings)) {
            throw new Exception(\MISSING_CONFIGURATION);
        }
        
        parent::__construct($settings);
        $this->database = $settings;

        if (!isset($GLOBALS['async'.\SQLSRV]))
            $GLOBALS['async'.\SQLSRV] = $this;
        \setInstance($this);
    }

    public function query(string $query, bool $use_prepare = false) 
    {
        $this->query = $query;
        $this->use_prepare = $use_prepare;
        return $this->query_wait();
    }

    public function query_wait() 
    {
        $preparedValues = $this->prepareValues();
        $prepareActive = $this->isPrepareOn();
        $user = $this->database->getUser();
        $password = $this->database->getPassword();
        $name = $this->database->getName();
        $host = $this->database->getHost();
        $query = $this->query;
        $use_prepare = $this->use_prepare;

        // return to caller, let other tasks start, otherwise block after
        $db = yield \await_process(
            function () use($user, $password, $name, $host, $query, $use_prepare, $prepareActive, $preparedValues) {
                $settings = new Config('sqlsrv', [$user, $password, $name, $host]);
                $db = new ez_sqlsrv($settings);
                $db->preparedValues = $preparedValues;
                $db->prepareActive = $prepareActive;
                $db->query($query, $use_prepare);

                return $db;
            }
        );

        $this->shortcutUsed = $db->shortcutUsed;
        $this->last_query = $db->last_query;
        $this->return_val = $db->return_val;
        $this->debug_all = $db->debug_all;
        $this->col_info = $db->col_info;
        $this->dbh = $db->dbh;
        $this->trace = $db->trace;
        $this->result = $db->result;
        $this->insert_id = $db->insert_id;
        $this->is_insert = $db->is_insert;
        $this->num_rows = $db->num_rows;
        $this->last_result = $db->last_result;
        $this->isTransactional = $db->isTransactional;
        $this->_connected = $db->_connected;
        $this->_affectedRows = $db->_affectedRows;
        $this->$prepareActive = $db->$prepareActive;

        $this->debug_all = $db->getDebug_All();
        $this->trace = $db->getTrace();
        $this->debug_called = $db->getDebug_Called();
        $this->varDump_called = $db->getVarDump_called();
        $this->show_errors = $db->getShow_Errors();
        $this->num_queries = $db->getNum_Queries();
        $this->conn_queries = $db->getConn_Queries();
        $this->captured_errors = $db->getCaptured_Errors();
        $this->cache_dir = $db->getCache_Dir();
        $this->use_disk_cache = $db->getUse_Disk_Cache();
        $this->cache_timeout = $db->getCache_Timeout();
        $this->cache_queries = $db->getCache_Queries();
        $this->cache_inserts = $db->getCache_Inserts();
        $this->num_rows = $db->getNum_Rows();
        $this->db_connect_time = $db->getDb_Connect_Time();
        $this->sql_log_file = $db->getSql_Log_File();
        $this->profile_times = $db->getProfile_Times();
        $this->insert_id = $db->getInsert_Id();
        $this->last_query = $db->getLast_Query();
        $this->last_error = $db->getLast_Error();
        $this->col_info = $db->getCol_Info();
        $this->timers = $db->getTimers();
        $this->total_query_time = $db->getTotal_Query_Time();
        $this->trace_log = $db->getTrace_Log();
        $this->use_trace_log = $db->getUse_Trace_Log();
        $this->do_profile = $db->getDo_Profile();
        $this->last_result = $db->getLast_Result();
        $this->from_disk_cache = $db->getFrom_Disk_Cache();
        $this->debug_echo_is_on = $db->getDebug_Echo_Is_On();
        $this->func_call = $db->getFunc_Call();
        $this->all_func_calls = $db->getAll_Func_Calls();

        return $this->return_val;
    }
}
