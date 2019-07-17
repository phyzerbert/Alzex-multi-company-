<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        config(['site.page' => 'company']);
        $data = Company::all();
        return view('admin.companies', compact('data'));
    }

    public function edit(Request $request){
        $request->validate([
            'name'=>'required',
        ]);
        $data = $request->all();
        $item = Company::find($request->get("id"));
        $item->update($data);
        return back()->with('success', __('page.updated_successfully'));
    }

    public function create(Request $request){
        $request->validate([
            'name'=>'required|string',
        ]);
        $data = $request->all();
        Company::create($data);
        return back()->with('success', __('page.created_successfully'));
    }

    public function delete($id){
        $user = Company::find($id);
        $user->delete();
        return back()->with("success", __('page.deleted_successfully'));
    }

}
