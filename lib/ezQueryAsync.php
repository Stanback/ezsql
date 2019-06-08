<?php

namespace ezsql;

use ezsql\ezQuery;
use ezsql\ezSchema;
use ezsql\ezQueryInterface;

class ezQueryAsync extends ezQuery
{    
    /**
     * Creates self signed certificate
     * 
     * @param string $privatekeyFile
     * @param string $certificateFile
     * @param string $signingFile
     * // param string $caCertificate
     * @param string $ssl_path
     * @param array $details - certificate details 
     * 
     * Example: 
     *  array $details = [
     *      "countryName" =>  '',
     *      "stateOrProvinceName" => '',
     *      "localityName" => '',
     *      "organizationName" => '',
     *      "organizationalUnitName" => '',
     *      "commonName" => '',
     *      "emailAddress" => ''
     *  ];
     * 
     * @return string certificate path
     */
    public static function createCertificate(
        string $privatekeyFile = 'certificate.key', 
        string $certificateFile = 'certificate.crt', 
        string $signingFile = 'certificate.csr', 
        // string $caCertificate = null, 
        string $ssl_path = null, 
        array $details = ["commonName" => "localhost"]
    ) 
    {
        if (empty($ssl_path)) {
            $ssl_path = \getcwd();
            $ssl_path = \preg_replace('/\\\/', \_DS, $ssl_path). \_DS;
        } else
            $ssl_path = $ssl_path. \_DS;
        
        $opensslConfig = array("config" => $ssl_path.'openssl.cnf');
        
        // Generate a new private (and public) key pair
        $privatekey = \openssl_pkey_new($opensslConfig);
            
        // Generate a certificate signing request
        $csr = \openssl_csr_new($details, $privatekey, $opensslConfig);
    
        // Create a self-signed certificate valid for 365 days
        $sslcert = \openssl_csr_sign($csr, null, $privatekey, 365, $opensslConfig);
    
        // Create key file. Note no passphrase
        \openssl_pkey_export_to_file($privatekey, $ssl_path.$privatekeyFile, null, $opensslConfig);
    
        // Create server certificate 
        \openssl_x509_export_to_file($sslcert, $ssl_path.$certificateFile, false);
        
        // Create a signing request file 
        \openssl_csr_export_to_file($csr, $ssl_path.$signingFile);

        return $ssl_path;
    }
    
    public function selecting(string $table = null, $columnFields = '*', ...$conditions) 
    {
        return ;
    }

    /**
     * Get SQL statement string from selecting method instead of executing get_result
     * @return string
     */
    private function select_sql($table = '', $columnFields = '*', ...$conditions)
    {
        return ;         
    }

    public function create_select(string $newTable, $fromColumns, $oldTable = null, ...$conditions) 
    {
        return ; 
    }
    
    public function select_into(string $newTable, $fromColumns, $oldTable = null, ...$conditions) 
    {
        return ;    
    }

    public function update(string $table = null, $keyAndValue, ...$whereConditions) 
    {
        return ;
    }   
         
    public function delete(string $table = null, ...$whereConditions) 
    {
        return ;     
    }

	/**
    * Helper does the actual insert or replace query with an array
	* @return mixed bool/results - false for error
	*/
    private function _query_insert_replace($table = '', $keyAndValue, $type = '', $execute = true) 
    {
        return ;
	}
        
    public function replace(string $table = null, $keyAndValue) 
    {
        return ;
    }

    public function insert(string $table = null, $keyAndValue) 
    {
        return ;
    }

    public function insert_select(string $toTable = null, $toColumns = '*', $fromTable = null, $fromColumns = '*', ...$conditions) 
    {
        return ;     
    }

   public function create(string $table = null, ...$schemas) 
   {
        return ;
   }

   // todo not finish, not tested
   public function alter(string $table = null, ...$schemas) 
   {
        return ;
   }

   public function drop(string $table = null) 
   {
        return ;
   }
}
