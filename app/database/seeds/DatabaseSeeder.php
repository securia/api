<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		\Illuminate\Database\Eloquant\Model::unguard();

		// $this->call('UserTableSeeder');
	}

}
