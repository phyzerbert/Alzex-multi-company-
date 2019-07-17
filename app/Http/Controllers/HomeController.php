<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Nexmo;
use Carbon\Carbon;

use App\User;
use App\Models\Transaction;
use App\Models\Company;
use App\Models\Category;
use App\Models\Account;

class HomeController extends Controller
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
    public function index(Request $request) {        
        config(['site.page' => 'home']);
        $company = Company::first();
        $companies = Company::all();
        $user = Auth::user();
        $search_categories = Category::pluck('id')->toArray();
        
        if($user->hasRole('user')){
            $company = $user->company;
        }else{            
            if ($request->get('company_id') != ""){   
                $selected_company = $request->get('company_id');
                $company = Company::find($selected_company);
            }   
        }
        $company_id = $company->id;
        $search_users = $company->users()->pluck('id')->toArray();
        $search_accounts = $company->accounts()->pluck('id')->toArray();
        $first_transaction = $company->transactions()->orderBy('timestamp')->first();

        if($first_transaction){
            $from = date('Y-m-d', strtotime("-1 day", strtotime($first_transaction->timestamp)));
        }else{
            $from = date('Y-m-d', strtotime("-1 days"));
        }
        
        // $from = Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = date('Y-m-d', strtotime("+1 days"));
        $period = $spec_date = ''; 
            
        if ($request->get('spec_date') != ""){   
            $spec_date = $request->get('spec_date');
            $to = $spec_date;
        }else if ($request->get('period') != ""){   
            $period = $request->get('period');
            $from = substr($period, 0, 10);
            $to = substr($period, 14, 10);
        }
        return view('home', compact('period', 'companies', 'company', 'search_users', 'search_categories', 'search_accounts', 'company_id', 'from', 'to', 'spec_date'));
    } 
    
    public function set_pagesize(Request $request){
        // config(['app.pagesize' => $request->get('pagesize')]);
        $request->session()->put('pagesize', $request->get('pagesize'));
        // $setting = Setting::find(1);
        // $setting->pagesize = $request->get('pagesize');
        // $setting->save();
        return back();
    }
}
