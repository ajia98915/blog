<?php
namespace controllers;

use models\Blog;

class BlogController
{
    public function update()
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];
        $id = $_POST['id'];

        $blog = new Blog;
        $blog->update($title, $content, $is_show, $id);
    }
    public function edit()
    {
        $id = $_GET['id'];
        //根据ID取出日志信息

        $blog = new Blog;
        $data = $blog->find($id);

        view('blogs.edit',[
            'data' => $data,
        ]);

    }
    public function delete()
    {
       
        $id = $_POST['id'];
        $blog = new Blog;
        $blog->delete($id);
        message('删除成功',2,'/blog/index');
    }
    //添加日志表单
    public function create()
    {
        view('blogs.create');
    }

    public function store()
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];

        $blog = new Blog;
        $blog->add($title,$content,$is_show);

        //跳转
        message('发表成功', 2,'/blog/index');
    }
    // 日志列表
    public function index()
    {
        $blog = new Blog;
        // 搜索数据
        $data = $blog->search();
        // 加载视图
        view('blogs.index', $data);
    }

    // 为所有的日志生成详情页
    public function content_to_html()
    {
        $blog = new Blog;
        $blog->content2html();
    }

    public function index2html()
    {
        $blog = new Blog;
        $blog->index2html();
    }

    public function display()
    {
        // 接收日志ID
        $id = (int)$_GET['id'];

        $blog = new Blog;

        // 把浏览量+1，并输出（如果内存中没有就查询数据库，如果内存中有直接操作内存）
        echo $blog->getDisplay($id);

        
    }

    public function displayToDb()
    {
        $blog = new Blog;
        $blog->displayToDb();
    }
}
