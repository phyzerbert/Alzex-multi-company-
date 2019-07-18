<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Company;
use App\User;

use Auth;

class CategoryController extends Controller
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
    public function index(Request $request)
    {
        config(['site.page' => 'category']);
        $user = Auth::user();
        $companies = Company::all();
        $users = User::where('role_id', 2)->get();
        $mod = new Category();
        if($user->hasRole('user')){
            $mod = $user->categories();
            $users = $user->company->users;
        }
        $name = $company_id = $user_id = $comment = '';

        if($request->get('name') != ''){
            $name = $request->get('name');
            $mod = $mod->where('name', 'LIKE', "%$name%");
        }

        if($request->get('comment') != ''){
            $comment = $request->get('comment');
            $mod = $mod->where('comment', 'LIKE', "%$comment%");
        }

        if($request->get('user_id') != ''){
            $user_id = $request->get('user_id');
            $mod = $mod->where('user_id', $user_id);
        }

        if($request->get('company_id') != ''){
            $company_id = $request->get('company_id');
            $company_users = User::where('company_id', $company_id)->pluck('id');
            $mod = $mod->whereIn('user_id', $company_users);
        }

        $data = $mod->orderBy('created_at', 'desc')->paginate(15);

        return view('categories', compact('data', 'users', 'companies', 'name', 'comment', 'user_id', 'company_id'));
    }

    public function edit(Request $request){
        $request->validate([
            'name'=>'required',
        ]);
        $item = Category::find($request->get("id"));
        $item->name = $request->get("name");
        $item->comment = $request->get("comment");
        $item->save();
        return back()->with('success', 'Updated Successfully');
    }

    public function create(Request $request){
        $request->validate([
            'name'=>'required|string',
        ]);

        Category::create([
            'name' => $request->get('name'),
            'user_id' => Auth::user()->id,
            'comment' => $request->get('comment'),
        ]);
        return back()->with('success', 'Created Successfully');
    }

    public function delete($id){
        $user = Category::find($id);
        $user->delete();
        return back()->with("success", "Deleted Successfully");
    }

}
