---
title: Creating forms in the CMS
summary: How to use the UserForms module to create forms via the CMS.
---

# Creating forms in the CMS

## Before we begin

Make sure that your Silverstripe CMS installation has the [UserForms](https://addons.silverstripe.org/add-ons/silverstripe/userforms/) module installed.

## Data protection and privacy

> [!Important]
> This feature allows authors with CMS permissions to create forms which process submission data,
> and store data the CMS database by default. Anyone with the ability to create forms
> also has access to view and export submissions. As the owner and operator of your website,
> you should ensure processes and safeguards are in place to perform these actions securely.
>
> This is your responsibility

Here are a few tips to get you started:

- Ensure you have the necessary consents for processing and storing data according to your legislation (e.g. GDPR)
- Only accept form submissions via encrypted transfers (HTTPS) - check our [Secure Coding](https://docs.silverstripe.org/en/developer_guides/security/secure_coding/) guidelines
- Control access to form submissions (via CMS page access controls)
- Control access to files uploaded with submissions (via [folder access controls](field-types.md#file-upload-field))
- Create a process to limit the types of data you are allowed to collect via this feature (e.g. no payment information or health data)
- Create a process for limiting submission storage duration (manual deletion)
- Consider further safeguards such as at-rest encryption (check [encryption related addons](https://addons.silverstripe.org/add-ons?search=encrypt))

## Features

- [Create and edit forms](creating-and-editing-forms.md)
- [Add different field types to a form](field-types.md)
- [Set up multipage forms](multipage-forms.md)
- [View submissions and set up automated emails upon form completion](form-submissions.md)
