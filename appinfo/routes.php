<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\GPodderSync\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'routes' => [
		['name' => 'episode_action#create', 'url' => '/episode_action/create', 'verb' => 'POST'],
		['name' => 'episode_action#list', 'url' => '/episode_action', 'verb' => 'GET'],

		['name' => 'subscription_change#list', 'url' => '/subscriptions', 'verb' => 'GET'],
		['name' => 'subscription_change#create', 'url' => '/subscription_change/create', 'verb' => 'POST'],

		['name' => 'version#index', 'url' => '/', 'verb' => 'GET'],



	]
];
