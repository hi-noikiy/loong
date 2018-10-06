<?php
/**
 * Created by PhpStorm.
 * User: Administrator - chenyu
 * Date: 2018/6/22
 * Time: 16:58
 * Desc:
 */

namespace App\Http\Controllers\Shop\Admin;

use App\Facades\Verifiable;
use App\Repositories\CommentRepository;
use Illuminate\Http\Request;

class CommentController extends CommonController
{
    private $commentRepository;

    public function __construct(
        CommentRepository $commentRepository
    )
    {
        parent::__construct();
        $this->checkPrivilege('comment');
        $this->commentRepository = $commentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search['keywords'] = $request->get('keywords');
        $search['seller'] = 'selfsale';
        $comments = $this->commentRepository->getCommentByPage($search);
        return view('shop.admin.comment.comment', compact('comments', 'search'));
    }

    public function change(Request $request)
    {
        dd($request->except('_token'));
    }

    public function adAdd($id)
    {

    }

    public function adEdit($id, $ad_type)
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ver = Verifiable::Validator($request->all(), ["xxxxx" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->xxxxxxxxxxxxxx->xxxxxxxxxxxxxx($request->except('_token'));
        return view('shop.admin.success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $search['keywords'] = $request->get('keywords');
        $search['seller'] = $id ? $id : 'selfsale';
        $comments = $this->commentRepository->getCommentByPage($search);
        return view('shop.admin.comment.comment', compact('comments', 'search'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $comments = $this->commentRepository->getComments($id);
        return view('shop.admin.comment.commentInfo', compact('comments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $ver = Verifiable::Validator($request->all(), ["xxxxx" => 'required']);
        if (!$ver->passes()) {
            return view('shop.admin.failed');
        }
        $re = $this->xxxxxxxxxxxxxx->XXXXXXXXXXXXXX($request->except('_token', '_method'), $id);
        return view('shop.admin.success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->commentRepository->delComment($id);
    }
}
