# User Documentation

Instructions on how to create, use, and maintain user forms in the CMS.

## Setting up a User Form

1. Create a page in the CMS of type 'User Defined Form'
2. Go to the 'Form' tab and select the fields you'd like to have displayed on your form.
  1. For each field, select a type from the dropdown and press 'Add'
  2. In the empty text box type out the label for the field.
  3. Expand the "Show Options" to show additional options. Notes on options for each field can be found below.
  4. Checking the "Is this field Required?" checkbox to make this field mandatory, and optionally set a custom
     error message.
3. Set the thank you message under the 'Configuration' tab. This will be displayed to the user when they
   successfully complete the form.
4. Setup recipients by clicking on the 'Configuration' tab.
  1. Press the "Add Email Recipient" button and fill out the fields.
  2. Setup the "Email subject" that the user would receive in their email.
  3. Set the "Send email from" as an email address which exists on the same domain as your site.
     E.g. if your site is www.example.com you would use contact@example.com.
  4. The "Email for reply to" and "Send emails to" email address fields can either by typed out, or you can
     select a form field to draw the value for that field from.
  5. Alternatively, if submissions should be stored on the server only but not emailed, it is not necessary to
     add any recipients, but ensure that the "Disable Saving Submissions" to server is unchecked. These can be
     accessed or downloaded in CSV format on the "Submissions" tab.

## Field Types

### Checkbox Field

A basic check (boolean) field to store a true or false value.

Marking this field as required will require it to be checked.

### Checkbox Group

Enables a set of options to be displayed, grouped together under a common title.

Once this field has been added you can add each of the sub-options by clicking "Show Options"
and then "Add Option". Each sub-option can only be assigned a single string value.

Marking this field as required will require at least one option to be checked.

### Country Dropdown

A list of all countries drawn from the internal list of known countries.

### Date Field

A date entry field. This does not include time.

If your theme enables it, a date picker popup will be displayed to the user on the frontend.

### Dropdown Field

A dropdown list field.

Once this field has been added you can add each of the options by clicking "Show Options"
and then "Add Option". Each sub-option can only be assigned a single string value.

### Email Field

A text field with email address validation.

### File Upload Field

Enables the user to attach a file to their submission.

The folder that this field uploads to can be customised by selecting "Show Options"
and then selecting a new folder from the "Select upload folder" option. If no folder
is selected it will upload by default to the "Uploads" folder.

### Heading Field

This inserts a fixed heading into your form, and is not a field editable by the user.

Once this field has been added you can select a heading level (1 to 6) by clicking
"Show Options" and using the "Select Heading Level" field.

If you do not check the "Hide from reports" checkbox then this field will be displayed
in submission reports.

### HTML Block

This inserts a fixed block of HTML into your form, and is not a field editable by the user.

Once this field has been added you can change the content of the HTML by clicking
"Show Options" and entering your content into the "HTML" field.

If you do not check the "Hide from reports" checkbox then this field will be displayed
in submission reports.

Note: Take care not to allow input from unauthorised sources or users, as custom script
or code could be injected into your form.

### Member List Field

This displays a dropdown list containing all users that belong to the specified group.

To set the group to display, after adding this field to your form, click "Show Options"
and select the source group under the "Group" field.

Note: Take care that you do not expose confidential or personal information about your CMS
or front end users as these names will become publicly visible.

### Numeric Field

A basic text field that will only accept numeric values (numbers and decimals only).

### Radio Field

A list of options, similar to the Checkbox Set Field, but one which allows only a single value to
be selected from a list.

Once this field has been added you can add each of the sub-options by clicking "Show Options"
and then "Add Option". Each sub-option can only be assigned a single string value.

### Text Field

A basic text field.
