<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

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
    public function index()
    {
        config(['site.page' => 'category']);
        $user = Auth::user();
        $data = Category::all();
        if($user->hasRole('user')){
            $data = $user->categories;
        }
        return view('categories', compact('data'));
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
            'user_id' => $request->get('name'),
            'comment' => Auth::user()->id,
        ]);
        return back()->with('success', 'Created Successfully');
    }

    public function delete($id){
        $user = Category::find($id);
        $user->delete();
        return back()->with("success", "Deleted Successfully");
    }

}
