@extends('shop.layouts.index')
@section('content')
    <body style="overflow-y: scroll;background-color: #f7f7f7;">
    <div class="warpper clearfix">
        <div class="title"><a href="javascript:history.go(-1);" class="s-back">返回</a>系统设置 - 编辑计划任务</div>
        <div class="content">
            <div class="tip">
                <div class="tip_title">
                    <i class="tip_icon"></i>
                    <h5>操作提示</h5>
                </div>
                <ul>
                    <li>对于已安装的计划任务可进行编辑，编辑计划任务名称、内容、执行时间等信息。</li>
                </ul>
            </div>
            <div class="fromlist clearfix">
                <div class="main-info">
                    <form action="{{url('admin/cron/'.$cron->cron_id)}}" method="post" class="form-horizontal"
                          enctype="multipart/form-data">
                        {{csrf_field()}}
                        {{method_field('PUT')}}

                        <div class="form-group">
                            <label class="col-sm-4 control-label">计划任务名称：</label>
                            <div class="col-sm-4">
                                <input type="text" name="cron_name" class="form-control" value="{{$cron->cron_name}}"
                                       placeholder="计划任务名称">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">计划任务描述：</label>
                            <div class="col-sm-4">
                                <textarea name="cron_desc" id="" cols="15" rows="5" class="form-control"
                                          placeholder="计划任务描述">{{$cron->cron_desc}}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">每次处理记录个数：</label>
                            <div class="col-sm-2">
                                <input type="text" name="cron_num" class="form-control" value="{{$cron->cron_num}}"
                                       placeholder="个数">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">计划任务执行时间：</label>
                            <div class="col-sm-4">
                                <div class="clearfix hg40 pad-top-4">
                                    <label class="radio-inline fl">
                                        <input type="radio" name="ttype" value="day" @if($cron->day > 0) checked @endif> 每月
                                    </label>
                                    <div class="fl wd-180">
                                        <select name="day" id="" class="form-control wd-120 input-sm fl">
                                            @for($i=1;$i<32;$i++)
                                                <option value="{{$i}}" @if($cron->day == $i) selected @endif>{{$i}}</option>
                                            @endfor
                                        </select>
                                        <span class="line-hg-30 mar-left-5">天</span>
                                    </div>
                                </div>
                                <div class="clearfix hg40 pad-top-4">
                                    <label class="radio-inline fl">
                                        <input type="radio" name="ttype" value="week" @if($cron->week > 0) checked @endif> 每周
                                    </label>
                                    <div class="fl wd-180">
                                        <select name="week" id="" class="form-control wd-120 input-sm fl">
                                            @foreach($week as $k => $w)
                                                <option value="{{$k+1}}" @if($cron->week == $k+1) selected @endif>{{$w}}</option>
                                            @endforeach
                                        </select>
                                        <span class="line-hg-30 mar-left-5">星期</span>
                                    </div>
                                </div>
                                <div class="clearfix hg40 pad-top-4">
                                    <label class="radio-inline fl">
                                        <input type="radio" name="ttype" value="hour" @if($cron->day == 0 && $cron->week == 0) checked @endif> 每日
                                    </label>
                                    <div class="fl wd-180">
                                        <input type="text" name="hour" class="form-control input-sm fl"
                                               value="{{$cron->hour}}"
                                               placeholder="请用半角逗号分隔多个小时">
                                    </div>
                                    <span class="line-hg-30 mar-left-5 fl mar-right-20">小时</span>
                                    <div class="clearfix checkbox-items fl">
                                        <div class="checkbox-item fl mar-right-20">
                                            <input type="checkbox" class="ui-checkbox" value="1"
                                                   id="all_hour" @if($cron->hour == '0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23') checked @endif>
                                            <label class="ui-label mar-top-7" for="all_hour">全部</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">分钟：</label>
                            <div class="col-sm-4">
                                <input type="text" name="minute" class="form-control"
                                       value="{{$cron->minute}}"
                                       placeholder="请用半角逗号分隔多个分钟">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">执行后关闭：</label>
                            <div class="col-sm-4">
                                <div class="clearfix checkbox-items">
                                    <div class="checkbox-item fl mar-right-20">
                                        <input type="checkbox" name="cron_run_once" class="ui-checkbox" value="1"
                                               id="cron_run_once">
                                        <label class="ui-label mar-top-7" for="cron_run_once">关闭</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">允许执行的服务器ip：</label>
                            <div class="col-sm-4">
                                <input type="text" name="allow_ip" class="form-control"
                                       value="{{$cron->allow_ip}}"
                                       placeholder="允许运行服务器的IP，请用半角逗号分隔多个IP">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">执行的任务：</label>
                            <div class="col-sm-4 checkbox-items">
                                <select name="alow_files" id="" class="form-control wd-240 input-sm fl">
                                    @foreach($task as $k => $t)
                                        <option value="{{$k}}" @if($cron->alow_files == $k) selected @endif>{{$t}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-4 control-label">&nbsp;</div>
                            <div class="">
                                <input type="submit" value="　确定　" class="btn btn-danger clearfix">
                                <a type="button" class="btn btn-default clearfix mar-left-20"
                                   href="javascript:history.go(-1)">返回</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @component('shop.components.copyright',['copyright'=>$copyright])@endcomponent
    <div style="height: 30px">　</div>
    </body>
@section('script')
    <script>
        $(function () {
            $('#all_hour').on('click', function () {
                var all = '';
                for (var i = 0; i < 24; i++) {
                    all += i + ',';
                }
                all = all.substr(0, all.length-1);
                if($('input[name=hour]').val()==all){
                    $('input[name=hour]').val('');
                }else{
                    $('input[name=hour]').val(all);
                }
            });
        });
    </script>
@endsection
@endsection