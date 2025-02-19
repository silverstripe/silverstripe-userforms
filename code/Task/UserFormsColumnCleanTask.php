<?php

namespace SilverStripe\UserForms\Task;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\Dev\Deprecation;
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
 * @deprecated 6.4.0 Will be removed without equivalent functionality to replace it
 */

class UserFormsColumnCleanTask extends BuildTask
{
    protected static string $commandName = 'userforms-column-clean';

    protected string $title = 'UserForms EditableFormField Column Clean task';

    protected static string $description = 'Removes unused columns from EditableFormField for MySQL databases;';

    protected $tables = [EditableFormField::class];

    protected $keepColumns = ['ID'];

    public function __construct()
    {
        Deprecation::noticeWithNoReplacment('6.4.0', '', Deprecation::SCOPE_CLASS);
        parent::__construct();
    }

    /**
     * Publish the existing forms.
     */
    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $schema = DataObject::getSchema();

        foreach ($this->tables as $db) {
            $table = $schema->tableName($db);
            $columns = $schema->databaseFields($db);
            $query = "SHOW COLUMNS FROM $table";
            $liveColumns = DB::query($query)->column();
            $query = "SHOW TABLES LIKE 'Backup_$table'";
            $tableExists = DB::query($query)->value();
            if ($tableExists != null) {
                $output->writeln("Tasks run already on $table exiting");
                return Command::SUCCESS;
            }
            $backedUp = false;
            foreach ($liveColumns as $column) {
                if (!$backedUp) {
                    $output->writeln("Backing up $table <br />");
                    $output->writeln("Creating Backup_$table <br />");
                    // backup table
                    $query = "CREATE TABLE Backup_$table LIKE $table";
                    DB::query($query);
                    $output->writeln("Populating Backup_$table <br />");
                    $query = "INSERT Backup_$table SELECT * FROM $table";
                    DB::query($query);
                    $backedUp = true;
                }
                if (!isset($columns[$column]) && !in_array($column, $this->keepColumns ?? [])) {
                    $output->writeln("Dropping $column from $table <br />");
                    $query = "ALTER TABLE $table DROP COLUMN $column";
                    DB::query($query);
                }
            }
        }
        return Command::SUCCESS;
    }
}
