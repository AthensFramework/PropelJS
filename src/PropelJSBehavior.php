<?php

namespace Athens\PropelJS;

use Propel\Generator\Model\Behavior;

use Propel\Common\Pluralizer\StandardEnglishPluralizer;
use Propel\Common\Pluralizer\PluralizerInterface;

class PropelJSBehavior extends Behavior
{
    /** @var  PluralizerInterface */
    protected $pluralizer;

    private static $tablesProcessed = 0;

    /**
     * @return PluralizerInterface
     */
    public function getPluralizer() {
        if ($this->pluralizer === null) {
            $this->pluralizer = new StandardEnglishPluralizer();
        }
        
        return $this->pluralizer;
    }

    public function modifyTable()
    {
        self::$tablesProcessed++;

        if (self::$tablesProcessed === count($this->getTables())) {
            self::execute();
        }
    }
    
    public function execute()
    {

        $jsDirectory = getcwd() . '/generated-js';
        $apiDirectory = getcwd() . '/generated-api';

        if(!file_exists($jsDirectory)) {
            mkdir($jsDirectory);
        }

        if(!file_exists($apiDirectory)) {
            mkdir($apiDirectory);
        }

        $databaseName = $this->getDatabase()->getName();
        $namespace = $this->getDatabase()->getNamespace();

        $tables = $this->getTables();

        $tablePlurals = [];
        $tablePhpNames = [];
        $tableColumns = [];
        foreach ($tables as $table) {
            $tablePhpNames[$table->getName()] = $table->getPhpName();
            $tablePlurals[$table->getName()] = $this->getPluralizer()->getPluralForm($table->getName());

            $tableColumns[$table->getName()] = [];
            foreach ($table->getColumns() as $column) {
                $tableColumns[$table->getName()][$column->getPhpName()] = $column->getType();
            }
        }

        $js = ($this->renderTemplate('js',
            [
                'databaseName' => $databaseName,
                'tablePhpNames' => $tablePhpNames,
                'tablePlurals' => $tablePlurals,
                'tableColumns' => $tableColumns,
            ]
        ));
        file_put_contents($jsDirectory . "/$databaseName.js", $js);

        $api = ($this->renderTemplate('API',
            [
                'namespace' => $namespace,
                'tablePhpNames' => $tablePhpNames,
                'tablePlurals' => $tablePlurals,
            ]
        ));
        file_put_contents($apiDirectory . "/API.php", $api);
        
    }

}