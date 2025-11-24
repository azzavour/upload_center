<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\SqlServerConnector as BaseConnector;
use PDO;

/**
 * Custom SQL Server connector that drops unsupported PDO attributes.
 *
 * pdo_sqlsrv in this environment throws "An invalid attribute was designated
 * on the PDO object" when PDO::ATTR_STRINGIFY_FETCHES is set, so we remove it
 * from the default options to allow connections to succeed.
 */
class SqlServerConnector extends BaseConnector
{
    /**
     * Override the default PDO connection options to exclude
     * PDO::ATTR_STRINGIFY_FETCHES which is not supported by pdo_sqlsrv here.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        // PDO::ATTR_STRINGIFY_FETCHES intentionally omitted
    ];
}
