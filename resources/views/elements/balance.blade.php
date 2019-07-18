<div class="header-elements d-flex">
    <div class="d-flex justify-content-center">
        <div class="btn-group justify-content-center">
            <a href="#" class="btn bg-primary-400 dropdown-toggle" data-toggle="dropdown"><i class="icon-wallet"></i>  {{__('page.balance')}}</a>
            <div class="dropdown-menu">
                @php
                    $balance = \App\Models\Account::sum('balance');
                    $accounts = \App\Models\Account::all();
                    if (Auth::user()->hasRole('user')) {
                        $accounts = Auth::user()->company->accounts;
                        $balance = Auth::user()->company->accounts()->sum('balance');
                    }
                @endphp
                <div class="dropdown-header dropdown-header-highlight">{{__('page.total')}}:    {{number_format($balance)}}</div>
                    @foreach ($accounts as $item)                                         
                        <div class="dropdown-item">
                            <div class="flex-grow-1">{{$item->name}}</div>
                            <div class="">                                                
                                @php
                                    $account_expense = $item->expenses()->sum('amount');
                                    $account_incoming = $item->incomings()->sum('amount');
                                    $account_balance = $account_incoming - $account_expense;
                                @endphp
                                {{number_format( $account_balance)}}
                            </div>
                        </div>
                    @endforeach
                <div class="dropdown-divider"></div>
            </div>
        </div>
    </div>
</div>