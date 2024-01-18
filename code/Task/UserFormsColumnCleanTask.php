<?php

namespace SilverStripe\UserForms\Task;

use SilverStripe\Dev\MigrationTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * UserForms Column Clean Task
 *
 * Column clean up tasks for Userforms
 *
 * @package userforms
 */

class UserFormsColumnCleanTask extends MigrationTask
{
    protected $title = 'UserForms EditableFormField Column Clean task';

    protected $description = 'Removes unused columns from EditableFormField for MySQL databases;';

    protected $tables = [EditableFormField::class];

    protected $keepColumns = ['ID'];

    /**
     * Publish the existing forms.
     */
    public function run($request)
    {
        $schema = DataObject::getSchema();

        foreach ($this->tables as $db) {
            $columns = $schema->databaseFields($db);
            $query = "SHOW COLUMNS FROM $db";
            $liveColumns = DB::query($query)->column();
            $backedUp = 0;
            $query = "SHOW TABLES LIKE 'Backup_$db'";
            $tableExists = DB::query($query)->value();
            if ($tableExists != null) {
                echo "Tasks run already on $db exiting";
                return;
            }
            $backedUp = 0;
            foreach ($liveColumns as $index => $column) {
                if ($backedUp == 0) {
                    echo "Backing up $db <br />";
                    echo "Creating Backup_$db <br />";
                    // backup table
                    $query = "CREATE TABLE Backup_$db LIKE $db";
                    DB::query($query);
                    echo "Populating Backup_$db <br />";
                    $query = "INSERT Backup_$db SELECT * FROM $db";
                    DB::query($query);
                    $backedUp = 1;
                }
                if (!isset($columns[$column]) && !in_array($column, $this->keepColumns ?? [])) {
                    echo "Dropping $column from $db <br />";
                    $query = "ALTER TABLE $db DROP COLUMN $column";
                    DB::query($query);
                }
            }
        }
    }
}
