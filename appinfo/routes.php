<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Ledger\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#do_echo', 'url' => '/echo', 'verb' => 'POST'],
        ['name' => 'db#getTotals', 'url' => '/gettotals', 'verb' => 'POST'],
        ['name' => 'db#getTimeline', 'url' => '/gettimeline', 'verb' => 'POST'],
        ['name' => 'db#getTransactions', 'url' => '/gettransactions', 'verb' => 'POST'],
        ['name' => 'db#addTimeline', 'url' => '/addtimeline', 'verb' => 'POST'],
        ['name' => 'db#addMember', 'url' => '/addmember', 'verb' => 'POST'],
        ['name' => 'db#editMember', 'url' => '/editMember', 'verb' => 'POST'],
    ]
];
