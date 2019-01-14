## INTRODUCTION

The `pagetype` module provides a "node like" page entity type. This entity type is fieldable, and bundles can be managed via the `Structure->Page Types` admin menu.

The page entity works much the same as the node entity, in that pages are given a default visible path (pages/{page-id} by default) and can be fully customized with their own set of fields. The core different between Nodes and Pages is that Pages, by default, have *no continuity*.

This means that pages are more suited for one-off, custom layouts with unique field needs. This isn't to say that pages can't be given continuity, but it's an opt-in feature.


## REQUIREMENTS

Pagetype requires or integrates with the following modules:

 - Drupal Core `7.x`
 - `path` module
 - `token` module


## RECOMMENDED MODULES

 - [`pathauto`](https://www.drupal.org/project/pathauto)


## INSTALLATION

 - Download the pagetype module to your modules directory and add the `tbpixel/pagetype` module as a [repository](https://getcomposer.org/doc/05-repositories.md#path) in your composer.json file.
 - Install as you would normally install a contributed Drupal module. Visit:
   [Installing modules](https://drupal.org/documentation/install/modules-themes/modules-7)
   for further information.


## CONFIGURATION

  - Configure user permissions in Administration » People » Permissions:

    - Administer pagetype page entities

     All pagetype administrative actions fall under the administer pagetype page entities.

  - Pathauto integration:

    - Pathauto is finicky to integrate with a new entity type. Visit `/admin/config/search/path/patterns`, scroll down to the Page paths section, cut the default path and save the configuration. Then, paste the path setting back into the same field and save again. This will allow pathauto to generate page URL's for you automatically.



## MAINTAINERS

Current maintainers:

 - Tony Barry (TBPixel) - [Drupal.org profile](https://www.drupal.org/u/tbpixel) - [GitHub](https://github.com/TBPixel)
