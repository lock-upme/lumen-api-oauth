#Lumen Oauth2

Lumen在作为API开发的时候，access_token是验证是避免不了，我们在使用Oauth2验证的配置经常会现一些问题

国外一篇文章：http://esbenp.github.io/2015/05/26/lumen-web-api-oauth-2-authentication/

介绍Oauth2的配置，在按作者步骤配置过程中，总是不能成功配置；Google了多次才找到解决方法，现把完整的版本发布出来;

安装配置的步骤参考老外文章

其中要注意的是在.env 添加
	AUTH_MODEL=App\Auth\user		-----原文章中没有此项
	默认缓存是memcached				-----本地环境没有的话，可以改成文件缓存
	

#特别说明
在原作者的基础上，添加了两个本地项目用户表

	ut_member				------本地用户表
	ut _member_users	------本地用户表与Oauth2用户表关联
	
#测试工具
在网站目录下添加了一个文件夹tool，接口测试工具，是一位宝宝同事提供的~

http://www.domain.com/tool/index.php
		