<?php

use SilverStripe\Admin\CMSMenu;
use SilverStripe\UserForms\Control\UserDefinedFormAdmin;

CMSMenu::remove_menu_class(UserDefinedFormAdmin::class);
