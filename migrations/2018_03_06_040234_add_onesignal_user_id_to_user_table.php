<?php

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Builder;

return [ 
	'up' => function(Builder $schema) {
		$schema->table('users', function($table) {
			$table->string('onesignal_user_id');
		});
	},
	'down' => function(Builder $schema) {
		$schema->table('users', function($table) {
			$table->dropColumn('onesignal_user_id');
		});
	}
];
