###一、创建github repository(仓库)

###二、安装git客户端

#####2-1 下载git客户端

	官方下载地址：http://git-scm.com/download/  根据你自己的系统 下载对应版本

#####2-2 安装客户端 默认安装

	打开git-bash.exe，在桌面快捷方式/开始菜单/安装目录中

#####2-3 绑定用户

	git config --global user.name "你的hub名称"

	git config --global user.email "你的hub注册填写的邮箱"

ps：git config  –global 参数，有了这个参数，表示你这台机器上所有的Git仓库都会使用这个配置，当然你也可以对某个仓库指定的不同的用户名和邮箱。

###三、为Github账户设置SSH key

#####3-1 生成ssh key

	ssh-keygen -t rsa -C “你的hub注册填写的邮箱”

	1）是路径确认，直接按回车存默认路径即可

	2）直接回车键，这里我们不使用密码进行登录, 用密码太麻烦;

	3）直接回车键

生成成功后，去对应目录C:\Users\您的管理名称\.ssh里（specter为电脑用户名，每个人不同）用记事本打开id_rsa.pub，得到ssh key公钥、

#####3-2 为github账号配置ssh key

	切换到github，展开个人头像的小三角，点击settings

	然后打开SSH keys菜单， 点击Add SSH key新增密钥，填上标题

	接着将id_rsa.pub文件中key粘贴到key文本框里，最后点击Add key生成密钥吧

###四、上传本地项目到github

#####4-1 创建一个本地项目

	放哪里都行看个人

#####4-2 建立本地仓库

	首先，进入到项目目录

	然后执行指令：git init

	初始化成功后你会发现项目里多了一个隐藏文件夹.git

	接着，将所有文件添加到仓库

	执行指令：git add .

	然后，把文件提交到仓库，双引号内是提交注释。

	执行指令：git commit -m "commit file"

	如此本地仓库建立好了。

#####4-3 关联github仓库

	到github 对应仓库复制仓库地址 我选择ssh地址

	然后执行指令：git remote add origin git@github.com:chenyu00544/loong.git

#####4-4 上传本地代码

	执行指令：git push -u origin master

######1）敲一个：yes， 然后回车

	到此，本地代码已经推送到github仓库了，我们现在去githubt仓库看看。

###五、执行指令添加文件

	创建相应文件和文件夹

	git add .

	git commit -m "提交test1.html"

	git push -u origin master

###六、执行指令更新文件

#####第一步：查看当前的git仓库状态，可以使用git status

	git status

#####第二步：更新全部

	git add *

#####第三步：接着输入git commit -m "更新说明"

	git commit -m "更新说明"

#####第四步：先git pull,拉取当前分支最新代码防止代码修改冲突

	git pull

	无效 使用
	git pull origin master --allow-unrelated-histories

	有文件没有pull下来，先git status看看状态再
	撤销修改 git checkout 路径+文件
	

#####第五步：push到远程master分支上

	git push -u origin master

###七、执行指令删除文件

	删除文件夹要使用-r 参数

	git rm --cached -r 路径/文件夹名或文件名

	git commit -m "remove file"

	git push -u origin master

OK:完结~~~~~~~~~~~~~~~~~~~~~~~~~~~~

###github上不去或者网页打开不正常
	在hosts文件中加入下列IP，保存即可生效
	
	#github
	192.30.253.113 github.com
	192.30.253.113 github.com
	192.30.253.118 gist.github.com
	192.30.253.119 gist.github.com