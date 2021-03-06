@extends('shop.layouts.index')

@section('content')
    <div style="background: #ffffff;height: 100%; text-align: center">
        <img src="{{url('styles/images/success.png')}}" alt="" class="suc-tip">
        <h5 class="suc-title">修改成功</h5>
        <div class="suc-back">5秒后<a href="javascript:history.go(-1)">返回上一页</a></div>
        @if(!empty($back_url))
            @foreach($back_url as $url)
                {!! $url !!}
            @endforeach
        @endif
    </div>

@section('script')
    <script>
        var i = 4;
        $(function () {
            setInterval(function () {
                if (i == 0) {
                    window.history.go(-1);
                }
                $('.suc-back').html(i + '秒后<a href="javascript:history.go(-1)">返回上一页</a>');
                i--;
            }, 1000)
        })
    </script>
@endsection
@endsection
