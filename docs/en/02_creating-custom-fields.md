---
title: Creating custom fields
---

# Creating custom fields

To create and use your own custom fields, depending on what you want to accomplish, you may need to create two
new classes subclassed from the following:

- [`EditableFormField`](api:SilverStripe\UserForms\Model\EditableFormField) - this Field represents what will be seen/used in the CMS userforms interface
- [`FormField`](api:SilverStripe\Forms\FormField) - this Field represents what will be seen/used in the frontend user form when the above field has been
added

## How (without the "why")

You need to create your own subclass of `EditableFormField` (the field which will be used in the CMS). This class needs to
implement the method `getFormField()`, which will need to return an instantiated `FormField` to be used in the
frontend form.

[`EditableTextField`](api:SilverStripe\UserForms\Model\EditableFormField\EditableTextField) and [`TextField`](api:SilverStripe\Forms\TextField) are two existing classes and probably the best example to look in to.

## Why two different fields?

Consider the following example (`EditableTextField` and `TextField`).

We have a field type that allows us to (optionally) set a minimum and maximum number of characters that can be input
into that particular field.

As an author, when I create this field in the CMS, I want the ability to specify what those `min`/`max` settings are.
As a developer, I want to be able to add validation to make sure that these `min`/`max` values are valid (EG: `min`
is less than `max`). So, this class is going to need DB fields to store these min/max values, and it's going to need
some validation for when an author fills in those fields.

As a frontend user, I want to fill in the field, and be notified when the value I have entered does not meet the
requirements. As a developer, I need to now compare the value entered by the user with the `min`/`max` values that the
author specified.

So, we have two fields, with two different concerns.

The subclass of `EditableFormField` is what you want to create to represent the field as it is used in the CMS. Its
validation should be based on what you require your **content authors** to enter.

The subclass of `FormField` is what you want to create to represent the field as it is used on the frontend. Its
validation should be based on what you require your **frontend users** to enter.

The subclass of `EditableFormField` is in charge of instantiating its `FormField` with any/all information the `FormField`
requires to perform its duty.
