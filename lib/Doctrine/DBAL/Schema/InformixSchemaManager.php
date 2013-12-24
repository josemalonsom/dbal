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

namespace Doctrine\DBAL\Schema;

use Doctrine\DBAL\Event\SchemaIndexDefinitionEventArgs;
use Doctrine\DBAL\Events;

/**
 * Informix Schema Manager.
 *
 * @author Jose M. Alonso M.  <josemalonsom@yahoo.es>
 * @link   www.doctrine-project.org
 */
class InformixSchemaManager extends AbstractSchemaManager
{
    /**
     * {@inheritdoc}
     */
    public function listTableNames()
    {
        $sql = $this->_platform->getListTablesSQL();
        $sql .= " AND UPPER(OWNER) = UPPER('".$this->_conn->getUsername()."')";

        $tables = $this->_conn->fetchAll($sql);

        return $this->_getPortableTablesList($tables);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $tableColumn = array_change_key_case($tableColumn, \CASE_LOWER);

        $fixed = null;
        $unsigned = false;
        $scale = false;
        $precision = false;

        $type = $this->_platform->getDoctrineTypeMapping($tableColumn['typename']);

        switch ( strtolower($tableColumn['typename']) ) {
            case 'byte':
            case 'character varying':
            case 'nvarchar':
            case 'varchar':
                $fixed = false;
                break;
            case 'char':
            case 'character':
            case 'nchar':
                $fixed = true;
                break;
            case 'dec':
            case 'decimal':
            case 'double':
            case 'double precision':
            case 'float':
            case 'numeric':
            case 'real':
            case 'smallfloat':
                $scale = $tableColumn['scale'];
                $precision = $tableColumn['precision'];
                break;
        }

        $options = array(
            'length'        => $tableColumn['default'] ? : null,
            'unsigned'      => (bool)$unsigned,
            'fixed'         => (bool)$fixed,
            'default'       => ($tableColumn['default'] == 'NULL') ? null : $tableColumn['default'],
            'notnull'       => (bool) ($tableColumn['nulls'] == 'N'),
            'scale'         => null,
            'precision'     => null,
            'platformOptions' => array(),
        );

        if ( $scale !== null && $precision !== null ) {
            $options['scale'] = $scale;
            $options['precision'] = $precision;
        }

        return new Column($tableColumn['colname'], \Doctrine\DBAL\Types\Type::getType($type), $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableTablesList($tables)
    {
        $tableNames = array();

        foreach ( $tables as $tableRow ) {
            $tableRow = array_change_key_case($tableRow, \CASE_LOWER);
            $tableNames[] = $tableRow['tabname'];
        }

        return $tableNames;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableTableIndexesList($tableIndexes, $tableName=null)
    {

        $indexesByColumns = array();

        foreach( $tableIndexes as $k => $v ) {

            $v = array_change_key_case($v, CASE_LOWER);

            foreach ( range(1,16) as $i ) {

              if ( ! empty($v["col$i"]) ) {

                  $indexesByColumns[] = array(
                      'column_name' => $v["col$i"],
                      'key_name'    => $v['idxname'],
                      'non_unique'  => $v['idxtype'] == 'D',
                      'primary'     => $v['constrtype'] == 'P',
                  );     
                
              }

            }

        }

        return parent::_getPortableTableIndexesList($indexesByColumns, $tableName);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableTableForeignKeyDefinition($tableForeignKey)
    {

        $tableForeignKey = array_change_key_case($tableForeignKey, CASE_LOWER);

        $fkColnames = $pkColnames = array();

        foreach ( range(1, 16) as $i ) {

            if ( ! empty($tableForeignKey["col$i"]) ) {
                $fkColnames[] = $tableForeignKey["col$i"];
            }

            if ( ! empty($tableForeignKey["pkcol$i"]) ) {
                $pkColnames[] = $tableForeignKey["pkcol$i"];
            }

        }

        $tableForeignKey['delrule'] = $this->_getPortableForeignKeyRuleDef($tableForeignKey['delrule']);
        $tableForeignKey['updrule'] = $this->_getPortableForeignKeyRuleDef($tableForeignKey['updrule']);

        return new ForeignKeyConstraint(
            array_map('trim', $fkColnames),
            $tableForeignKey['reftabname'],
            array_map('trim', $pkColnames),
            $tableForeignKey['constrname'],
            array(
                'onDelete' => $tableForeignKey['delrule'],
                'onUpdate' => $tableForeignKey['updrule'],
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableForeignKeyRuleDef($def)
    {
        if ( $def == "C" ) {
            return "CASCADE";
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableViewDefinition($view)
    {
        $view = array_change_key_case($view, \CASE_LOWER);

        if ( ! is_resource($view['text']) ) {
            $pos = strpos($view['text'], ' AS ');
            $sql = substr($view['text'], $pos+4);
        } else {
            $sql = '';
        }

        return new View($view['name'], $sql);
    }
}
