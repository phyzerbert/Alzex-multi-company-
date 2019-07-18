@extends('layouts.master')
@section('style')
    <link rel="stylesheet" href="{{asset('master/global_assets/js/plugins/imageviewer/css/jquery.verySimpleImageViewer.css')}}">
    <style>
        #image_preview {
            max-width: 600px;
            height: 600px;
        }
        .image_viewer_inner_container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    @php
        $role = Auth::user()->role->slug;
    @endphp
    <div class="content-wrapper">
        <div class="page-header page-header-light">
            <div class="page-header-content header-elements-md-inline">
                <div class="page-title d-flex">
                    <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">{{__('page.home')}}</span> - {{__('page.transaction')}}</h4>
                    <a href="index.html#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>
                @include('elements.balance')
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="{{url('/')}}" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> {{__('page.home')}}</a>
                        <span class="breadcrumb-item active">{{__('page.transaction')}}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <form class="form-inline ml-3 float-left" action="{{route('set_pagesize')}}" method="post" id="pagesize_form">
                        @csrf
                        <label for="pagesize" class="control-label">{{__('page.show')}} :</label>
                        <select class="form-control form-control-sm mx-2" name="pagesize" id="pagesize">
                            <option value="" @if($pagesize == '') selected @endif>15</option>
                            <option value="25" @if($pagesize == '25') selected @endif>25</option>
                            <option value="50" @if($pagesize == '50') selected @endif>50</option>
                            <option value="100" @if($pagesize == '100') selected @endif>100</option>
                        </select>
                    </form>
                    @include('transaction.daily_filter')
                    <a href="{{route('transaction.create')}}" class="btn btn-primary btn-sm float-right" id="btn-add"><i class="icon-plus-circle2 mr-2"></i> {{__('page.add_new')}}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-blue">
                                    <th style="width:30px;">#</th>
                                    <th>{{__('page.date')}}</th>
                                    <th>{{__('page.company')}}</th>
                                    <th>{{__('page.category')}}</th>
                                    <th>{{__('page.description')}}</th>
                                    <th>{{__('page.amount')}}</th>
                                    <th>{{__('page.withdraw_from')}}</th>
                                    <th>{{__('page.target_account')}}</th>
                                    <th>{{__('page.user')}}</th>
                                    <th>{{__('page.balance')}}</th>
                                    <th>{{__('page.type')}}</th>
                                    <th>{{__('page.action')}}</th>
                                </tr>
                            </thead>
                            <tbody>                                
                                @foreach ($data as $item)
                                @php
                                    $timestamp = date('Y-m-d', strtotime($item->timestamp));
                                   
                                   $before_expenses = \App\Models\Transaction::where('type', 1)->where('timestamp', '<', $timestamp)->sum('amount');
                                   
                                    $before_incoming = \App\Models\Transaction::where('type', 2)->where('timestamp', '<', $timestamp)->sum('amount');

                                    $equal_expenses = \App\Models\Transaction::where('type', 1)->whereDate('timestamp', $timestamp)->where('created_at', '<=', $item->created_at)->sum('amount');
                                    $equal_incoming = \App\Models\Transaction::where('type', 2)->whereDate('timestamp', $timestamp)->where('created_at', '<=',$item->created_at)->sum('amount');
                                    
                                    $total_expenses = $before_expenses + $equal_expenses;
                                    $total_incoming = $before_incoming + $equal_incoming;
                                    
                                    $current_balance = $total_incoming - $total_expenses;
                                @endphp
                                    <tr>
                                        <td>{{ (($data->currentPage() - 1 ) * $data->perPage() ) + $loop->iteration }}</td>
                                        <td class="date">{{ date('Y-m-d', strtotime($item->timestamp))}}</td>
                                        <td class="company">@isset($item->company->name){{$item->company->name}}@endisset</td>
                                        <td class="category">@isset($item->category->name){{$item->category->name}}@endif</td>
                                        <td class="description">
                                            {{$item->description}}
                                            @if ($item->attachment != "")
                                                <a href="#" class="btn-attach" data-value="{{$item->attachment}}"><i class="icon-attachment"></i></a>
                                            @endif
                                        </td>
                                        <td class="amount">
                                            @if ($item->type == 1)
                                                <span style="color:red">-{{ number_format($item->amount) }}</span>
                                            @elseif($item->type == 2)
                                                <span style="color:green">{{ number_format($item->amount) }}</span>
                                            @else
                                                {{ number_format($item->amount) }}
                                            @endif
                                        </td>
                                        <td class="from">@isset($item->account->name){{$item->account->name}}@endisset</td>
                                        <td class="to">@isset($item->target->name){{$item->target->name}}@endisset</td>
                                        <td class="user">{{$item->user->name}}</td>
                                        <td class="balance">
                                            @if ($current_balance < 0)
                                                <span style="color:red">{{ number_format($current_balance) }}</span>
                                            @else
                                                <span style="color:green">{{ number_format($current_balance) }}</span>
                                            @endif
                                        </td>
                                        <td class="type">
                                            @php
                                                $types = array(__('page.expense'), __('page.incoming'), __('page.transfer'));
                                            @endphp
                                            {{$types[$item->type-1]}}
                                        </td>
                                        <td class="py-1" style="min-width:130px;">
                                            <!--<a href="{{route('transaction.edit', [$item->id, 'daily'])}}" class="btn bg-blue btn-icon rounded-round btn-edit" data-id="{{$item->id}}"  data-popup="tooltip" title="{{__('page.edit')}}" data-placement="top"><i class="icon-pencil7"></i></a>-->
                                            <a href="#" class="btn bg-blue btn-icon rounded-round btn-edit" data-id="{{$item->id}}"  data-popup="tooltip" title="{{__('page.edit')}}" data-placement="top"><i class="icon-pencil7"></i></a>
                                            <a href="{{route('transaction.delete', $item->id)}}" class="btn bg-danger text-pink-800 btn-icon rounded-round ml-2" data-popup="tooltip" title="{{__('page.delete')}}" data-placement="top" onclick="return window.confirm('{{__('page.are_you_sure')}}')"><i class="icon-trash"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="text-danger text-center">
                                <tr>
                                    <td colspan="3">{{__('page.total')}}</td>
                                    <td colspan="3">{{__('page.expenses')}} : -{{number_format($expenses)}}</td>
                                    <td colspan="3">{{__('page.incomes')}} : {{number_format($incomes)}}</td>
                                    <td colspan="3">{{__('page.profit')}} : {{number_format($incomes - $expenses)}}</td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="clearfix mt-1">
                            <div class="float-left" style="margin: 0;">
                                <p>{{__('page.total')}} <strong style="color: red">{{ $data->total() }}</strong> {{__('page.items')}}</p>
                            </div>
                            <div class="float-right" style="margin: 0;">
                                {!! $data->appends(['description' => $description, 'category' => $category, 'type' => $type, 'account' => $account, ''])->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>                
    </div>

    <div class="modal fade" id="attachModal">
        <div class="modal-dialog" style="margin-top:17vh">
            <div class="modal-content">
                <div id="image_preview"></div>
                {{-- <img src="" id="attachment" width="100%" height="600" alt=""> --}}
            </div>
        </div>
    </div>
    
    
    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('page.edit')}}</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <form action="{{route('transaction.update')}}" method="POST" id="edit_form" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="id" class="id">
                        <div class="form-group">
                            <label>{{__('page.user')}}:</label>
                            <select data-placeholder="Select user" name="user" class="form-control form-control-select2 user" data-fouc>
                                @foreach ($users as $user)
                                    <option value={{$user->id}}>{{$user->name}}</option>
                                @endforeach
                            </select>
                            <span class="form-text text-success user_error"></span>
                        </div>
                        <div class="form-group">
                            <label>{{__('page.category')}}:</label>
                            <select data-placeholder="{{__('page.select_category')}}" name="category" class="form-control form-control-select2 category" data-fouc>
                                <option label="{{__('page.select_category')}}"></option>
                                @foreach ($categories as $category)
                                    <option value={{$category->id}}>{{$category->name}}</option>
                                @endforeach
                            </select>
                            <span class="form-text text-success category_error"></span>
                        </div>
                        <div class="form-group account_div">
                            <label>{{__('page.withdraw_from')}}:</label>
                            <select data-placeholder="{{__('page.withdraw_from')}}" name="account" class="form-control form-control-select2-icons account" id="from_account" data-fouc>
                                <option label="{{__('page.withdraw_from')}}"></option>
                                @foreach ($accounts as $account)
                                    <option value="{{$account->id}}" data-icon="wallet">{{$account->name}}</option>                                            
                                @endforeach                              
                            </select>
                            <span class="form-text text-success account_error"></span>
                        </div>
                        <div class="form-group target_div">
                            <label>{{__('page.target_account')}}:</label>
                            <select data-placeholder="{{__('page.target_account')}}" name="target" class="form-control form-control-select2-icons target" id="target_account" data-fouc>
                                <option label="{{__('page.target_account')}}"></option>
                                @foreach ($accounts as $account)
                                    <option value="{{$account->id}}" data-icon="wallet">{{$account->name}}</option>                                            
                                @endforeach                            
                            </select>
                            <span class="form-text text-success target_error"></span>
                        </div>
                        <div class="form-group">
                            <label>{{__('page.date')}}:</label>
                            <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text"><i class="icon-calendar"></i></span>
                                </span>
                                <input type="text" name="timestamp" class="form-control pickadate timestamp" placeholder="{{__('page.date')}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{__('page.amount')}}:</label>
                            <input type="number" name="amount" class="form-control amount" required placeholder="{{__('page.amount')}}">
                            <span class="form-text text-success amount_error"></span>
                        </div>
                        <div class="form-group">
                            <label>{{__('page.attachment')}}:</label>
                            <input type="file" name="attachment" class="form-input-styled attachment" accept="image/*" data-fouc>
                            <span class="form-text text-muted">{{__('page.accepted_formats_image')}}</span>
                        </div>
                        <div class="form-group">
                            <label>{{__('page.description')}}:</label>
                            <input type="text" name="description" class="form-control description" placeholder="{{__('page.description')}}">
                        </div>
                    </div>
    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-submit"><i class="icon-paperplane"></i>&nbsp;{{__('page.save')}}</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="icon-close2"></i>&nbsp;{{__('page.close')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
{{-- <script src="{{asset('master/global_assets/js/plugins/daterangepicker/jquery.daterangepicker.min.js')}}"></script> --}}
<script src="{{asset('master/global_assets/js/plugins/ui/moment/moment.min.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/pickers/daterangepicker.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/imageviewer/js/jquery.verySimpleImageViewer.min.js')}}"></script>

<script src="{{asset('master/global_assets/js/plugins/forms/selects/select2.min.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/forms/styling/uniform.min.js')}}"></script>
    <!-- Theme JS files -->
<script src="{{asset('master/global_assets/js/plugins/ui/moment/moment.min.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/pickers/daterangepicker.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/pickers/anytime.min.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/pickers/pickadate/picker.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/pickers/pickadate/picker.date.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/pickers/pickadate/picker.time.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/pickers/pickadate/legacy.js')}}"></script>
<script src="{{asset('master/global_assets/js/plugins/notifications/jgrowl.min.js')}}"></script>
<script>
    var FormLayouts = function() {

        var _componentSelect2 = function() {
            if (!$().select2) {
                console.warn('Warning - select2.min.js is not loaded.');
                return;
            };

            $('.form-control-select2').select2();

            function iconFormat(icon) {
                var originalOption = icon.element;
                if (!icon.id) { return icon.text; }
                var $icon = "<i class='icon-" + $(icon.element).data('icon') + "'></i>" + icon.text;

                return $icon;
            }

            $('.form-control-select2-icons').select2({
                templateResult: iconFormat,
                minimumResultsForSearch: Infinity,
                templateSelection: iconFormat,
                escapeMarkup: function(m) { return m; }
            });
        };

        var _componentUniform = function() {
            if (!$().uniform) {
                console.warn('Warning - uniform.min.js is not loaded.');
                return;
            }

            $('.form-input-styled').uniform({
                fileButtonClass: 'action btn bg-pink-400'
            });
        };

        return {
            init: function() {
                _componentSelect2();
                _componentUniform();
            }
        }
    }();

    document.addEventListener('DOMContentLoaded', function() {
        FormLayouts.init();
    }); 
</script>




<script>
    $(document).ready(function () {
        $('#search_period').daterangepicker({ 
            singleDatePicker: true,
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
        

        $(".btn-attach").click(function(e){
            e.preventDefault();
            let path = '{{asset("/")}}' + $(this).data('value');
            $("#attachment").attr('src', path);
            $("#attachModal").modal();
        });
        $("#btn-reset").click(function(){
            $("#search_description").val('');
            $("#search_category").val('');
            $("#search_account").val('');
            $("#search_type").val('');
            $("#period").val('');
        });

        $("#pagesize").change(function(){
            $("#pagesize_form").submit();
        })

        $("#prev_date").click(function(){
            $('#change_date').val('1');
            $("#searchForm").submit();
        });
        $("#next_date").click(function(){
            $('#change_date').val('2');
            $("#searchForm").submit();
        });


        $(".btn-attach").click(function(e){
            e.preventDefault();
            let path = '{{asset("/")}}' + $(this).data('value');
            // $("#attachment").attr('src', path);
            $("#image_preview").html('')
            $("#image_preview").verySimpleImageViewer({
                imageSource: path,
                frame: ['100%', '100%'],
                maxZoom: '900%',
                zoomFactor: '10%',
                mouse: true,
                keyboard: true,
                toolbar: true,
            });

            $("#attachModal").modal();

        });
        
        
        $("input.pickadate").pickadate({
            format: 'yyyy-mm-dd',
            today: false,
            clear: false,
            close: false,
        });

        $(".btn-edit").click(function(){
            let id = $(this).data('id');
            let token = "{{csrf_token()}}";
            $.ajax({
                url: "{{route('get_transaction')}}",
                data: {id: id, _token: token},
                dataType: "json",
                type: "POST",
                success: function(data){
                    <!--console.log(data);-->
                    $("#edit_form .id").val(data.id);
                    $("#edit_form .timestamp").val(data.timestamp.substring(0,10));
                    $("#edit_form .user").val(data.user_id).change();
                    $("#edit_form .category").val(data.category_id).change();
                    $("#edit_form .amount").val(data.amount);
                    $("#edit_form .description").val(data.description);
                    $("#from_account").val(data.from).change();
                    $("#target_account").val(data.to).change();
                    $("#edit_form .target_div").show();
                    $("#edit_form .account_div").show();

                    if(data.type == "1"){
                        $("#edit_form .target_div").hide();
                    }else if(data.type == "2"){
                        $("#edit_form .account_div").hide();
                    }                    
                    
                    $("#editModal").modal();    
                },
                error: function(err){
                    alert('Something went wrong.');
                    return false;
                }
            });

        });
        
        $("#edit_form .btn-submit").click(function(){

            let attachment = $("#edit_form .attachment")[0].files[0];
            let form_data = new FormData($("#edit_form")[0]);
            form_data.append('attachment', attachment);
            $("#ajax-loading").show();
            $.ajax({
                url: "{{route('transaction.update')}}",
                type: 'POST',
                dataType: 'json', 
                cache: false,                   
                processData: false,
                contentType: false,
                data: form_data,
                success : function(data) {
                    $("#ajax-loading").hide();
                    
                    if(data == 'success') {
                        // $("#depositModal").modal('hide');
                        alert("{{__('page.updated_successfully')}}");                           
                        window.location.reload();
                    }
                    else if(data.message == 'The given data was invalid.') {
                        alert(data.message);
                    }
                },
                error: function(data) {
                    $("#ajax-loading").hide();
                    console.log(data.responseJSON);
                    if(data.responseJSON.message == 'The given data was invalid.') {
                        let messages = data.responseJSON.errors;
                        if(messages.amount) {
                            $('#edit_form .amount_error').text(data.responseJSON.errors.amount[0]);
                            $('#edit_form .amount').focus();
                        }

                    }
                }
            });
        })
        
        $("#edit_form input").keypress(function(e){
            if(e.keyCode == 13){
                $("#edit_form .btn-submit").trigger('click');
            };
        })
    });
</script>
@endsection
