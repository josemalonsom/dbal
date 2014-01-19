<?php

namespace Doctrine\Tests\DBAL\Platforms;

use Doctrine\DBAL\Platforms\InformixPlatform;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;

require_once __DIR__ . '/../../TestInit.php';

class InformixPlatformTest extends AbstractPlatformTestCase
{

    public function createPlatform()
    {
        return new InformixPlatform();
    }

    public function getGenerateTableSql()
    {
        return 'CREATE TABLE test (id SERIAL NOT NULL, '
            . 'test VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))';
    }

    public function getGenerateTableWithMultiColumnUniqueIndexSql()
    {
        return array(
            'CREATE TABLE test (foo VARCHAR(255) DEFAULT NULL, bar VARCHAR(255) DEFAULT NULL)',
            'CREATE UNIQUE INDEX UNIQ_D87F7E0C8C73652176FF8CAA ON test (foo, bar)'
        );
    }

    public function getGenerateIndexSql()
    {
        return 'CREATE INDEX my_idx ON mytable (user_name, last_login)';
    } 

    public function getGenerateUniqueIndexSql()
    {
        return 'CREATE UNIQUE INDEX index_name ON test (test, test2)';
    }

    public function getGenerateForeignKeySql()
    {
        return 'ALTER TABLE test ADD CONSTRAINT FOREIGN KEY (fk_name_id) '
            . 'REFERENCES other_table (id)';
    }

    public function getGenerateConstraintUniqueIndexSql()
    {
        return 'ALTER TABLE test ADD CONSTRAINT UNIQUE (test) '
            . 'CONSTRAINT constraint_name';
    }

    public function getGenerateConstraintPrimaryIndexSql()
    {
        return 'ALTER TABLE test ADD CONSTRAINT PRIMARY KEY (test) '
            . 'CONSTRAINT constraint_name';
    }

    public function getGenerateConstraintForeignKeySql(ForeignKeyConstraint $fk)
    {
        return 'ALTER TABLE test ADD CONSTRAINT FOREIGN KEY (fk_name) '
            . 'REFERENCES foreign (id) CONSTRAINT constraint_fk';
    }

    public function testGeneratesBitAndComparisonExpressionSql()
    {
        return 'BITAND(2, 4)';
    }

    public function testGeneratesBitOrComparisonExpressionSql()
    {
        return 'BITOR(2, 4)';
    }

    public function getGenerateAlterTableSql()
    {
        return array(
            'RENAME COLUMN mytable.bar TO baz',
            'ALTER TABLE mytable ADD quota INTEGER DEFAULT NULL, DROP foo, '
            . 'MODIFY baz VARCHAR(255) DEFAULT \'def\' NOT NULL, '
            . 'MODIFY bloo BOOLEAN DEFAULT \'f\' NOT NULL',
            'RENAME TABLE mytable TO userlist',
        );
    }

    public function getQuotedColumnInPrimaryKeySQL()
    {
    }

    public function getQuotedColumnInIndexSQL()
    {
    }

    public function getQuotedColumnInForeignKeySQL()
    {
    }

    public function testQuotedColumnInPrimaryKeyPropagation()
    {
        $this->skipQuotedIdentifiersTests();
    }

    public function testQuotedColumnInIndexPropagation()
    {
        $this->skipQuotedIdentifiersTests();
    }

    public function testQuotedColumnInForeignKeyPropagation()
    {
        $this->skipQuotedIdentifiersTests();
    }

    public function testQuotesAlterTableRenameIndex()
    {
        $this->skipQuotedIdentifiersTests();
    }

    protected function skipQuotedIdentifiersTests()
    {
        $this->markTestSkipped('By default Informix doesn\'t support quoted identifiers.');
    }

    public function testReturnsBinaryTypeDeclarationSQL()
    {
        $this->assertSame(
            'BYTE', 
            $this->_platform->getBinaryTypeDeclarationSQL(array())
        );

        $this->assertSame(
            'BYTE', 
            $this->_platform->getBinaryTypeDeclarationSQL(array('length' => 0))
        );

        $this->assertSame(
            'BYTE', 
            $this->_platform->getBinaryTypeDeclarationSQL(array('length' => 9999999))
        );

        $this->assertSame(
            'BYTE', 
            $this->_platform->getBinaryTypeDeclarationSQL(array('fixed' => true))
        );

        $this->assertSame(
            'BYTE', 
            $this->_platform->getBinaryTypeDeclarationSQL(array('fixed' => true, 'length' => 0))
        );

        $this->assertSame(
            'BYTE', 
            $this->_platform->getBinaryTypeDeclarationSQL(array('fixed' => true, 'length' => 9999999))
        );
    }

    public function testConvertBooleans()
    {
        $this->assertSame(
            array('t', 'f', 't', 'f'),
            $this->_platform->convertBooleans(array(true, false, true, false))
        );

        $this->assertSame('t', $this->_platform->convertBooleans(true));
        $this->assertSame('f', $this->_platform->convertBooleans(false));
    }

}
