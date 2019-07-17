<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Company;
use App\Models\Accountgroup;

class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        config(['site.page' => 'account']);
        $data = Account::all();
        $companies = Company::all();
        return view('admin.accounts', compact('data', 'companies'));
    }


    public function create(Request $request){
        $request->validate([
            'name'=>'required|string',
            'company'=>'required',
        ]);

        Account::create([
            'name' => $request->get('name'),
            'company_id' => $request->get('company'),
            'comment' => $request->get('comment'),
        ]);
        return back()->with('success', __('page.created_successfully'));
    }

    public function edit(Request $request){
        $request->validate([
            'name'=>'required',
            'company'=>'required',
        ]);
        $item = Account::find($request->get("id"));
        $item->name = $request->get("name");
        $item->comment = $request->get("comment");
        $item->company_id = $request->get("company");
        $item->save();
        return back()->with('success', __('page.updated_successfully'));
    }

    public function delete($id){
        $item = Account::find($id);
        $item->delete();
        return back()->with("success", __('page.deleted_successfully'));
    }

}
