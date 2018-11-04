<?php

	namespace App\Providers;

	use App\User;
	use Illuminate\Support\ServiceProvider;
	use Illuminate\Auth\GenericUser;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Validator;



	class AuthServiceProvider extends ServiceProvider{
		/**
		 * Register any application services.
		 *
		 * @return void
		 */
		public function register(){
			//
		}

		public $request;

		/**
		 * Boot the authentication services for the application.
		 *
		 * @return void
		 */
		public function boot(){
			$this->app['auth']->viaRequest('api', function($request){
				$validator = Validator::make($request->all(), [
					'username' => 'required',
					'token' => 'required|size:512'
				])->validate();

				$users = DB::connection('mysql.write')
						   ->table('users')
						   ->where('username','=',$request->input('username'))
						   ->where('username_hash','=',sha1($request->input('username')));

				$count = $users->count();

				$user = $users->first();

				if($count === 1){
					$tokens = DB::connection('mysql.write')
								->table('auth_tokens')
								->where('UID','=',$user->id)
								->where('token','=',$request->input('token'));
					if($tokens->count() === 1){
						return new User((array) $user);
					}
					else{
						$this->addMessage('error','Token doesnt exists.');
						return $this->getResponse();
					}
				}
				else{
					$this->addMessage('error','User doesnt exists.');
					return $this->getResponse();
				}

			});
		}
	}
