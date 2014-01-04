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

namespace Doctrine\DBAL\Platforms;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Constraint;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;

/**
 * The InformixPlatform provides the behavior, features and SQL dialect of the
 * IBM Informix database platform.
 *
 * @author Jose M. Alonso M.  <josemalonsom@yahoo.es>
 * @link   www.doctrine-project.org
 */
class InformixPlatform extends AbstractPlatform
{

    /**
     * {@inheritDoc}
     */
    public function getBinaryTypeDeclarationSQL(array $field)
    {
        return 'BYTE';
    }

    /**
     * {@inheritDoc}
     */
    public function getBlobTypeDeclarationSQL(array $field)
    {
       /*
        The use of BLOB type in Informix is tricky and doesn't work properly
        with the pdo_informix extension so the BYTE type is used instead.
       */
        return 'BYTE';
    }

    /**
     * {@inheritDoc}
     */
    public function initializeDoctrineTypeMappings()
    {
        $this->doctrineTypeMapping = array(
            'bigint'            => 'bigint',
            'bigserial'         => 'bigint',
            'blob'              => 'blob',
            'boolean'           => 'boolean',
            'byte'              => 'blob',
            'character'         => 'string',
            'character varying' => 'string',
            'char'              => 'string',
            'clob'              => 'text',
            'date'              => 'date',
            'datetime'          => 'datetime',
            'dec'               => 'decimal',
            'decimal'           => 'decimal',
            'double precision'  => 'float',
            'float'             => 'float',
            'int8'              => 'bigint',
            'integer'           => 'integer',
            'int'               => 'integer',
            'lvarchar'          => 'text',
            'nchar'             => 'string',
            'numeric'           => 'decimal',
            'nvarchar'          => 'string',
            'real'              => 'decimal',
            'serial8'           => 'bigint',
            'serial'            => 'integer',
            'smallfloat'        => 'decimal',
            'smallint'          => 'smallint',
            'text'              => 'text',
            'varchar'           => 'string',
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed)
    {
        return $fixed
            ? ($length ? 'CHAR(' . $length . ')' : 'CHAR(255)')
            : ($length ? 'VARCHAR(' . $length . ')' : 'VARCHAR(255)');
    }

    /**
     * {@inheritDoc}
     */
    public function getClobTypeDeclarationSQL(array $field)
    {
        /*
         The use of CLOB type in Informix is tricky and doesn't work properly
         with the pdo_informix extension so the TEXT type is used instead.
        */
        return 'TEXT';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'informix';
    }

    /**
     * {@inheritDoc}
     *
     * Informix doesn't support table names between quotes so we prevent to use them.
     *
     * @return string
     */
    public function getIdentifierQuoteCharacter()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxIdentifierLength()
    {
        return 128;
    }

    /**
     * {@inheritDoc}
     */
    public function getVarcharMaxLength()
    {
        return 255;
    }

    /**
     * {@inheritDoc}
     */
    public function getMd5Expression($column)
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getNowExpression()
    {
        return 'TODAY';
    }

    /**
     * {@inheritDoc}
     */
    public function getNotExpression($expression)
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getPiExpression()
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateDiffExpression($date1, $date2)
    {
        return $date1 . '::DATE - ' . $date2 . '::DATE';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddHourExpression($date, $hours)
    {
        return $date . ' + interval(' . $hours . ') hour(9) to hour';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubHourExpression($date, $hours)
    {
        return $date . ' - interval(' . $hours . ') hour(9) to hour';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddDaysExpression($date, $days)
    {
        return $date . ' + interval(' . $days . ') day(9) to day';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubDaysExpression($date, $days)
    {
        return $date . ' - interval(' . $days . ') day(9) to day';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddMonthExpression($date, $months)
    {
        return 'ADD_MONTHS(' . $date . ',' . $months . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubMonthExpression($date, $months)
    {
        return $this->getDateAddMonthExpression($date, -abs($months));
    }

    /**
     * {@inheritDoc}
     */
    public function getBitAndComparisonExpression($value1, $value2)
    {
        return 'BITAND(' . $value1 . ', ' . $value2 . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function getBitOrComparisonExpression($value1, $value2)
    {
        return 'BITOR(' . $value1 . ', ' . $value2 . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function getDropForeignKeySQL($foreignKey, $table)
    {
        if ( $foreignKey instanceof ForeignKeyConstraint ) {
            $foreignKey = $foreignKey->getQuotedName($this);
        }

        if ( $table instanceof Table ) {
            $table = $table->getQuotedName($this);
        }

        return 'ALTER TABLE ' . $table . ' DROP CONSTRAINT ' . $foreignKey;
    }

    /**
     * {@inheritDoc}
     * Informix don't support comments on columns
     */
    public function getCommentOnColumnSQL($tableName, $columnName, $comment)
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getBooleanTypeDeclarationSQL(array $columnDef)
    {
        return 'BOOLEAN';
    }

    /**
     * {@inheritDoc}
     */
    public function getIntegerTypeDeclarationSQL(array $columnDef)
    {
        return empty($columnDef['autoincrement'])
            ? 'INTEGER'
            : 'SERIAL';
    }

    /**
     * {@inheritDoc}
     */
    public function getBigIntTypeDeclarationSQL(array $columnDef)
    {
        return empty($columnDef['autoincrement'])
            ? 'BIGINT'
            : 'BIGSERIAL';
    }

    /**
     * {@inheritDoc}
     */
    public function getSmallIntTypeDeclarationSQL(array $columnDef)
    {
        return 'SMALLINT';
    }

    /**
     * {@inheritDoc}
     */
    protected function _getCommonIntegerTypeDeclarationSQL(array $columnDef)
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeTypeDeclarationSQL(array $fieldDeclaration)
    {
        return 'DATETIME YEAR TO SECOND';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTypeDeclarationSQL(array $fieldDeclaration)
    {
        return 'DATE';
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeTypeDeclarationSQL(array $fieldDeclaration)
    {
        return 'DATETIME HOUR TO SECOND';
    }

    /**
     * {@inheritDoc}
     */
    public function getListDatabasesSQL()
    {
        return 'SELECT sysmaster:sysdatabases.name FROM sysmaster:sysdatabases';
    }

    /**
     * {@inheritDoc}
     */
    public function getListSequencesSQL($database)
    {
        return 'SELECT st.tabname as sequence, ss.start_val, ss.inc_val '
            . 'FROM syssequences ss, systables st '
            . 'WHERE ss.tabid = st.tabid';
    }

    /**
     * {@inheritdoc}
     */
    public function getSequenceNextValSQL($sequenceName)
    {
        return $sequenceName . '.NEXTVAL';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableConstraintsSQL($table)
    {
        return 'SELECT '
            . 'sc.constrid, sc.constrname, sc.owner, sc.tabid, '
            . 'sc.constrtype, sc.idxname, sc.collation '
            . 'FROM systables st, sysconstraints sc WHERE '
            . 'st.tabname = "' . $table . '" '
            . 'AND st.tabid = sc.tabid';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableColumnsSQL($table, $database = null)
    {
        return 'SELECT st.tabname, sc.colname, sc.colno, sc.coltype, '
            . 'CASE '
            . '    (CASE WHEN (sc.coltype BETWEEN 256 AND 309) '
            . '          THEN (sc.coltype - 256) ELSE sc.coltype END) '
            . 'WHEN 0    THEN "char" '
            . 'WHEN 1    THEN "smallint" '
            . 'WHEN 2    THEN "integer" '
            . 'WHEN 3    THEN "float" '
            . 'WHEN 4    THEN "smallfloat" '
            . 'WHEN 5    THEN "decimal" '
            . 'WHEN 6    THEN "serial" '
            . 'WHEN 7    THEN "date" '
            . 'WHEN 8    THEN "money" '
            . 'WHEN 9    THEN "null" '
            . 'WHEN 10   THEN "datetime" '
            . 'WHEN 11   THEN "byte" '
            . 'WHEN 12   THEN "text" '
            . 'WHEN 13   THEN "varchar" '
            . 'WHEN 14   THEN "interval" '
            . 'WHEN 15   THEN "nchar" '
            . 'WHEN 16   THEN "nvarchar" '
            . 'WHEN 17   THEN "int8" '
            . 'WHEN 18   THEN "serial8" '
            . 'WHEN 19   THEN "set" '
            . 'WHEN 20   THEN "multiset" '
            . 'WHEN 21   THEN "list" '
            . 'WHEN 22   THEN "row" '
            . 'WHEN 23   THEN "collection" '
            . 'WHEN 43   THEN "lvarchar" '
            . 'WHEN 45   THEN "boolean" '
            . 'WHEN 52   THEN "bigint" '
            . 'WHEN 53   THEN "bigserial" '
            . 'ELSE '
            . '    CASE '
            . '        WHEN (sc.extended_id > 0) THEN '
            . '        (SELECT UPPER(name) FROM sysxtdtypes '
            . '         WHERE extended_id = sc.extended_id) '
            . '    ELSE '
            . '        "unknown" '
            . '    END '
            . 'END typename, '
            . 'CASE '
            . '    WHEN (sc.coltype in (3, 4, 5, 8) and (sc.collength / 256) >= 1) '
            . '    THEN (sc.collength / 256)::INT '
            . '    ELSE NULL '
            . 'END precision, '
            . 'CASE '
            . '    WHEN (sc.coltype in (3, 4, 5, 8)) THEN '
            . '        CASE '
            . '            WHEN (MOD(sc.collength, 256) = 255) THEN NULL '
            . '            ELSE MOD(sc.collength, 256)::INT '
            . '         END '
            . '    ELSE '
            . '        NULL '
            . 'END scale, '
            . 'CASE '
            . '    WHEN (sc.coltype < 256) THEN "Y" '
            . '    WHEN (sc.coltype BETWEEN 256 AND 309) THEN "N" '
            . 'ELSE NULL '
            . 'END nulls, '
            . 'CASE sd.type '
            . '    WHEN "C" THEN "CURRENT" '
            . '    WHEN "T" THEN "TODAY" '
            . '    WHEN "N" THEN "NULL" '
            . 'ELSE sd.default '
            . 'END default '
            . 'FROM '
            . 'systables st '
            . 'LEFT OUTER JOIN syscolumns sc ON st.tabid = sc.tabid '
            . 'LEFT OUTER JOIN sysdefaults sd ON (sc.tabid = sd.tabid AND sc.colno = sd.colno) '
            . 'WHERE '
            . 'UPPER(st.tabname) = UPPER("' . $table . '") ';

    }

    /**
     * {@inheritDoc}
     */
    public function getListTablesSQL()
    {
        return 'SELECT systables.tabname FROM systables WHERE tabtype = "T"';
    }

    /**
     * {@inheritDoc}
     */
    public function getListUsersSQL()
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function getListViewsSQL($database)
    {
        return 'SELECT systables.tabname, sysviews.viewtext '
            . 'FROM systables, sysviews WHERE systables.tabtype = "V" '
            . 'AND systables.tabid = sysviews.tabid';

    }

    /**
     * {@inheritDoc}
     */
    public function getListTableIndexesSQL($table, $currentDatabase = null)
    {

        return 'SELECT si.idxname, si.idxtype, ctr.constrtype, '
            . 'sc1.colname  col1,  sc2.colname  col2,  sc3.colname  col3,  '
            . 'sc4.colname  col4,  sc5.colname  col5,  sc6.colname  col6,  '
            . 'sc7.colname  col7,  sc8.colname  col8,  sc9.colname  col9,  '
            . 'sc10.colname col10, sc11.colname col11, sc12.colname col12, '
            . 'sc13.colname col13, sc14.colname col14, sc15.colname col15, '
            . 'sc16.colname col16 '
            . 'FROM  systables st '
            . 'INNER JOIN sysindexes si '
            . '    ON si.tabid = st.tabid  '
            . 'LEFT OUTER JOIN sysconstraints ctr '
            . '    ON (ctr.tabid = st.tabid and ctr.idxname = si.idxname) '
            . 'LEFT OUTER JOIN syscolumns sc1 '
            . '    ON (si.part1 = sc1.colno AND si.tabid = sc1.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc2 '
            . '    ON (si.part2 = sc2.colno AND si.tabid = sc2.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc3 '
            . '    ON (si.part3 = sc3.colno AND si.tabid = sc3.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc4 '
            . '    ON (si.part4 = sc4.colno AND si.tabid = sc4.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc5 '
            . '    ON (si.part5 = sc5.colno AND si.tabid = sc5.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc6 '
            . '    ON (si.part6 = sc6.colno AND si.tabid = sc6.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc7 '
            . '    ON (si.part7 = sc7.colno AND si.tabid = sc7.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc8 '
            . '    ON (si.part8 = sc8.colno AND si.tabid = sc8.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc9 '
            . '    ON (si.part9 = sc9.colno AND si.tabid = sc9.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc10 '
            . '    ON (si.part10 = sc10.colno AND si.tabid = sc10.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc11 '
            . '    ON (si.part11 = sc11.colno AND si.tabid = sc11.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc12 '
            . '    ON (si.part12 = sc12.colno AND si.tabid = sc12.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc13 '
            . '    ON (si.part13 = sc13.colno AND si.tabid = sc13.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc14 '
            . '    ON (si.part14 = sc14.colno AND si.tabid = sc14.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc15 '
            . '    ON (si.part15 = sc15.colno AND si.tabid = sc15.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc16 '
            . '    ON (si.part16 = sc16.colno AND si.tabid = sc16.tabid) '
            . 'WHERE UPPER(st.tabname) = UPPER("' . $table . '") ';

    }

    /**
     * {@inheritDoc}
     *
     * The SQL sentence used is based on the next thread:
     * {@link http://www.databaseteam.org/6-informix/8791d9fcbeab8020.htm}.
     */
    public function getListTableForeignKeysSQL($table)
    {

        return 'SELECT st.tabname, sc.constrname, sr.updrule, sr.delrule, '
            . 'refst.tabname reftabname, refsc.idxname refconstrname, '
            . 'sc1.colname  col1,  sc2.colname  col2,  sc3.colname  col3,  '
            . 'sc4.colname  col4,  sc5.colname  col5,  sc6.colname  col6,  '
            . 'sc7.colname  col7,  sc8.colname  col8,  sc9.colname  col9,  '
            . 'sc10.colname col10, sc11.colname col11, sc12.colname col12, '
            . 'sc13.colname col13, sc14.colname col14, sc15.colname col15, '
            . 'sc16.colname col16, '
            . 'pksc1.colname  pkcol1,  pksc2.colname  pkcol2,  pksc3.colname  pkcol3,  '
            . 'pksc4.colname  pkcol4,  pksc5.colname  pkcol5,  pksc6.colname  pkcol6,  '
            . 'pksc7.colname  pkcol7,  pksc8.colname  pkcol8,  pksc9.colname  pkcol9,  '
            . 'pksc10.colname pkcol10, pksc11.colname pkcol11, pksc12.colname pkcol12, '
            . 'pksc13.colname pkcol13, pksc14.colname pkcol14, pksc15.colname pkcol15, '
            . 'pksc16.colname pkcol16 '
            . 'FROM systables st '
            . 'INNER JOIN sysconstraints sc '
            . '    ON st.tabid = sc.tabid '
            . 'INNER JOIN sysreferences sr '
            . '    ON sc.constrid = sr.constrid '
            . 'INNER JOIN systables refst '
            . '    ON sr.ptabid = refst.tabid '
            . 'INNER JOIN sysindexes si '
            . '    ON sc.idxname = si.idxname '
            . 'INNER JOIN sysconstraints refsc '
            . '    ON sr.primary = refsc.constrid '
            . 'INNER JOIN sysindexes refsi '
            . '    ON refsc.idxname = refsi.idxname '
            . 'LEFT OUTER JOIN syscolumns sc1 '
            . '    ON (si.part1 = sc1.colno AND si.tabid = sc1.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc2 '
            . '    ON (si.part2 = sc2.colno AND si.tabid = sc2.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc3 '
            . '    ON (si.part3 = sc3.colno AND si.tabid = sc3.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc4 '
            . '    ON (si.part4 = sc4.colno AND si.tabid = sc4.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc5 '
            . '    ON (si.part5 = sc5.colno AND si.tabid = sc5.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc6 '
            . '    ON (si.part6 = sc6.colno AND si.tabid = sc6.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc7 '
            . '    ON (si.part7 = sc7.colno AND si.tabid = sc7.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc8 '
            . '    ON (si.part8 = sc8.colno AND si.tabid = sc8.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc9 '
            . '    ON (si.part9 = sc9.colno AND si.tabid = sc9.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc10 '
            . '    ON (si.part10 = sc10.colno AND si.tabid = sc10.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc11 '
            . '    ON (si.part11 = sc11.colno AND si.tabid = sc11.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc12 '
            . '    ON (si.part12 = sc12.colno AND si.tabid = sc12.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc13 '
            . '    ON (si.part13 = sc13.colno AND si.tabid = sc13.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc14 '
            . '    ON (si.part14 = sc14.colno AND si.tabid = sc14.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc15 '
            . '    ON (si.part15 = sc15.colno AND si.tabid = sc15.tabid) '
            . 'LEFT OUTER JOIN syscolumns sc16 '
            . '    ON (si.part16 = sc16.colno AND si.tabid = sc16.tabid) '
            . 'LEFT OUTER JOIN syscolumns pksc1 '
            . '    ON (refsi.part1 = pksc1.colno AND refsi.tabid = pksc1.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc2 '
            . '    ON (refsi.part2 = pksc2.colno AND refsi.tabid = pksc2.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc3 '
            . '    ON (refsi.part3 = pksc3.colno AND refsi.tabid = pksc3.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc4 '
            . '    ON (refsi.part4 = pksc4.colno AND refsi.tabid = pksc4.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc5 '
            . '    ON (refsi.part5 = pksc5.colno AND refsi.tabid = pksc5.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc6 '
            . '    ON (refsi.part6 = pksc6.colno AND refsi.tabid = pksc6.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc7 '
            . '    ON (refsi.part7 = pksc7.colno AND refsi.tabid = pksc7.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc8 '
            . '    ON (refsi.part8 = pksc8.colno AND refsi.tabid = pksc8.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc9 '
            . '    ON (refsi.part9 = pksc9.colno AND refsi.tabid = pksc9.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc10 '
            . '    ON (refsi.part10 = pksc10.colno AND refsi.tabid = pksc10.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc11 '
            . '    ON (refsi.part11 = pksc11.colno AND refsi.tabid = pksc11.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc12 '
            . '    ON (refsi.part12 = pksc12.colno AND refsi.tabid = pksc12.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc13 '
            . '    ON (refsi.part13 = pksc13.colno AND refsi.tabid = pksc13.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc14 '
            . '    ON (refsi.part14 = pksc14.colno AND refsi.tabid = pksc14.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc15 '
            . '    ON (refsi.part15 = pksc15.colno AND refsi.tabid = pksc15.tabid)'
            . 'LEFT OUTER JOIN syscolumns pksc16 '
            . '    ON (refsi.part16 = pksc16.colno AND refsi.tabid = pksc16.tabid)'
            . 'WHERE '
            . 'UPPER(st.tabname) = UPPER("' . $table . '") '
            . 'AND sc.constrtype = "R" ';

    }

    /**
     * {@inheritDoc}
     */
    public function getCreateViewSQL($name, $sql)
    {
        return "CREATE VIEW ".$name." AS ".$sql;
    }

    /**
     * {@inheritDoc}
     */
    public function getDropViewSQL($name)
    {
        return "DROP VIEW ".$name;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateDatabaseSQL($database)
    {
        return "CREATE DATABASE ".$database." WITH LOG";
    }

    /**
     * {@inheritDoc}
     */
    public function supportsCreateDropDatabase()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentDateSQL()
    {
        return 'TODAY';
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentTimeSQL()
    {
        return 'CURRENT HOUR TO SECOND';
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentTimestampSQL()
    {
        return 'CURRENT';
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexDeclarationSQL($name, Index $index)
    {
        return $this->getUniqueConstraintDeclarationSQL($name, $index);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getCreateTableSQL($tableName, array $columns, array $options = array())
    {
        $indexes = array();

        if ( isset($options['indexes']) ) {
            $indexes = $options['indexes'];
        }

        $options['indexes'] = array();

        $sqls = parent::_getCreateTableSQL($tableName, $columns, $options);

        foreach ( $indexes as $definition ) {
            $sqls[] = $this->getCreateIndexSQL($definition, $tableName);
        }

        return $sqls;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlterTableSQL(TableDiff $diff)
    {
        $sql = array();
        $columnSql = array();

        $queryParts = array();

        foreach ( $diff->addedColumns as $column ) {

            if ( $this->onSchemaAlterTableAddColumn($column, $diff, $columnSql) ) {
                continue;
            }

            $queryParts[] = 'ADD ' . $this->getColumnDeclarationSQL($column->getQuotedName($this), $column->toArray());
        }

        foreach ( $diff->removedColumns as $column ) {

            if ( $this->onSchemaAlterTableRemoveColumn($column, $diff, $columnSql) ) {
                continue;
            }

            $queryParts[] =  'DROP ' . $column->getQuotedName($this);
        }

        foreach ( $diff->changedColumns as $columnDiff ) {

            if ( $this->onSchemaAlterTableChangeColumn($columnDiff, $diff, $columnSql) ) {
                continue;
            }

            /* @var $columnDiff \Doctrine\DBAL\Schema\ColumnDiff */
            $column = $columnDiff->column;
            $queryParts[] =  'ALTER ' . ($columnDiff->oldColumnName) . ' '
                    . $this->getColumnDeclarationSQL($column->getQuotedName($this), $column->toArray());
        }

        foreach ( $diff->renamedColumns as $oldColumnName => $column ) {

            if ( $this->onSchemaAlterTableRenameColumn($oldColumnName, $column, $diff, $columnSql) ) {
                continue;
            }

            $queryParts[] =  'RENAME COLUMN' . $oldColumnName . ' TO ' . $column->getQuotedName($this);
        }

        $tableSql = array();

        if ( ! $this->onSchemaAlterTable($diff, $tableSql) ) {

            if ( count($queryParts) > 0 ) {
                $sql[] = 'ALTER TABLE ' . $diff->name . ' ' . implode(" ", $queryParts);
            }

            $sql = array_merge($sql, $this->_getAlterTableIndexForeignKeySQL($diff));

            if ( $diff->newName !== false ) {
                $sql[] =  'RENAME TABLE TO ' . $diff->newName;
            }
        }

        return array_merge($sql, $tableSql, $columnSql);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateTemporaryTableSnippetSQL()
    {
        return 'CREATE ' . $this->getTemporaryTableSQL() . ' TABLE';
    }

    /**
     * {@inheritDoc}
     */
    public function getTemporaryTableSQL()
    {
        return 'TEMP';
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateSequenceSQL(Sequence $sequence)
    {
        return 'CREATE SEQUENCE ' . $sequence->getQuotedName($this)
            . ' START WITH ' . $sequence->getInitialValue()
            . ' INCREMENT BY ' . $sequence->getAllocationSize()
            . ' MINVALUE ' . $sequence->getInitialValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getAlterSequenceSQL(Sequence $sequence)
    {
        return 'ALTER SEQUENCE ' . $sequence->getQuotedName($this)
            . ' INCREMENT BY ' . $sequence->getAllocationSize();
    }

    /**
     * {@inheritdoc}
     */
    public function getDropSequenceSQL($sequence)
    {
        if ( $sequence instanceof Sequence ) {
            $sequence = $sequence->getQuotedName($this);
        }

        return 'DROP SEQUENCE ' . $sequence;
    }

    /**
     * {@inheritDoc}
     */
    protected function doModifyLimitQuery($query, $limit, $offset = null)
    {
        if ( $limit === null && $offset === null ) {
            return $query;
        }

        $snippet  = $offset ? "SKIP $offset " : "";
        $snippet .= $limit  ? "LIMIT $limit " : "";

        $sql = preg_replace('/SELECT\s+/i', 'SELECT ' . $snippet, $query, 1);

        return $sql;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubstringExpression($value, $from, $length = null)
    {
        if ( $length === null ) {
            return 'SUBSTR(' . $value . ', ' . $from . ')';
        }

        return 'SUBSTR(' . $value . ', ' . $from . ', ' . $length . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function supportsIdentityColumns()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function prefersIdentityColumns()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * Informix returns all column names in SQL result sets in uppercase.
     */
    public function getSQLResultCasing($column)
    {
        return strtoupper($column);
    }

    /**
     * {@inheritDoc}
     */
    public function getForUpdateSQL()
    {
        return ' ';
    }

    /**
     * {@inheritDoc}
     */
    public function getDummySelectSQL()
    {
        return 'SELECT 1 FROM SYSTABLES WHERE TABID = 1';
    }

    /**
     * {@inheritDoc}
     */
    protected function getReservedKeywordsClass()
    {
        return 'Doctrine\DBAL\Platforms\Keywords\InformixKeywords';
    }

    /**
     * {@inheritDoc}
     */
    public function getUniqueConstraintDeclarationSQL($name, Index $index)
    {
        $columns = $index->getQuotedColumns($this);

        if ( count($columns) === 0 ) {
            throw new \InvalidArgumentException("Incomplete definition. 'columns' required.");
        }

        return ' UNIQUE (' . $this->getIndexFieldDeclarationListSQL($columns)
            . ') CONSTRAINT ' . $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateConstraintSQL(Constraint $constraint, $table)
    {

        $constraintSql = parent::getCreateConstraintSQL($constraint, $table);

        return $this->repositionContraintNameSQL($constraint, $constraintSql);

    }

    /**
     * {@inheritDoc}
     */
    public function getForeignKeyBaseDeclarationSQL(ForeignKeyConstraint $foreignKey)
    {

        $foreignKeySql = parent::getForeignKeyBaseDeclarationSQL($foreignKey);

        return $this->repositionContraintNameSQL($foreignKey, $foreignKeySql);

    }

    /**
     * {@inheritDoc}
     */
    public function getCreateForeignKeySQL(ForeignKeyConstraint $foreignKey, $table)
    {
        if ( $table instanceof Table ) {
            $table = $table->getQuotedName($this);
        }

        $query = 'ALTER TABLE ' . $table . ' ADD CONSTRAINT '
               . $this->getForeignKeyDeclarationSQL($foreignKey);

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function getForeignKeyDeclarationSQL(ForeignKeyConstraint $foreignKey)
    {
        $foreignKeySql = parent::getForeignKeyDeclarationSQL($foreignKey);

        return $this->repositionContraintNameSQL($foreignKey, $foreignKeySql);

    }

    /**
    * Repositions the declaration of the name of the constraint.
    *
    * In Informix the name of the constraint is placed at the end of the
    * declaration.
    *
    * @param \Doctrine\DBAL\Schema\Constraint $constraint
    * @param string $sql
    *
    * @return string
    */
    protected function repositionContraintNameSQL(Constraint $constraint, $sql) {

        if ( $constraintName = $constraint->getName() ) {

            if ( preg_match("/\bADD\s+CONSTRAINT\b/i", $sql) ) {

                $sql = preg_replace("/\bADD\s+CONSTRAINT\s+$constraintName\b/i",
                                    ' ADD CONSTRAINT ', $sql);
            }
            else {

                $sql = preg_replace("/\bCONSTRAINT\s+$constraintName\b/i", '', $sql);
            }

            $sql .= ' CONSTRAINT ' . $constraintName;

        }

        return $sql;

    }
}
