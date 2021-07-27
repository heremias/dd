<?php

/*

Generate Content from the old Drupal 8 site
-------------------------------------------

In the old Drupal 8 site, install the default_content module:

vendor/bin/drupal module:install default_content;

At the bottom of the old Drupal 8 site index.php file, temporarily add the following line:

require __DIR__.'/modules/custom/docmagic_migrate_content/export_snippet.php';

Then view any page in the browser to populate the content directory in the docmagic_migrate_content module.  Now remove
the line in index.php that was just added.

Import Content into the new Drupal 8 site
-----------------------------------------

With the generated data in the content directory, install the docmagic_migrate_content module to import it into the
new Drupal 8 site.

vendor/bin/drupal module:install default_content;
vendor/bin/drupal module:install docmagic_migrate_content;

*/

// importing with the "default_content" module seems to serialize, pre-render, and process the text
// so skip php_code format evaluation to avoid halting import/export operations
// @see \Drupal\docmagic_migrate\Mock\PhpDatabaseContentMock::php_eval()
define('SKIP_MIGRATION_PHP_EVAL', true);

// @see \Drupal\default_content\Commands\DefaultContentCommands::contentExportReferences()
/** @var \Drupal\default_content\Exporter $defaultContentExporter */
$defaultContentExporter = \Drupal::service('default_content.exporter');
$folder = __DIR__.'/content';
foreach (\Drupal::entityTypeManager()->getDefinitions() as $entity_type_id => $entity_type) {
    $entities = \Drupal::entityQuery($entity_type_id)->execute();
    foreach ($entities as $entity_id) {
        try {
            $serialized_by_type = $defaultContentExporter->exportContentWithReferences($entity_type_id, $entity_id);
            $defaultContentExporter->writeDefaultContent($serialized_by_type, $folder);
        } catch (\Exception $e) {
            // do nothing
        }
    }
}
