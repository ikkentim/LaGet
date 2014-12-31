<?php

class HomeController extends Controller {

	public function home()
	{
		return View::make('home/home');
	}

}
