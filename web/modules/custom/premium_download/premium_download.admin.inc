<?php
// $Id: premium_download.admin.inc,v 1.1.2.87 2010/12/05 21:14:15 davereid Exp $

/**
 * @file
 * Administrative page callbacks for the premium_download module.
 */

function premium_download_list_files($query = array(), $conditions = array(), $tableselect = FALSE) {
  // Initialize the query array.
  $query += array(
    'conditions' => array(),
    'args' => array(),
    'limit' => 0,
  );

  // Check if this will be a tableselect element.
  $tableselect &= \Drupal::currentUser()->hasPermission('access premium download admin') && \Drupal::moduleHandler()->moduleExists('elements');

  // Set up the header.
  $header = array(
    'filepath' => array('data' => t('File Path'), 'field' => 'filepath', 'sort' => 'asc'),
    'permission' => array('data' => t('Permission'), 'field' => 'permission'),
    'operations' => array('data' => t('Operations'))
  );

  // Remove any columns that are present in conditions.
  _premium_download_build_conditions($query, NULL, $conditions);
  foreach ($conditions as $field => $value) {
    unset($header[$field]);
  }

  // Build the SQL query.
  $sql = 'SELECT pid FROM {premium_download}';
  if ($query['conditions']) {
    $sql .= ' WHERE (' . implode(') AND (', $query['conditions']). ')';
  }
//  $sql .= tablesort_sql($header);
//  if ($query['limit']) {
//    $query = pager_query($sql, $query['limit'], 0, NULL, $query['args']);
//  }
//  else {
    $query = db_query($sql, $query['args']);
//  }

  // Load the redirects.
  $pids = array();
  while ($pid = db_result($query)) {
    $pids[] = $pid;
  }
  $premium_downloads = premium_download_load_multiple($pids);

  $destination = drupal_get_destination();
  $rows = array();
  $weight = 0;
  foreach ($pids as $pid) {
    $premium_download = $premium_downloads[$pid];
    $row = array();
    if (isset($header['filepath'])) {
      $filepath_url = $premium_download['filepath'];
      $row['filepath'] = \Drupal\Core\Link::fromTextAndUrl($filepath_url, \Drupal\Core\Url::fromUserInput('/'.ltrim($premium_download['filepath'], '/'), array(/*'query' => $premium_download['filepath_query'],*/ 'alias' => TRUE)))->toString();

    }
    if (isset($header['permission'])) {
      $row['permission'] = $premium_download['permission'];
    }
    if (isset($header['operations'])) {
      $operations = array();
      $operations['edit'] = array(
        'title' => t('Edit'),
        'href' => 'admin/build/premium-download/edit/' . $pid,
        'query' => $destination,
      );
      $operations['delete'] = array(
        'title' => t('Delete'),
        'href' => 'admin/build/premium-download/delete/' . $pid,
        'query' => $destination,
      );
      $operations['edit']['url'] = \Drupal\Core\Url::fromUserInput('/'.ltrim($operations['edit']['href'], '/'));
      $operations['delete']['url'] = \Drupal\Core\Url::fromUserInput('/'.ltrim($operations['delete']['href'], '/'));
      $row['operations'] = \Drupal::theme()->render('links', array('links' => $operations, 'attributes' => array('class' => 'links inline nowrap')));

    }
    $rows[(string) $pid] = $row;
  }

  if ($tableselect) {
    return array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $rows,
      '#empty' => t('No premium download available.'),
    );
  }
  else {
    if (empty($rows)) {
      $rows[] = array(array(
        'data' => t('No premium download available.'),
        'colspan' => count($header),
        'class' => 'empty',
      ));
    }
    return array(
      //'#type' => 'markup',
      //'#value' => \Drupal::theme()->render('table', array('header' => $header, 'rows' => $rows)),
      '#markup' => \Drupal::theme()->render('table', array('header' => $header, 'rows' => $rows)),
    );

  }
}

/**
 * Render a list of premium donwload for the main admin page.
 */
function premium_download_admin_file(&$form_state) {
  if (isset($form_state['values']['operation']) && empty($form_state['values']['confirm'])) {
    return premium_download_admin_file_update_confirm($form_state, $form_state['values']['operation'], array_filter($form_state['values']['pids']));
  }

  // Get filter key.
  $keys = func_get_args();
  array_shift($keys); // Offset the $form_state parameter.
  $keys = implode('/', $keys);

  // Add the local actions and filter form.
  $form['actions'] = array(
    '#type' => 'markup',
    '#value' => premium_download_local_actions(),
  );
  $form['filter'] = premium_download_filter_form($keys);

  // Build the 'Update options' form.
  $form['options'] = array(
    '#type' => 'fieldset',
    '#title' => t('Update options'),
    '#prefix' => '<div class="container-inline">',
    '#suffix' => '</div>',
    '#access' => \Drupal::currentUser()->hasPermission('access premium download admin') && \Drupal::moduleHandler()->moduleExists('elements'),
  );
  $options = array();
  foreach (\Drupal::moduleHandler()->invokeAll('premium_download_operations') as $key => $operation) {
    $options[$key] = $operation['action'];
  }
  $form['options']['operation'] = array(
    '#type' => 'select',
    '#options' => $options,
    '#default_value' => 'delete',
  );
  $form['options']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Update'),
    '#validate' => array('premium_download_admin_file_update_validate'),
    '#submit' => array('premium_download_admin_file_update_submit'),
  );

  // Apply the filter conditions.
  $query = array('conditions' => array(), 'args' => array(), 'limit' => 50);
  premium_download_filter_query($query, $keys);

  $form['pids'] = premium_download_list_files($query, array(), TRUE);
  $form['pager'] = array(
    '#type' => 'markup',
    '#value' => \Drupal::theme()->render('pager', NULL, 50, 0),
  );

  return $form;
}

function premium_download_filter_query(&$query, $keys = '') {
  if ($keys) {
    // Replace wildcards with MySQL/PostgreSQL wildcards.
    $wildcard = preg_replace('!\*+!', '%', $keys);
    $query['conditions'][] = "(filepath LIKE '%%%s%%')";
    $query['args'] = array_merge($query['args'], array($wildcard));
  }
}

/**
 * Return a form to filter URL redirects.
 *
 * @ingroup forms
 * @see premium_download_filter_form_submit()
 */
function premium_download_filter_form($keys = '') {
  $form['#attributes'] = array('class' => 'search-form');
  $form['basic'] = array(
    '#type' => 'fieldset',
    '#title' => t('Filter redirects'),
  );
  $form['basic']['inline'] = array(
    '#prefix' => '<div class="container-inline">',
    '#suffix' => '</div>',
  );
  $form['basic']['inline']['filter'] = array(
    '#type' => 'textfield',
    '#title' => '',
    '#default_value' => $keys,
    '#maxlength' => 128,
    '#size' => 25,
  );
  $form['basic']['inline']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Filter'),
    '#submit' => array('premium_download_filter_form_submit'),
  );
  if ($keys) {
    $form['basic']['inline']['reset'] = array(
      '#type' => 'submit',
      '#value' => t('Reset'),
      '#submit' => array('premium_download_filter_form_reset'),
    );
  }
  return $form;
}

/**
 * Process filter form submission when the Filter button is pressed.
 */
function premium_download_filter_form_submit($form, &$form_state) {
  $form_state['redirect'] = 'admin/build/premium-download/list/'. trim($form_state['values']['filter']);
}

/**
 * Process filter form submission when the Reset button is pressed.
 */
function premium_download_filter_form_reset($form, &$form_state) {
  $form_state['redirect'] = 'admin/build/premium-download';
}

/**
 * Validate node_admin_nodes form submissions.
 *
 * Check if any nodes have been selected to perform the chosen
 * 'Update option' on.
 */
function premium_download_admin_file_update_validate($form, &$form_state) {
  // Error if there are no items to select.
  if (!is_array($form_state['values']['pids']) || !count(array_filter($form_state['values']['pids']))) {
//    form_set_error('', t('No redirects selected.'));
  }
  
}

/**
 * Process premium_download_admin_file form submissions.
 *
 * Execute the chosen 'Update option' on the selected redirects.
 */
function premium_download_admin_file_update_submit($form, &$form_state) {
  $operations = \Drupal::moduleHandler()->invokeAll('premium_download_operations');
  $operation = $operations[$form_state['values']['operation']];

  // Filter out unchecked redirects
  $pids = array_filter($form_state['values']['pids']);

  if (!empty($operation['confirm']) && empty($form_state['values']['confirm'])) {
    // We need to rebuild the form to go to a second step. For example, to
    // show the confirmation form for the deletion of redirects.
    $form_state['rebuild'] = TRUE;
  }
  else {
    $function = $operation['callback'];

    // Add in callback arguments if present.
    if (isset($operation['callback arguments'])) {
      $args = array_merge(array($pids), $operation['callback arguments']);
    }
    else {
      $args = array($pids);
    }
    call_user_func_array($function, $args);
    premium_download_clear_cache();

    $count = count($form_state['values']['pids']);
    \Drupal::logger('premium_download')->notice('@action @count redirects.', array('@action' => $operation['action_past'], '@count' => $count));
    drupal_set_message(\Drupal::translation()->formatPlural(count($pids), '@action @count redirect.', '@action @count redirects.', array('@action' => $operation['action_past'], '@count' => $count)));
    //$form_state['redirect'] = 'admin/config/search/premium-download';
  }
}

function premium_download_admin_file_update_confirm(&$form_state, $operation, $pids) {
  $operations = \Drupal::moduleHandler()->invokeAll('premium_download_operations');
  $operation = $operations[$form_state['values']['operation']];

  $form['pids'] = array('#prefix' => '<ul>', '#suffix' => '</ul>', '#tree' => TRUE);
  $premium_downloads = premium_download_load_multiple($pids);
  foreach ($pids as $pid) {
    $premium_download = $premium_downloads[$pid];
    $form['pids'][$pid] = array(
      '#type' => 'hidden',
      '#value' => $pid,
      '#prefix' => '<li>',
      '#suffix' => $premium_download['filepath'] . "</li>\n"
    );
  }
  $form['operation'] = array('#type' => 'hidden', '#value' => $form_state['values']['operation']);
  $form['#submit'][] = 'premium_download_admin_file_update_submit';
  $confirm_question = \Drupal::translation()->formatPlural(count($pids), 'Are you sure you want to @action this redirect?', 'Are you sure you want to @action these redirects?', array('@action' => \Drupal\Component\Utility\Unicode::strtolower($operation['action'])));

//  return confirm_form(
//    $form,
//    $confirm_question,
//    'admin/config/search/premium-download', // @todo This does not redirect back to filtered page.
//    t('This action cannot be undone.'),
//    $operation['action'],
//    t('Cancel')
//  );
}

function premium_download_edit_form(&$form_state, $premium_download = array()) {
  // Merge default values.
  $premium_download += array(
    'pid' => NULL,
    'filepath' => isset($_GET['filepath']) ? urldecode($_GET['filepath']) : '',
    'permission' => \Drupal::config('premium_download.settings')->get('premium_download_default_status'),
  );
  $form['pid'] = array(
    '#type' => 'value',
    '#value' => $premium_download['pid'],
  );
  $form['filepath'] = array(
    '#type' => 'textfield',
    '#title' => t('From'),
    '#description' => t("Enter file path to PDFs (e.g. %example1 or %example2).", array('%example1' => 'media/docmagic/compliance/confidentiality.pdf', '%example2' => 'media/docmagic/compliance/compliance09/audit-rpt-2.pdf')),
    '#size' => 42,
    '#maxlength' => 255,
    '#default_value' => $premium_download['filepath'],
    '#required' => TRUE,
    '#element_validate' => array('premium_download_validate_filepath_field'),
  );

  $form['permission'] = array(
    '#type' => 'select',
    '#title' => t('Select the '),
    '#description' => t('You can find more information about HTTP redirect status codes at <a href="@status-codes">@status-codes</a>.', array('@status-codes' => 'http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection')),
    '#default_value' => $premium_download['permission'],
    '#options' => premium_download_permission_type_options(),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  $form['cancel'] = array(
    '#value' => \Drupal\Core\Link::fromTextAndUrl(t('Cancel'), \Drupal\Core\Url::fromUserInput(isset($_REQUEST['destination']) ? $_REQUEST['destination'] : '/admin/build/premium-download'))->toString(),
  );


  return $form;
}


/**
 * Validate a redirect's filepath path (from) field.
 */
function premium_download_validate_filepath_field($element, &$form_state) {
  // Check that the path contains no URL fragment.
  if (strpos($element['#value'], '#') !== FALSE) {
//    form_error($element, t('The filepath path cannot contain an URL fragment anchor.'));
  }
  if(strrchr(strtoupper($element['#value']),'.') != '.PDF')
  {
//      form_error($element, t('The filepath path must be pdf.'));
  }
  return $element;
}


function premium_download_edit_form_validate($form, &$form_state) {
  $premium_download = &$form_state['values'];
  if(strrchr(strtoupper($premium_download['filepath']),'.') != '.PDF')
  {
//      form_set_error('filepath', t('The file path needs to be PDF'));
  }
  if ($existing = premium_download_load_by_filepath($premium_download['filepath'])) {
    if ($premium_download['pid'] != $existing->pid) {
      // The "from" path should not conflict with another redirect
//      form_set_error('filepath', t('The filepath path %filepath  already exist. Do you want to <a href="@edit-page">edit the existing premium download</a>?', array('%filepath' => $premium_download['filepath'], '@edit-page' => \Drupal\Core\Url::fromUserInput('/admin/build/premium-download/edit/'. $existing->pid)->toString())));

    }
  }
}

function premium_download_edit_form_submit($form, &$form_state) {
  premium_download_save($form_state['values']);
  drupal_set_message(t('The redirect has been saved.'));
  $form_state['redirect'] = 'admin/build/premium-download';
}

function premium_download_delete_form($form_state, $premium_download) {
  $form['premium_download'] = array(
    '#type' => 'value',
    '#value' => $premium_download,
  );
//  return confirm_form(
//    $form,
//    t('Are you sure you want to delete the premium download for %filepath?', array('%filepath' => $premium_download['filepath'])),
//    isset($_REQUEST['destination']) ? $_REQUEST['destination'] : 'admin/build/premium-download'
//  );
}

function premium_download_delete_form_submit($form, &$form_state) {
  premium_download_delete($form_state['values']['premium_download']['pid']);
  drupal_set_message(t('The premium download has been deleted.'));
  $form_state['redirect'] = 'admin/build/premium-download';
}


function premium_download_permission_type_options() {
  return array(
    'trial download' => t('trial download'),
   'compliance download' => t('compliance download')
  );
}

?>
