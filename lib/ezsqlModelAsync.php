<?php

namespace ezsql;

use ezsql\ezsqlModel;
use ezsql\ezQueryAsync;

/**
 * Core class containing common functions to manipulate query result
 * sets once returned
 */	
class ezsqlModelAsync extends ezsqlModel
{	
	public function get_var(string $query = null, int $x = 0, int $y = 0, bool $use_prepare = false)
	{		
		// Log how the function was called
		$this->log_query("\$db->get_var(\"$query\",$x,$y)");
		
		// If there is a query then perform it if not then use cached results..
		if ( $query) {
			$this->query($query, $use_prepare);
		}
		
		// Extract public out of cached results based x,y values
		if ( isset($this->last_result[$y]) ) {
			$values = \array_values(\get_object_vars($this->last_result[$y]));
		}
		
		// If there is a value return it else return null
		return (isset($values[$x]) && $values[$x] !== null) ? $values[$x] :null;
	}
	
	public function get_row(string $query = null, $output = OBJECT, int $y = 0, bool $use_prepare = false)
	{
		// Log how the function was called
		$this->log_query("\$db->get_row(\"$query\",$output,$y)");
		
		// If there is a query then perform it if not then use cached results..
		if ( $query ) {
			$this->query($query, $use_prepare);
		}
		
		if ( $output == OBJECT ) {
			// If the output is an object then return object using the row offset..
			return isset($this->last_result[$y]) ? $this->last_result[$y] : null;
		} elseif ( $output == ARRAY_A ) {
			// If the output is an associative array then return row as such..
			return isset($this->last_result[$y]) ? \get_object_vars($this->last_result[$y]) : null;
		} elseif ( $output == ARRAY_N )	{
			// If the output is an numerical array then return row as such..
			return isset($this->last_result[$y]) ? \array_values(\get_object_vars($this->last_result[$y])) : null;
		} else {
			// If invalid output type was specified..
			$this->show_errors ? \trigger_error(" \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N", \E_USER_WARNING) : null;
		}
	}
	
	public function get_col(string $query = null, int $x = 0, bool $use_prepare = false)
	{
		$new_array = array();
		
		// If there is a query then perform it if not then use cached results..
		if ( $query ) {
			$this->query($query, $use_prepare);
		}
		
		// Extract the column values
		if (\is_array($this->last_result)) {
			$j = \count($this->last_result);
			for ( $i=0; $i < $j; $i++ ) {
				$new_array[$i] = $this->get_var(null, $x, $i, $use_prepare);
			}
		}

		return $new_array;
	}
	
	public function get_results(string $query = null, $output = \OBJECT, 	bool $use_prepare = false) 
	{
		// Log how the function was called
		$this->log_query("\$db->get_results(\"$query\", $output, $use_prepare)");
		
		// If there is a query then perform it if not then use cached results..
		if ( $query ) {
			$this->query($query, $use_prepare);
		}
		
		if ( $output == OBJECT ) {
			return $this->last_result;
		} elseif ( $output == \_JSON ) { 
			return \json_encode($this->last_result); // return as json output
		} elseif ( $output == ARRAY_A || $output == ARRAY_N ) {
			$new_array = [];
			if ( $this->last_result ) {
				$i = 0;
				foreach( $this->last_result as $row ) {
					$new_array[$i] = \get_object_vars($row);
					if ( $output == ARRAY_N ) {
						$new_array[$i] = \array_values($new_array[$i]);
					}
					$i++;
				}
			}
			return $new_array;
		}
	}
	
	public function get_col_info(string $info_type = "name", int $col_offset = -1)
	{
		if ( $this->col_info ) {
			$new_array = [];
			if ( $col_offset == -1 ) {
				$i=0;
				foreach($this->col_info as $col ) {
					$new_array[$i] = $col->{$info_type};
					$i++;
				}
				
				return $new_array;
			}

			return $this->col_info[$col_offset]->{$info_type};
		}
	}

	public function create_cache(string $path = null) 
	{
		$cache_dir = empty($path) ? $this->cache_dir : $path;
		if ( ! \is_dir($cache_dir) ) {
			$this->cache_dir = $cache_dir;
			@\mkdir($cache_dir, ('\\' == \DIRECTORY_SEPARATOR ? null : 0755), true);
		} 
	}

	public function store_cache(string $query, bool $is_insert = false)
	{
		// The would be cache file for this query
		$cache_file = $this->cache_dir.\_DS.\md5($query);
		
		// disk caching of queries
		if ( $this->use_disk_cache 
			&& ( $this->cache_queries && ! $is_insert ) || ( $this->cache_inserts && $is_insert )
		) {
			$this->create_cache();
			if ( ! \is_dir($this->cache_dir) ) {
				return $this->register_error("Could not open cache dir: $this->cache_dir");
			} else {
				// Cache all result values
				$result_cache = array(
					'col_info' => $this->col_info,
					'last_result' => $this->last_result,
					'num_rows' => $this->num_rows,
					'return_value' => $this->num_rows,
				);
				
				\file_put_contents($cache_file, \serialize($result_cache));
				if( \file_exists($cache_file . ".updating") )
					\unlink($cache_file . ".updating");
			}
		}
	}
	
	public function get_cache(string $query)
	{
		// The would be cache file for this query
		$cache_file = $this->cache_dir.\_DS.\md5($query);
		
		// Try to get previously cached version
		if ( $this->use_disk_cache && \file_exists($cache_file) ) {
			// Only use this cache file if less than 'cache_timeout' (hours)
			if ( (\time() - \filemtime($cache_file)) > ($this->cache_timeout*3600) 
				&& !(\file_exists($cache_file . ".updating") 
				&& (\time() - \filemtime($cache_file . ".updating") < 60)) 
			) {
				\touch($cache_file . ".updating"); // Show that we in the process of updating the cache
			} else {
				$result_cache = \unserialize(\file_get_contents($cache_file));
				
				$this->col_info = $result_cache['col_info'];
				$this->last_result = $result_cache['last_result'];
				$this->num_rows = $result_cache['num_rows'];
				
				$this->from_disk_cache = true;
				
				// If debug ALL queries
				$this->trace || $this->debug_all ? $this->debug() : null ;
				
				return $result_cache['return_value'];
			}
		}
	}
	
    public function secureSetup(
        string $key = 'certificate.key', 
        string $cert = 'certificate.crt', 
        string $ca = 'cacert.pem', 
        string $path = '.'.\_DS) 
    {
		if (! \file_exists($path.$cert) || ! \file_exists($path.$key)) {
			$vendor = \getVendor();
			if (($vendor != \SQLITE) || ($vendor != \MSSQL))
            	$path = ezQueryAsync::createCertificate();
		} elseif ($path == '.'.\_DS) {
            $ssl_path = \getcwd();
            $path = \preg_replace('/\\\/', \_DS, $ssl_path). \_DS;
        }

        $this->isSecure = true;
        $this->sslKey = $key;
        $this->sslCert = $cert;
		$this->sslCa = $ca;
		$this->sslPath = $path;
	}
} // ezsqlModel