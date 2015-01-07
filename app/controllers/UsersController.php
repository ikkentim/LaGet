<?php

class UsersController extends Controller
{

    /**
     * Display a listing of the resource.
     * GET /users
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     * GET /users/create
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * POST /users
     *
     * @return Response
     */
    public function store()
    {
        $validator = Validator::make(
            Input::all(),
            [
                'password' => 'required|min:6',
                'email' => 'required|email|unique:users'
            ]
        );

        if($validator->fails()){
            return Response::json(['errors' => $validator->messages()], 409);
        }
        $newUser = User::create([
            'email' => Input::get('email'),
            'password' => Hash::make(Input::get('password')),
            'apikey' => md5(Input::get('email') . time())
        ]);

        if ($newUser) {
            Auth::login($newUser);
            return Response::json($newUser->toArray());
        }

        return Response::json(['error' => 'email already in use.'], 409);
    }

    /**
     * Display the specified resource.
     * GET /users/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * GET /users/{id}/edit
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * PUT /users/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /users/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}