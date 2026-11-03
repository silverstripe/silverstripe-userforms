<?php

namespace SilverStripe\UserForms\Model\Filters;

use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\Filters\SearchFilter;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use LogicException;

class SubmittedFieldFilter extends SearchFilter
{
    /**
     * @inheritDoc
     * @throws LogicException Throws a logic exception when applied to a data class that is not a SubmittedForm
     */
    public function applyOne(DataQuery $query): DataQuery
    {
        if (!is_a($query->dataClass(), SubmittedForm::class, true)) {
            throw new LogicException('DataQuery\'s Data Class is not an instance of ' . SubmittedForm::class);
        }

        $query->where([
            'EXISTS (' .
                'SELECT "ID" ' .
                'FROM "SubmittedFormField" ' .
                'WHERE (' .
                    '"ParentID" = "SubmittedForm"."ID" AND ' .
                    '"Name" = ? AND ' .
                    '"Value" LIKE ?' .
                ')' .
            ')' => [$this->name, $this->value],
        ]);

        return $query;
    }

    /**
     * @inheritDoc
     * @throws LogicException Throws a logic exception when applied to a data class that is not a SubmittedForm
     */
    public function excludeOne(DataQuery $query): DataQuery
    {
        if (!is_a($query->dataClass(), SubmittedForm::class, true)) {
            throw new LogicException('DataQuery\'s Data Class is not an instance of ' . SubmittedForm::class);
        }

        $query->where([
            'NOT EXISTS (' .
                'SELECT "ID" ' .
                'FROM "SubmittedFormField" ' .
                'WHERE (' .
                    '"ParentID" = "SubmittedForm"."ID" AND ' .
                    '"Name" = ? AND ' .
                    '"Value" LIKE ?' .
                ')' .
            ')' => [$this->name, $this->value],
        ]);

        return $query;
    }
}
