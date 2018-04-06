@extends('shop.layouts.index')
@section('content')
    <form class="form-horizontal" method="post" action="{{url('admin/login')}}">
        {{csrf_field()}}
        <div class="error">
            @if(count($errors) > 0)
                @if(is_object($errors))
                    <p style="color:red">{{$errors->all()[0]}}</p>
                @else
                    <p style="color:red">{{$errors}}</p>
                @endif
            @endif
        </div>
        <div class="container-fluid">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-3 control-label">{{$lang['username']}}</label>
                <div class="col-sm-7">
                    <input type="text" name="username" class="form-control" id="inputEmail3" placeholder="{{$lang['username']}}">
                </div>
            </div>
            <div class="form-group">
                <label for="inputPassword3" class="col-sm-3 control-label">{{$lang['password']}}</label>
                <div class="col-sm-7">
                    <input type="password" name="password" class="form-control" id="inputPassword3" placeholder="{{$lang['password']}}">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-9">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox"> {{$lang['remember']}}
                        </label>
                        <label class="col-sm-offset-4">
                            <a class="link-home cl-link-blue" href="../">{{$lang['back_home']}}</a>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-4 center clear">
                    <button type="submit" class="btn btn-primary">　　{{$lang['login']}}　　</button>
                </div>
            </div>
            <div class="copy-right text-info text-center">&copy; 2010-2018 ShopEx,Inc.All rights reserved.</div>
        </div>
    </form>
@endsection