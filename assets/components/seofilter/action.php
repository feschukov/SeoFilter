<?php
header('Content-Type: application/json; charset=UTF-8');
define('MODX_API_MODE', true);
//require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php'; //TODO: удалить после переноса

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
// Switch context if need
if (!empty($_REQUEST['pageId'])) {
    if ($resource = $modx->getObject('modResource', (int)$_REQUEST['pageId'])) {
        if ($resource->get('context_key') != 'web') {
            $modx->switchContext($resource->get('context_key'));
        }
        $modx->resource = $resource;
    }
}
/** @var SeoFilter $SeoFilter */
$SeoFilter = $modx->getService('seofilter', 'SeoFilter', $modx->getOption('seofilter_core_path', null,
        $modx->getOption('core_path') . 'components/seofilter/') . 'model/seofilter/', array());

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    $modx->sendRedirect($modx->makeUrl($modx->getOption('site_start'), '', '', 'full'));
} elseif (empty($_REQUEST['sf_action'])) {
    echo $SeoFilter->error('sf_err_action_ns');
} else {
    echo $SeoFilter->process($_REQUEST['sf_action'], $_REQUEST);
}
@session_write_close();