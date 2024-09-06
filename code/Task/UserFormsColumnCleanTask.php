<?php

namespace SilverStripe\UserForms\Task;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\UserForms\Model\EditableFormField;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * UserForms Column Clean Task
 *
 * Column clean up tasks for Userforms
 *
 * @package userforms
 */

class UserFormsColumnCleanTask extends BuildTask
{
    protected static string $commandName = 'userforms-column-clean';

    protected string $title = 'UserForms EditableFormField Column Clean task';

    protected static string $description = 'Removes unused columns from EditableFormField for MySQL databases;';

    protected $tables = [EditableFormField::class];

    protected $keepColumns = ['ID'];

    /**
     * Publish the existing forms.
     */
    protected function execute(InputInterface $input, PolyOutput $output): int
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
                $output->writeln("Tasks run already on $db exiting");
                return Command::SUCCESS;
            }
            $backedUp = 0;
            foreach ($liveColumns as $index => $column) {
                if ($backedUp == 0) {
                    $output->writeln("Backing up $db <br />");
                    $output->writeln("Creating Backup_$db <br />");
                    // backup table
                    $query = "CREATE TABLE Backup_$db LIKE $db";
                    DB::query($query);
                    $output->writeln("Populating Backup_$db <br />");
                    $query = "INSERT Backup_$db SELECT * FROM $db";
                    DB::query($query);
                    $backedUp = 1;
                }
                if (!isset($columns[$column]) && !in_array($column, $this->keepColumns ?? [])) {
                    $output->writeln("Dropping $column from $db <br />");
                    $query = "ALTER TABLE $db DROP COLUMN $column";
                    DB::query($query);
                }
            }
        }
        return Command::SUCCESS;
    }
}
