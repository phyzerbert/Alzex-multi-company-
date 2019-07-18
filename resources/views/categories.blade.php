@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <div class="page-header page-header-light">
            <div class="page-header-content header-elements-md-inline">
                <div class="page-title d-flex">
                    <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">{{__('page.home')}}</span> - {{__('page.category')}}</h4>
                    <a href="index.html#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>

                <div class="header-elements d-none">
                    <div class="d-flex justify-content-center">
                    </div>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="{{url('/')}}" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> {{__('page.home')}}</a>
                        <span class="breadcrumb-item active">{{__('page.category')}}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container content">
            <div class="card">
                <div class="card-header">
                    <form action="" method="POST" class="form-inline float-left" id="searchForm">
                        @csrf
                        @if (Auth::user()->hasRole('admin'))
                            <select class="form-control form-control-sm mr-sm-2 mb-2" name="company_id" id="search_company">
                                <option value="">{{__('page.select_company')}}</option>
                                @foreach ($companies as $item)
                                    <option value="{{$item->id}}" data-icon="wallet" @if($company_id == $item->id) selected @endif>{{$item->name}}</option>                                            
                                @endforeach     
                            </select>
                            <select class="form-control form-control-sm mr-sm-2 mb-2" name="user_id" id="search_user">
                                <option value="">{{__('page.select_user')}}</option>
                                @foreach ($users as $item)
                                    <option value="{{$item->id}}" data-icon="wallet" @if($user_id == $item->id) selected @endif>{{$item->name}}</option>                                            
                                @endforeach     
                            </select>
                        @endif
                        <input type="text" class="form-control form-control-sm mr-sm-2 mb-2" name="name" id="search_description" value="{{$name}}" placeholder="{{__('page.name')}}">
                        <input type="text" class="form-control form-control-sm mr-sm-2 mb-2" name="comment" id="search_description" value="{{$comment}}" placeholder="{{__('page.comment')}}">
                        
                        <button type="submit" class="btn btn-sm btn-primary mb-2"><i class="icon-search4"></i>&nbsp;&nbsp;{{__('page.search')}}</button>
                        <button type="button" class="btn btn-sm btn-info mb-2 ml-1" id="btn-reset"><i class="icon-eraser"></i>&nbsp;&nbsp;{{__('page.reset')}}</button>
                    </form>
                    @if(Auth::user()->hasRole('user'))
                        <button type="button" class="btn btn-primary float-right" id="btn-add"><i class="icon-plus-circle2 mr-2"></i> {{__('page.add_new')}}</button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-blue">
                                    <th style="width:30px;">#</th>
                                    <th>{{__('page.name')}}</th>
                                    <th>{{__('page.username')}}</th>
                                    <th>{{__('page.comment')}}</th>
                                    @if (Auth::user()->hasRole('user'))                                        
                                        <th>{{__('page.action')}}</th>                                    
                                    @endif
                                </tr>
                            </thead>
                            <tbody>                                
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ (($data->currentPage() - 1 ) * $data->perPage() ) + $loop->iteration }}</td>
                                        <td class="name">{{$item->name}}</td>
                                        <td class="user">{{$item->user->name}}</td>
                                        <td class="comment">{{$item->comment}}</td>
                                        @if (Auth::user()->hasRole('user'))     
                                            <td class="py-1">
                                                <a href="#" class="btn bg-blue btn-icon rounded-round btn-edit" data-id="{{$item->id}}"  data-popup="tooltip" title="{{__('page.edit')}}" data-placement="top"><i class="icon-pencil7"></i></a>
                                                <a href="{{route('category.delete', $item->id)}}" class="btn bg-danger text-pink-800 btn-icon rounded-round ml-2" data-popup="tooltip" title="{{__('page.delete')}}" data-placement="top" onclick="return window.confirm('{{__('page.are_you_sure')}}')"><i class="icon-trash"></i></a>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                        <div class="clearfix mt-1">
                            <div class="float-left" style="margin: 0;">
                                <p>{{__('page.total')}} <strong style="color: red">{{ $data->total() }}</strong> {{__('page.items')}}</p>
                            </div>
                            <div class="float-right" style="margin: 0;">
                                {!! $data->appends(['name' => $name, 'comment' => $comment, 'user_id' => $user_id, 'company_id' => $company_id])->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>                
    </div>

    <!-- The Modal -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('page.add_new')}}</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <form action="{{route('category.create')}}" id="create_form" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{__('page.name')}}</label>
                            <input class="form-control" type="text" name="name" placeholder="Name">
                        </div>

                        {{-- <div class="form-group">
                            <label class="control-label">Parent</label>
                            <select class="form-control" name="parent">
                                <option value="">Select parent category</option>
                                @foreach ($data as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>                                    
                                @endforeach
                            </select>
                        </div> --}}

                        <div class="form-group">
                            <label class="control-label">{{__('page.comment')}}</label>
                            <input class="form-control" type="text" name="comment" placeholder="Comment">
                        </div>
                    </div>    
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-submit"><i class="icon-paperplane"></i>&nbsp;{{__('page.save')}}</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="icon-close2"></i>&nbsp;{{__('page.close')}}</button>
                    </div>
                </form>
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
                <form action="{{route('category.edit')}}" id="edit_form" method="post">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" class="id" name="id" />                    
                        <div class="form-group">
                            <label class="control-label">{{__('page.name')}}</label>
                            <input class="form-control name" type="text" name="name" placeholder="{{__('page.name')}}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{__('page.comment')}}</label>
                            <input class="form-control comment" type="text" name="comment" placeholder="{{__('page.comment')}}">
                        </div>
                    </div>
    
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-submit"><i class="icon-paperplane"></i>&nbsp;{{__('page.save')}}</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="icon-close2"></i>&nbsp;{{__('page.close')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        
        $("#btn-add").click(function(){
            $("#create_form input.form-control").val('');
            $("#create_form .invalid-feedback strong").text('');
            $("#addModal").modal();
        });


        $(".btn-edit").click(function(){
            let id = $(this).data("id");
            let name = $(this).parents('tr').find(".name").text().trim();
            let comment = $(this).parents('tr').find(".comment").text().trim();
            
            $("#edit_form input.form-control").val('');
            $("#edit_form .id").val(id);
            $("#edit_form .name").val(name);
            $("#edit_form .comment").val(comment);

            $("#editModal").modal();
        });

    });
</script>
@endsection
