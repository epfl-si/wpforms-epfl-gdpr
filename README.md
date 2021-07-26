# WPForms EPFL GDPR

[WPForms EPFL GDPR] is a [WPForms] addon that add EPFL specific functionalities
in relation to the GDPR.

_DISCLAIMER: This addon is only useful in the [EPFL] ecosystem. Therefore, any 
attempt to use it "as is" without any modification will most certainly fail. 
Consider yourself warned..._


## Features

This plugin can manage a form life cycle. When creating a new form, the user is
asked to fill the start date and the end date of the form. 
Once entered, these dates can no longer be changed. Once the end date has 
passed, the user no longer has access to the data collected by the form.


## Style Guide / WPCS

This plugin uses the [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards). Please follow installation instruction on 
https://github.com/squizlabs/PHP_CodeSniffer (and note that https://github.com/WordPress/WordPress-Coding-Standards/issues/1979#issuecomment-787464170 might be handy). Then, from project root directory, the command:
```
make lint-check
```
should run.




[WPForms EPFL GDPR]: https://github.com/epfl-si/wpforms-epfl-gdpr
[WPForms]: https://wpforms.com/
[EPFL]: https://www.epfl.ch
