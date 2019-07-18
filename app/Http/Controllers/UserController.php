<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\User;

use Auth;
use Hash;

class UserController extends Controller
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
        config(['site.page' => 'user']);
        $companies = Company::all();
        $data = User::paginate(15);
        return view('admin.users', compact('data', 'companies'));
    }

        
    public function profile(Request $request){
        $user = Auth::user();
        $current_page = 'profile';
        
        $data = array(
            'user' => $user,
            'current_page' => $current_page
        );
        return view('profile', $data);
    }

    public function updateuser(Request $request){
        $request->validate([
            'name'=>'required',
            'phone_number'=>'required',
            'password' => 'confirmed',
        ]);
        $user = Auth::user();
        $user->name = $request->get("name");
        $user->phone_number = $request->get("phone_number");

        if($request->get('password') != ''){
            $user->password = Hash::make($request->get('password'));
        }
        if($request->has("picture")){
            $picture = request()->file('picture');
            $imageName = time().'.'.$picture->getClientOriginalExtension();
            $picture->move(public_path('images/profile_pictures'), $imageName);
            $user->picture = 'images/profile_pictures/'.$imageName;
        }
        $user->update();
        return back()->with("success", __('page.updated_successfully'));
    }

    public function edituser(Request $request){
        $request->validate([
            'name'=>'required',
            // 'company'=>'required',
            'phone'=>'required',
            'password' => 'confirmed',
        ]);
        $user = User::find($request->get("id"));
        $user->name = $request->get("name");
        $user->company_id = $request->get("company");
        $user->phone_number = $request->get("phone");

        if($request->get('password') != ''){
            $user->password = Hash::make($request->get('password'));
        }
        $user->save();
        return response()->json('success');
    }

    public function create(Request $request){
        $request->validate([
            'name'=>'required|string|unique:users',
            // 'company'=>'required',
            'phone_number'=>'required',
            'role'=>'required',
            'password'=>'required|string|min:6|confirmed'
        ]);
        
        User::create([
            'name' => $request->get('name'),
            'company_id' => $request->get('company'),
            'phone_number' => $request->get('phone_number'),
            'role_id' => $request->get('role'),
            'password' => Hash::make($request->get('password'))
        ]);
        return response()->json('success');
    }

    public function delete($id){
        $user = User::find($id);
        $user->delete();
        return back()->with("success", __('page.deleted_successfully'));
    }

}
