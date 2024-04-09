---
title: Troubleshooting
---

# Troubleshooting

Check the below if you have any issues during installation or use

## Installation issues

After installation make sure you have done a `dev/build` you may also need to flush the admin view by appending
`?flush=1` to the URL, e.g. `https://example.com/admin?flush=1`

## Checkbox or radio group custom messages not showing

If your project has a custom template for `UserFormsCheckboxSetField.ss` or `UserFormsOptionSetField.ss`, then you will need to ensure they include `$Top.getValidationAttributesHTML().RAW`. See

- [UserFormsCheckboxSetField.ss](../../templates/SilverStripe/UserForms/FormField/UserFormsCheckboxSetField.ss)
- [UserFormsOptionSetField.ss](../../templates/SilverStripe/UserForms/FormField/UserFormsOptionSetField.ss)

## UserForms `EditableFormField` column clean task

This [`UserFormsColumnCleanTask`](api:SilverStripe\UserForms\Task\UserFormsColumnCleanTask) task is used to clear unused columns from EditableFormField database tables.

The reason to clear these columns is because having surplus forms can break form saving.

Currently it only supports MySQL and when it is run it queries the EditableFormField class for the valid columns,
it then grabs the columns for the live database. It will create a backup of the table and then remove any columns that
are surplus.

To run the task, log in as an administrator and go to `https://example.com/dev/tasks/UserFormsColumnCleanTask` in your browser, or run `sake dev/tasks/UserFormsColumnCleanTask` from the command line.

## My CSV export times out or runs out of memory

You likely have too many submissions to fit within the PHP constraints
on your server (execution time and memory). If you can't increase these limits,
consider installing the [gridfieldqueuedexport](https://github.com/silverstripe/silverstripe-gridfieldqueuedexport) module. It uses [queuedjobs](https://github.com/symbiote/silverstripe-queuedjobs) to export
submissions in the background, providing users with a progress indicator.
