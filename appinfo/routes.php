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
        ['name' => 'ledger#getTotals', 'url' => '/gettotals', 'verb' => 'POST'],
        ['name' => 'ledger#getTransactions', 'url' => '/gettransactions', 'verb' => 'POST'],
        ['name' => 'ledger#addMember', 'url' => '/addmember', 'verb' => 'POST'],
        ['name' => 'ledger#editMember', 'url' => '/editmember', 'verb' => 'POST'],
        ['name' => 'ledger#deleteMember', 'url' => '/deletemember', 'verb' => 'POST'],
        ['name' => 'timeline#editTimeline', 'url' => '/edittimeline', 'verb' => 'POST'],
        ['name' => 'timeline#deleteTimeline', 'url' => '/deletetimeline', 'verb' => 'POST'],
        ['name' => 'timeline#getTimeline', 'url' => '/gettimeline', 'verb' => 'POST'],
        ['name' => 'timeline#addTimeline', 'url' => '/addtimeline', 'verb' => 'POST'],
        ['name' => 'ledger#addTransaction', 'url' => '/addtransaction', 'verb' => 'POST'],
    ]
];
