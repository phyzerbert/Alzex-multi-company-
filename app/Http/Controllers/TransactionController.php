<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\User;
use App\Models\Category;
use App\Models\Company;
use App\Models\Account;

use Auth;

class TransactionController extends Controller
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
        config(['site.page' => 'transaction']);
        $user = Auth::user();
        $categories = Category::all();
        $users = User::all();
        $companies = Company::all();
        $accounts = Account::all();
        
        $mod = new Transaction();
        $category = $company_id = $account = $description = $type = $period = '';

        if($user->hasRole('user')){
            $company = $user->company;
            $company_id = $company->id;
            $accounts = $company->accounts;
            $categories = $user->categories;
            $users = $company->users;
            $mod = $company->transactions();
        }

        if ($request->get('type') != ""){
            $type = $request->get('type');
            $mod = $mod->where('type', $type);
        }
        if ($request->get('company_id') != ""){
            $company_id = $request->get('company_id');
            $mod = $mod->where('company_id', $company_id);
            $company = Company::find($company_id);
            $categories = $company->categories();
        }
        if ($request->get('description') != ""){
            $description = $request->get('description');
            // $users = User::where('description', 'LIKE', "%$description%")->pluck('id');
            $mod = $mod->where('description', 'LIKE', "%$description%")->orWhere('quantity', $description);
        }
        if ($request->get('category') != ""){
            $category = $request->get('category');
            $mod = $mod->where('category_id', $category);
        }
        if ($request->get('account') != ""){
            $account = $request->get('account');
            $mod = $mod->where('from', $account)->orWhere('to', $account);
        }
        if ($request->get('period') != ""){   
            $period = $request->get('period');
            $from = substr($period, 0, 10)." 00:00:00";
            $to = substr($period, 14, 10)." 23:59:59";
            if($from == $to){
                $mod = $mod->whereDate('timestamp', $to);
            }else{                
                $mod = $mod->whereBetween('timestamp', [$from, $to]);
            } 
        }

        $pagesize = $request->session()->get('pagesize');
        $data = $mod->orderBy('timestamp', 'desc')->paginate($pagesize);        
        $collection = $mod->get();
        $expenses = $collection->where('type', 1)->sum('amount');
        $incomes= $collection->where('type', 2)->sum('amount');
        return view('transaction.index', compact('data', 'companies', 'expenses', 'incomes', 'categories', 'accounts', 'users', 'type', 'company_id', 'description', 'category', 'account', 'period', 'pagesize'));
    }

    public function daily(Request $request)
    {
        config(['site.page' => 'transaction_daily']);
        $user = Auth::user();
        $categories = Category::all();
        $companies = Company::all();
        $accounts = Account::all();
        $users = User::all();
        
        $mod = new Transaction();
        $mod1 = new Transaction();
        $last_transaction = Transaction::orderBy('timestamp', 'desc')->first();
        $category = $company_id = $account = $description = $type = $period = $change_date = '';
        if($user->hasRole('user')){
            $company = $user->company;
            $company_id = $company->id;
            $accounts = $company->accounts;
            $users = $company->users;
            $accounts = $company->accounts;
            $categories = $user->categories;
            $mod = $company->transactions();
            $mod1 = $company->transactions();
            $last_transaction = $company->transactions()->orderBy('timestamp', 'desc')->first();
        }
        if(isset($last_transaction)){
            $period = date('Y-m-d', strtotime($last_transaction->timestamp));
        }else{
            $period = date('Y-m-d');
        }

        if ($request->get('type') != ""){
            $type = $request->get('type');
            $mod = $mod->where('type', $type);
            $mod1 = $mod1->where('type', $type);
        }

        if ($request->get('company_id') != ""){
            $company_id = $request->get('company_id');
            $mod = $mod->where('company_id', $company_id);
            $mod1 = $mod1->where('company_id', $company_id);
            $company = Company::find($company_id);
            $categories = $company->categories();
        }
        // if ($request->get('user') != ""){
        //     $user = $request->get('user');
        //     $users = User::where('name', 'LIKE', "%$user%")->pluck('id');
        //     $mod = $mod->whereIn('user_id', $users);
        //     $mod1 = $mod1->whereIn('user_id', $users);
        // }
        
        if ($request->get('description') != ""){
            $description = $request->get('description');
            $mod = $mod->where('description', 'LIKE', "%$description%")->orWhere('amount', $description);
            $mod1 = $mod1->where('description', 'LIKE', "%$description%")->orWhere('amount', $description);
        }
        if ($request->get('category') != ""){
            $category = $request->get('category');
            $mod = $mod->where('category_id', $category);
            $mod1 = $mod1->where('category_id', $category);
        }
        if ($request->get('account') != ""){
            $account = $request->get('account');
            $mod = $mod->where('from', $account)->orWhere('to', $account);
            $mod1 = $mod1->where('from', $account)->orWhere('to', $account);
        }
        if ($request->get('period') != ""){   
            $period = $request->get('period');
        }
        if($request->get('change_date') != ""){
            $change_date = $request->get('change_date');
            if($change_date == "1"){
                $period = date('Y-m-d', strtotime($period .' -1 day'));
            }else if($change_date == "2"){
                $period = date('Y-m-d', strtotime($period .' +1 day'));
            }
        }
        
        $mod = $mod->whereDate('timestamp', $period);
        $mod1 = $mod1->whereDate('timestamp', $period);

        $pagesize = $request->session()->get('pagesize');
        if(!$pagesize){$pagesize = 15;}
        $data = $mod->orderBy('created_at', 'desc')->paginate($pagesize);
        $expenses = $mod->where('type', 1)->sum('amount');
        $incomes = $mod1->where('type', 2)->sum('amount');
        return view('transaction.daily', compact('data', 'expenses', 'incomes', 'companies', 'company_id', 'categories', 'accounts', 'users', 'type', 'description', 'category', 'account', 'period', 'pagesize'));
    }

    public function create(Request $request){
        $user = Auth::user();
        $categories = $user->categories; 
        $company = $user->company;
        $accounts = $company->accounts;
        return view('transaction.create', compact('users', 'company', 'categories', 'accounts'));
    }

    public function expense(Request $request){
        $request->validate([
            'category'=>'required',
            'account'=>'required',
            'amount'=>'required|numeric',
            'timestamp'=>'required',
        ]);
        $user = Auth::user();
        $account = Account::find($request->get('account'));
        // if ($account->balance < $request->get('amount')) {
        //     return back()->withErrors(['insufficent' => 'Insufficent balance.']);
        // }
        $attachment = '';
        if($request->file('attachment') != null){
            $image = request()->file('attachment');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploaded/transaction_attachments'), $imageName);
            $attachment = 'uploaded/transaction_attachments/'.$imageName;
        }

        Transaction::create([
            'type' => 1,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'category_id' => $request->get('category'),
            'from' => $request->get('account'),
            'amount' => $request->get('amount'),
            'description' => $request->get('description'),
            'timestamp' => $request->get('timestamp')." ".date("H:i:s"),
            'attachment' => $attachment,
        ]);

        $account->decrement('balance', $request->get('amount'));
        return back()->with('success', __('page.created_successfully'));
    }

    public function incoming(Request $request){
        $request->validate([
            'category'=>'required',
            'account'=>'required',
            'amount'=>'required|numeric',
            'timestamp'=>'required',
        ]);
        $user = Auth::user();        
        $attachment = '';
        if($request->file('attachment') != null){
            $image = request()->file('attachment');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploaded/transaction_attachments'), $imageName);
            $attachment = 'uploaded/transaction_attachments/'.$imageName;
        }
        $account = Account::find($request->get('account'));

        Transaction::create([
            'type' => 2,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'category_id' => $request->get('category'),
            'to' => $request->get('account'),
            'amount' => $request->get('amount'),
            'description' => $request->get('description'),
            'timestamp' => $request->get('timestamp'),
            'attachment' => $attachment,
        ]);
        $account->increment('balance', $request->get('amount'));

        return back()->with('success', __('page.created_successfully'));
    }

    public function transfer(Request $request){
        $request->validate([
            'account'=>'required',
            'target'=>'required',
            'amount'=>'required|numeric',
            'timestamp'=>'required',
        ]);
        $user = Auth::user();
        $account = Account::find($request->get('account'));
        $target = Account::find($request->get('target'));

        // if ($account->balance < $request->get('amount')) {
        //     return back()->withErrors(['insufficent' => 'Insufficent balance.']);
        // }

        $attachment = '';
        if($request->file('attachment') != null){
            $image = request()->file('attachment');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploaded/transaction_attachments'), $imageName);
            $attachment = 'uploaded/transaction_attachments/'.$imageName;
        }

        Transaction::create([
            'type' => 3,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'category_id' => $request->get('category'),
            'from' => $request->get('account'),
            'to' => $request->get('target'),
            'amount' => $request->get('amount'),
            'description' => $request->get('description'),
            'timestamp' => $request->get('timestamp'),
            'attachment' => $attachment,
        ]);
        
        $account->decrement('balance', $request->get('amount'));
        $target->increment('balance', $request->get('amount'));

        return back()->with('success', __('page.created_successfully'));
    }

    public function edit(Request $request, $id, $page){
        $item = Transaction::find($id);
        $users = User::all();
        $categories = Category::all();
        $accountgroups = Accountgroup::all();  
        return view('transaction.edit', compact('item', 'users', 'categories', 'accountgroups', 'page'));
    }

    public function update(Request $request){
        $item = Transaction::find($request->get('id'));
        $type = $item->type;
        $item->user_id = $request->get('user');
        $item->category_id = $request->get('category');
        $item->description = $request->get('description');
        $item->timestamp = $request->get('timestamp');
        if($type == 1){
            // dd($request->all());
            $old_account = $item->account;
            if($item->from != $request->get('account')){
                $new_account = Account::find($request->get('account'));
                $old_account->increment('balance', $item->amount);
                $new_account->decrement('balance', $request->get('amount'));
                $old_account->decrement('balance', $request->get('amount'));
                $new_account->increment('balance', $item->amount);                
                $item->amount = $request->get('amount');
                $item->from = $request->get('account');
            }else if($item->amount != $request->get('amount')){
                $old_account->increment('balance', $item->amount);
                $old_account->decrement('balance', $request->get('amount'));             
                $item->amount = $request->get('amount');
            }
        }else if($type == 2){
            $old_target = $item->target;
            if($item->to != $request->get('target')){
                $new_target = Account::find($request->get('target'));
                $new_target->increment('balance', $request->get('amount'));
                $old_target->decrement('balance', $item->amount);
                $item->to = $request->get('account');
            }
            $item->amount = $request->get('amount');
        }else if($type == 3){
            $old_from = $item->account;
            if($item->from != $request->get('account')){
                $new_from = Account::find($request->get('account'));
                $old_from->increment('balance', $item->amount);
                $new_from->decrement('balance', $request->get('amount'));
                $item->from = $request->get('account');
            }

            $old_target = $item->target;
            if($item->to != $request->get('target')){
                $new_target = Account::find($request->get('target'));
                $new_target->increment('balance', $request->get('amount'));
                $old_target->decrement('balance', $item->amount);
                $item->to = $request->get('target');
            }

            if($item->to == $request->get('target') && $item->from == $request->get('account') && $item->amount != $request->get('amount')){
                $old_account->increment('balance', $item->amount);
                $old_target->decrement('balance', $item->amount);
                $old_account->decrement('balance', $request->get('amount'));
                $old_target->increment('balance', $request->get('amount'));
            }
            $item->amount = $request->get('amount');
        }

        if($request->file('attachment') != null){
            $image = request()->file('attachment');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploaded/transaction_attachments'), $imageName);
            $item->attachment = 'uploaded/transaction_attachments/'.$imageName;
        }

        $item->save();
        
        return response()->json('success');
    
        // return redirect(route('transaction.index'))->with('success', __('page.updated_successfully'));
        // return back()->with('success', __('page.updated_successfully'));

    }

    public function delete($id){
        $item = Transaction::find($id);

        $type = $item->type;
        if($type == 1){
            $account = $item->account;
            $account->increment('balance', $item->amount);
        }else if($type == 2){
            $target = $item->target;
            $target->decrement('balance', $item->amount);
        }else if($type == 3){
            $account = $item->account;
            $account->increment('balance', $item->amount);
            $target = $item->target;
            $target->decrement('balance', $item->amount);
        }

        $item->delete();
        return back()->with("success", __('page.deleted_successfully'));
    }
    
    public function get_transaction(Request $request){
        $id = $request->get('id');
        $item = Transaction::find($id);
        return response()->json($item);
    }
}
