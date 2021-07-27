<?php

namespace Drupal\docmagic_migrate\Mock;

class PhpDatabaseContentMock
{
    public static function db_query($query, /*array*/ $args = array(), /*array*/ $options = array())
    {
        $query = static::adjustPhpDatabaseContentQuery($query);

        // change row ID input
        if (!is_array($args)) {
            $value = $args;
            if (1 === $value && $query === 'SELECT SQL_CALC_FOUND_ROWS tid, name FROM {taxonomy_term_field_data} WHERE vid = ?') {
                $value = 'main';
            }
            $args = array();
            $args[] = $value;
        }

        if (!$args) {
            $args = array();
        }
        if (!$options) {
            $options = array();
        }

        // Drupal 8 core/includes/database.inc
        return \db_query($query, $args, $options);
    }

    public static function db_query_range($query, $from, $count, /*array*/ $args = array(), /*array*/ $options = array())
    {
        $query = static::adjustPhpDatabaseContentQuery($query);

        if (!$args) {
            $args = array();
        }
        if (!$options) {
            $options = array();
        }

        // Drupal 8 core/includes/database.inc
        return \db_query_range($query, $from, $count, $args, $options);
    }

    public static function adjustPhpDatabaseContentQuery($query)
    {
        // change placeholders
        // from Drupal 6 includes/database.mysql-common.inc
        // *   Valid %-modifiers are: %s, %d, %f, %b (binary data, do not enclose
        // *   in '') and %%.
        $query = str_replace(array("'%s'", '%s'), '?', $query);
        $query = str_replace('%d', '?', $query);

        $migrateTableNames = array(
            'node_revisions' => 'node_revision',
            'term_data' => 'taxonomy_term_field_data',
            'term_node' => 'taxonomy_index',
            'term_hierarchy' => 'taxonomy_term__parent',
        );
        $query = str_replace(array_keys($migrateTableNames), array_values($migrateTableNames), $query);

        if (false !== strpos($query, 'n.title')) {
            $query = str_replace('{node}', '{node_field_data}', $query);
            $query = str_replace('FROM node n', 'FROM node_field_data n', $query);
        }
        if (false !== strpos($query, 'node.title')) {
            $query = str_replace('FROM node INNER JOIN', 'FROM node_field_data node INNER JOIN', $query);
        }
        if (false !== strpos($query, 'nr.body')) {
            $query = str_replace('{node_revision}', '{node__body}', $query);
            $query = str_replace('nr.body', 'nr.body_value AS body', $query);
            $query = str_replace(' and nr.vid = n.vid', ' and nr.entity_id = n.nid', $query);
        }
        if (false !== strpos($query, 'tn.vid')) {
            $query = str_replace(', tn.vid', '', $query);
            $query = str_replace(' and n.vid = tn.vid', '', $query);
        }
        if (false !== strpos($query, 'ct.field_question_value')) {
            $query = str_replace('content_type_compliance', 'node__field_question', $query);
            $query = str_replace(' and n.vid = ct.vid', ' and n.nid = ct.entity_id', $query);
        }
        if (false !== strpos($query, 'th.tid')) {
            $query = str_replace(', th.tid', ', th.entity_id', $query);
            $query = str_replace(' and th.tid = tn.tid', ' and th.entity_id = tn.tid', $query);
            $query = str_replace('ON th.tid = t.tid', 'ON th.entity_id = t.tid', $query);
            $query = str_replace(', th.parent', ', th.parent_target_id', $query);
            $query = str_replace(' and th.parent = ', ' and th.parent_target_id = ', $query);
            $query = str_replace(' = th.parent', ' = th.parent_target_id', $query);
        }
        if (false !== strpos($query, 'd.tid')) {
            $vocabularies = array(
                1 => 'main',
            );
            foreach ($vocabularies as $oldVocabularyId => $vocabulary) {
                $query = str_replace(' and vid=\''.$oldVocabularyId.'\'', ' and vid=\''.$vocabulary.'\'', $query);
                $query = str_replace(' and td.vid=\''.$oldVocabularyId.'\'', ' and td.vid=\''.$vocabulary.'\'', $query);
            }
        }
        if (false !== strpos($query, 'DELETE') && false !== strpos($query, '{cache')) {
            $query = str_replace('{cache}', '{cache_menu}', $query);
            $query = str_replace('{cache_content}', '{cache_menu}', $query);
            $query = str_replace('{cache_filter}', '{cache_menu}', $query);
            $query = str_replace('{cache_menu}', '{cache_menu}', $query);
            $query = str_replace('{cache_page}', '{cache_menu}', $query);
            $query = str_replace('{cache_views}', '{cache_menu}', $query);
        }

        return $query;
    }

    public static function node_load($nid = null, $reset = false)
    {
        if (is_array($nid)) {
            // use the first value
            $nid = reset($nid);
        }

        // Drupal 8 core/modules/node/node.module
        return \node_load($nid, $reset);
    }

    public static function php_eval($code, $namespace = null)
    {
        $raw = $code;
        $code = static::adjustPhpDatabaseContent($code);

        if ($namespace) {
            // supplying a namespace allows functions to be overridden
            $code = '<?php namespace '.$namespace.'; ?>'.$code;
        }

        static::debugPhpCode($raw, $code);

        // @see \Drupal\docmagic_migrate\EventSubscriber\MigrateSubscriber
        if (\function_exists('\php_eval') && !defined('SKIP_MIGRATION_PHP_EVAL')) {
            // from Drupal 8 modules/contrib/php/php.module
            return \php_eval($code);
        }
    }

    public static function mock_eval($code, $namespace = null)
    {
        $raw = $code;
        $code = static::adjustPhpDatabaseContent($code);

        if ($namespace) {
            // supplying a namespace allows functions to be overridden
            if (false === strpos($code, '<?php')) {
                $code = 'namespace '.$namespace.'; '.$code;
            } else {
                $code = '<?php namespace '.$namespace.'; ?>'.$code;
            }
        }

        static::debugPhpCode($raw, $code);

        return $code;
    }

    public static function debugPhpCode($raw, $code)
    {
        $dir = __DIR__.'/../../../../../sites/default/files/php_code/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents($dir.md5($raw).'.txt', '--- RAW ---'."\n".$raw."\n".'--- ADJUSTED ---'."\n".$code."\n".'--- END ---');
    }

    public static function adjustPhpDatabaseContent($code)
    {
        // replace short tags
        $code = preg_replace('`<\?([\s\r\n]|$)`', '<?php\1', $code);
        $code = str_replace('<?=', '<?php echo ', $code);

        if (false !== strpos($code, '$disclosureState = $_GET[\'state\'];')) {
            if (!isset($_GET['state']) || !$_GET['state']) {
                $code = '<?php $_GET[\'state\'] = \'\'; ?>'.$code;
            }
            if (preg_match('`</h[\d]>$`', trim($code))) {
                // missing closing bracket on if statement in a summary field
                $code .= '<?php } ?>';
            }
        }

        if (false !== strpos($code, '$pathvars = explode(\'/\', $_GET[\'q\']);')) {
            if (!isset($_GET['q']) || !$_GET['q']) {
                /*
                $code = '<?php $_GET[\'q\'] = \'\'; ?>'.$code;
                */
                $_GET['q'] = ltrim(\Drupal::request()->getPathInfo(), '/');
            }
        }

        if (false !== strpos($code, '$packageType = $_GET[\'package\'];')) {
            if (!isset($_GET['package']) || !$_GET['package']) {
                $code = '<?php $_GET[\'package\'] = \'\'; ?>'.$code;
            }
        }

        if (false !== strpos($code, '$packateExist = false;')) {
            $code = str_replace('$packateExist = false;', '$packageExist = false;', $code);
        }

        if (false !== strpos($code, 'jQuery.alert("")')) {
            $code = str_replace('jQuery.alert("")', '//jQuery.alert("")', $code);
        }

        if (false !== strpos($code, 'print $node_test')) {
            $code = str_replace('print $node_test', '//print $node_test', $code);
        }

        if (false !== strpos($code, 'function curPageURL()')) {
            $code = str_replace('function curPageURL()', 'if (!function_exists(\'curPageURL\')) {'."\n".'/*'.'function curPageURL()', $code);
            $code = str_replace('return $pageURL;', 'return $pageURL;'."\n".'}/*end curPageURL*/', $code);
            $code = str_replace('curPageURL();', 'function_exists(\'curPageURL\') ? curPageURL() : \'\';', $code);
            $code = str_replace('"node/"', '"/node/"', $code);
        }

        if (false !== strpos($code, 'function Year_dropdown_form()')) {
            $code = str_replace('function Year_dropdown_form()', 'if (!function_exists(\'Year_dropdown_form\')) {'."\n".'function Year_dropdown_form()', $code);
            $code = str_replace('return $form;', 'return $form;'."\n".'}/*end Year_dropdown_form*/', $code);
            $code = str_replace('Year_dropdown_form();', 'function_exists(\'Year_dropdown_form\') ? Year_dropdown_form() : \'\';', $code);
        }

        if (false !== strpos($code, 'function Company_dropdown_form()')) {
            $code = str_replace('function Company_dropdown_form()', 'if (!function_exists(\'Company_dropdown_form\')) {'."\n".'function Company_dropdown_form()', $code);
            $code = str_replace('return $form;', 'return $form;'."\n".'}/*end Company_dropdown_form*/', $code);
            $code = str_replace('Company_dropdown_form();', 'function_exists(\'Company_dropdown_form\') ? Company_dropdown_form() : \'\';', $code);
        }

        if (false !== strpos($code, 'function url_year()')) {
            $code = str_replace('function url_year()', 'if (!function_exists(\'url_year\')) {'."\n".'function url_year()', $code);
            $code = str_replace('return $current_year;', 'return $current_year;'."\n".'}/*end url_year*/', $code);
            $code = str_replace('url_year();', 'function_exists(\'url_year\') ? url_year() : \'\';', $code);
        }

        if (false !== strpos($code, '$_SERVER[\'HTTPS\']')
            || false !== strpos($code, '$_SERVER["HTTPS"]')
        ) {
            $code = '<?php $_SERVER[\'HTTPS\'] = \''.(isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 0).'\'; ?>'.$code;
        }

        if (false !== strpos($code, '$_SERVER[\'HTTP_REFERER\']')
            || false !== strpos($code, '$_SERVER["HTTP_REFERER"]')
        ) {
            $code = '<?php $_SERVER[\'HTTP_REFERER\'] = \''.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '').'\'; ?>'.$code;
        }

        if (false !== strpos($code, '$_SERVER[\'SERVER_NAME\']')
            || false !== strpos($code, '$_SERVER["SERVER_NAME"]')
        ) {
            if (!isset($_SERVER['SERVER_NAME']) || !$_SERVER['SERVER_NAME']) {
                $code = '<?php $_SERVER[\'SERVER_NAME\'] = \'\'; ?>'.$code;
            }
        }

        if (false !== strpos($code, '$_POST[\'EMail\']')) {
            $code = str_replace('$_POST[\'EMail\']', 'isset($_POST["EMail"]) ? $_POST["EMail"] : \'\'', $code);
            $code = str_replace('$_POST[\'ItemNo\']', 'isset($_POST["ItemNo"]) ? $_POST["ItemNo"] : \'\'', $code);
            $code = str_replace('$_POST[\'OrderTotal\']', 'isset($_POST["OrderTotal"]) ? $_POST["OrderTotal"] : \'\'', $code);
        }

        if (false !== strpos($code, 'exit;')) {
            $code = str_replace('exit;', '//exit;', $code);
        }

        if (false !== strpos($code, 'drupal_not_found();')) {
            $code = str_replace('drupal_not_found();', '//drupal_not_found();', $code);
        }

        if (false !== strpos($code, 'print $view->name;')) {
            $code = str_replace('print $view->name;', '//print $view->name;', $code);
        }

        return $code;
    }

    public static function curPageURL()
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }

    public static function Year_dropdown_form()
    {
        $formname="Year";
        //$years = array(2018,2017,2016,2015,2014,2013,2012,2011,2010,2009,2008,2007,2006,2005,2004,2003);
        $years = array_reverse(range(2003, date('Y')));

        //$urlyear=function_exists('url_year') ? url_year() : date('Y');
        $urlyear = self::url_year();
        $defaut_year = '/compliance/wizard/'.$urlyear;

        //Populate array with url / name
        foreach ($years as $year) {
            $options['/compliance/wizard/'.$year] = $year;
        }
        //Build dropdown select
        //If we try to build OnChange directly it gets mangled, so put in array to confuse the forms api
        $form['category'] = array(
            '#type' => 'select',
            '#name' => $formname,
            '#id' => $formname,
            '#title' => '',
            '#default_value' => $defaut_year,
            '#value' => $defaut_year,
            '#options' => $options,
            '#description' => '',
            '#multiple' => $multiple = FALSE,
            '#required' => $required = FALSE,
            '#attributes' => array('onChange' => "top.location.href=document.getElementById('$formname').options[document.getElementById('$formname').selectedIndex].value"),
        );

        return $form;
    }

    public static function Company_dropdown_form()
    {
        $formname="Year";
        //$years = array(2017,2016,2015,2014,2013,2012,2011,2010,2009,2008,2007,2006,2005,2004);
        $years = array_reverse(range(2004, date('Y')));

        //$urlyear=function_exists('url_year') ? url_year() : date('Y');
        $urlyear = self::url_year();
        $defaut_year = '/compliance/new-revised-documents/'.$urlyear;

        //Populate array with url / name
        foreach ($years as $year) {
            $options['/compliance/new-revised-documents/'.$year] = $year;
        }
        //Build dropdown select
        //If we try to build OnChange directly it gets mangled, so put in array to confuse the forms api
        $form['category'] = array(
            '#type' => 'select',
            '#name' => $formname,
            '#id' => $formname,
            '#title' => '',
            '#default_value' => $defaut_year,
            '#value' => $defaut_year,
            '#options' => $options,
            '#description' => '',
            '#multiple' => $multiple = FALSE,
            '#required' => $required = FALSE,
            '#attributes' => array('onChange' => "top.location.href=document.getElementById('$formname').options[document.getElementById('$formname').selectedIndex].value"),
        );

        return $form;
    }

    public static function url_year()
    {
        //$current_year= '2018';
        $current_year = date('Y');
        /*
        //if(!empty($_GET['q']))
        if (isset($_GET['q']) && !empty($_GET['q']))
        {
            $alias = drupal_get_path_alias($_GET['q']);
            $lines_array =explode("/", $alias);
            $current_year= end($lines_array);
            if ($current_year=='wizard')
            {
                //$current_year='2018';
                $current_year = date('Y');
            }
        }
        */

        if (\Drupal::hasRequest()) {
            $alias = \Drupal::request()->getUri();
            $lines_array = explode("/", $alias);
            $current_year = end($lines_array);
            if (!is_numeric($current_year) || $current_year > date('Y') || $current_year < 2003)
            {
                //$current_year='2018';
                $current_year = date('Y');
            }
        }

        return $current_year;
    }

    public static function register($class)
    {
        $self = \get_called_class();

        $mockedNs = array(substr($class, 0, strrpos($class, '\\')));
        if (0 < strpos($class, '\\Tests\\')) {
            $ns = str_replace('\\Tests\\', '\\', $class);
            $mockedNs[] = substr($ns, 0, strrpos($ns, '\\'));
        } elseif (0 === strpos($class, 'Tests\\')) {
            $mockedNs[] = substr($class, 6, strrpos($class, '\\') - 6);
        }
        foreach ($mockedNs as $ns) {
            if (\function_exists($ns.'\db_query')) {
                continue;
            }
            eval(<<<EOPHP
namespace $ns;

function db_query()
{
    \$ar = func_get_args();
    \$query = array_shift(\$ar);
    \$args = array_shift(\$ar);
    \$options = array_shift(\$ar);

    return \\$self::db_query(\$query, \$args, \$options);
}

function db_query_range()
{
    \$ar = func_get_args();
    \$query = array_shift(\$ar);
    \$from = array_shift(\$ar);
    \$count = array_shift(\$ar);
    \$args = array_shift(\$ar);
    \$options = array_shift(\$ar);

    return \\$self::db_query_range(\$query, \$from, \$count, \$args, \$options);
}

function node_load(\$nid, \$reset = false)
{
    return \\$self::node_load(\$nid, \$reset);
}

function php_eval(\$code)
{
    return \\$self::php_eval(\$code, '$ns');
}

function mock_eval(\$code)
{
    return \\$self::mock_eval(\$code, '$ns');
}

function curPageURL()
{
    return \\$self::curPageURL();
}

EOPHP
            );
        }
    }
}
